// participantes.js — CRUD de participantes (aulas 2–4: DOM, fetch, ApiService + JWT).
// A senha nunca volta na listagem; no PUT ela é opcional (mantém o hash).
import ApiService from './ApiService.js';
import { obterToken, exigirLogin, montarTopo, isAdmin, sair, sessaoExpirada } from './sessao.js';

document.addEventListener('DOMContentLoaded', () => {
  exigirLogin();
  montarTopo();

  const api = new ApiService(obterToken());
  const ADMIN = isAdmin();

  const alerta = document.getElementById('alertGlobal');
  const lista = document.getElementById('tbody');
  const vazio = document.getElementById('empty');
  const carregando = document.getElementById('loading');
  const tabela = document.getElementById('tableWrapper');
  const contador = document.getElementById('contador');
  const modal = document.getElementById('modalEditar');

  if (!ADMIN) {
    const secao = document.getElementById('formCadastro').closest('section');
    if (secao) secao.style.display = 'none';
    mostrar('Perfil comum: você pode listar. Só administradores cadastram, editam ou excluem.', 'info');
  }

  carregar();
  document.getElementById('formCadastro').addEventListener('submit', cadastrar);
  document.getElementById('formEditar').addEventListener('submit', salvarEdicao);
  document.getElementById('btnRecarregar').addEventListener('click', carregar);
  document.querySelectorAll('.modal-close').forEach((b) => b.addEventListener('click', fecharModal));
  document.querySelector('#modalEditar .modal-footer .btn-secondary')
    ?.addEventListener('click', fecharModal);
  modal.addEventListener('click', (e) => { if (e.target === modal) fecharModal(); });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') fecharModal();
  });

  function mostrar(msg, tipo) {
    alerta.textContent = msg;
    alerta.className = 'alert alert-' + (tipo === 'error' ? 'danger' : tipo);
    alerta.style.display = 'flex';
  }

  function esconder() {
    alerta.style.display = 'none';
    alerta.textContent = '';
  }

  function seloPerfil(perfil) {
    return (perfil || 'comum') === 'administrador'
      ? '<span class="badge badge-primary">admin</span>'
      : '<span class="badge badge-neutral">comum</span>';
  }

  async function carregar() {
    carregando.style.display = '';
    tabela.style.display = 'none';
    vazio.style.display = 'none';
    esconder();
    const res = await api.get('/participantes');
    carregando.style.display = 'none';
    if (!res || !res.success) {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao carregar participantes.', 'error');
      return;
    }
    desenhar(res.data.participantes || []);
  }

  function desenhar(participantes) {
    lista.innerHTML = '';
    contador.textContent = participantes.length + (participantes.length === 1 ? ' participante' : ' participantes');
    if (!participantes.length) {
      vazio.style.display = '';
      return;
    }
    tabela.style.display = '';
    for (const p of participantes) {
      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td><span class="badge badge-neutral">#' + p.id_participante + '</span></td>' +
        '<td><strong></strong></td>' +
        '<td></td>' +
        '<td></td>' +
        '<td></td>' +
        '<td>' + seloPerfil(p.perfil) + '</td>' +
        '<td><div class="table-actions">' +
        (ADMIN
          ? '<button type="button" class="btn btn-secondary btn-sm">✎ Editar</button>' +
            '<button type="button" class="btn btn-danger btn-sm">🗑 Excluir</button>'
          : '<span class="muted">—</span>') +
        '</div></td>';
      tr.children[1].querySelector('strong').textContent = p.nome;
      tr.children[2].textContent = p.email;
      tr.children[3].textContent = p.cpf;
      tr.children[4].textContent = p.telefone || '—';
      if (ADMIN) {
        const [btnEditar, btnExcluir] = tr.querySelectorAll('button');
        btnEditar.addEventListener('click', () => abrirModal(p));
        btnExcluir.addEventListener('click', () => excluir(p));
      }
      lista.appendChild(tr);
    }
  }

  async function cadastrar(e) {
    e.preventDefault();
    const participante = {
      nome: document.getElementById('nome').value.trim(),
      email: document.getElementById('email').value.trim(),
      telefone: document.getElementById('telefone').value.trim(),
      cpf: document.getElementById('cpf').value.trim(),
      senha: document.getElementById('senha').value,
      perfil: document.getElementById('perfil').value,
    };
    if (!participante.nome || !participante.email || !participante.cpf || !participante.senha) {
      mostrar('Preencha nome, e-mail, CPF e senha.', 'error');
      return;
    }
    const res = await api.post('/participantes', { participante });
    if (res && res.success) {
      e.target.reset();
      await carregar();
      mostrar('Participante "' + participante.nome + '" cadastrado com sucesso!', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao cadastrar participante.', 'error');
    }
  }

  function abrirModal(p) {
    document.getElementById('editId').value = p.id_participante;
    document.getElementById('editNome').value = p.nome || '';
    document.getElementById('editEmail').value = p.email || '';
    document.getElementById('editTelefone').value = p.telefone || '';
    document.getElementById('editCpf').value = p.cpf || '';
    document.getElementById('editSenha').value = '';
    document.getElementById('editPerfil').value = p.perfil || 'comum';
    modal.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  }

  function fecharModal() {
    modal.classList.remove('is-open');
    document.body.style.overflow = '';
  }

  async function salvarEdicao(e) {
    e.preventDefault();
    const id = document.getElementById('editId').value;
    const senha = document.getElementById('editSenha').value;
    const participante = {
      nome: document.getElementById('editNome').value.trim(),
      email: document.getElementById('editEmail').value.trim(),
      telefone: document.getElementById('editTelefone').value.trim(),
      cpf: document.getElementById('editCpf').value.trim(),
      perfil: document.getElementById('editPerfil').value,
    };
    if (!id || !participante.nome || !participante.email || !participante.cpf) {
      mostrar('Preencha nome, e-mail e CPF.', 'error');
      return;
    }
    if (senha) {
      if (senha.length < 6) {
        mostrar('A nova senha deve ter no mínimo 6 caracteres.', 'error');
        return;
      }
      participante.senha = senha; // em branco = mantém a atual
    }
    const res = await api.put('/participantes', id, { participante });
    if (res && res.success) {
      fecharModal();
      await carregar();
      mostrar('Participante atualizado com sucesso!', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao atualizar participante.', 'error');
    }
  }

  async function excluir(p) {
    if (!confirm('Excluir o participante "' + p.nome + '" (ID ' + p.id_participante + ')?')) return;
    const res = await api.delete('/participantes', p.id_participante);
    if (res && res.success) {
      carregar();
      mostrar('Participante excluído com sucesso.', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao excluir participante.', 'error');
      carregar();
    }
  }
});
