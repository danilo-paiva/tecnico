// locais.js — CRUD de locais (aulas 2–4: DOM, fetch, ApiService + JWT).
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

  async function carregar() {
    carregando.style.display = '';
    tabela.style.display = 'none';
    vazio.style.display = 'none';
    esconder();
    const res = await api.get('/locais');
    carregando.style.display = 'none';
    if (!res || !res.success) {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao carregar locais.', 'error');
      return;
    }
    desenhar(res.data.locais || []);
  }

  function desenhar(locais) {
    lista.innerHTML = '';
    contador.textContent = locais.length + (locais.length === 1 ? ' local' : ' locais');
    if (!locais.length) {
      vazio.style.display = '';
      return;
    }
    tabela.style.display = '';
    for (const l of locais) {
      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td><span class="badge badge-neutral">#' + l.id_local + '</span></td>' +
        '<td><strong></strong></td>' +
        '<td></td>' +
        '<td><span class="badge badge-primary">' + l.capacidade + '</span></td>' +
        '<td><div class="table-actions">' +
        (ADMIN
          ? '<button type="button" class="btn btn-secondary btn-sm" data-editar>✎ Editar</button>' +
            '<button type="button" class="btn btn-danger btn-sm" data-excluir>🗑 Excluir</button>'
          : '<span class="muted">—</span>') +
        '</div></td>';
      tr.children[1].querySelector('strong').textContent = l.nome;
      tr.children[2].textContent = l.endereco;
      if (ADMIN) {
        tr.querySelector('[data-editar]').addEventListener('click', () => abrirModal(l));
        tr.querySelector('[data-excluir]').addEventListener('click', (e) => excluir(l, e.target));
      }
      lista.appendChild(tr);
    }
  }

  async function cadastrar(e) {
    e.preventDefault();
    const nome = document.getElementById('nome').value.trim();
    const endereco = document.getElementById('endereco').value.trim();
    const capacidade = parseInt(document.getElementById('capacidade').value, 10);
    if (!nome || !endereco || !(capacidade >= 1)) {
      mostrar('Preencha nome, endereço e capacidade válida (≥1).', 'error');
      return;
    }
    const res = await api.post('/locais', { local: { nome, endereco, capacidade } });
    if (res && res.success) {
      e.target.reset();
      await carregar();
      mostrar('Local "' + nome + '" cadastrado com sucesso!', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao cadastrar local.', 'error');
    }
  }

  function abrirModal(l) {
    document.getElementById('editId').value = l.id_local;
    document.getElementById('editNome').value = l.nome || '';
    document.getElementById('editEndereco').value = l.endereco || '';
    document.getElementById('editCapacidade').value = l.capacidade || '';
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
    const nome = document.getElementById('editNome').value.trim();
    const endereco = document.getElementById('editEndereco').value.trim();
    const capacidade = parseInt(document.getElementById('editCapacidade').value, 10);
    if (!id || !nome || !endereco || !(capacidade >= 1)) {
      mostrar('Preencha todos os campos para editar.', 'error');
      return;
    }
    const res = await api.put('/locais', id, { local: { nome, endereco, capacidade } });
    if (res && res.success) {
      fecharModal();
      await carregar();
      mostrar('Local atualizado com sucesso!', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao atualizar local.', 'error');
    }
  }

  async function excluir(l, botao) {
    if (!confirm('Excluir o local "' + l.nome + '" (ID ' + l.id_local + ')?')) return;
    botao.disabled = true;
    const res = await api.delete('/locais', l.id_local);
    if (res && res.success) {
      carregar();
      mostrar('Local excluído com sucesso.', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao excluir local.', 'error');
      carregar();
    }
  }
});
