// ingressos.js — CRUD de ingressos (aulas 2–4: DOM, fetch, ApiService + JWT).
// O estoque disponível é controlado pela API (começa cheio).
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
  const comboEvento = document.getElementById('idEvento');
  const comboEditar = document.getElementById('editIdEvento');
  let eventos = [];

  if (!ADMIN) {
    const secao = document.getElementById('formCadastro').closest('section');
    if (secao) secao.style.display = 'none';
    mostrar('Perfil comum: você pode listar. Só administradores cadastram, editam ou excluem.', 'info');
  }

  carregarTudo();
  document.getElementById('formCadastro').addEventListener('submit', cadastrar);
  document.getElementById('formEditar').addEventListener('submit', salvarEdicao);
  document.getElementById('btnRecarregar').addEventListener('click', carregarTudo);
  document.querySelectorAll('.modal-close').forEach((b) => b.addEventListener('click', fecharModal));
  document.querySelector('#modalEditar .modal-footer .btn-secondary')
    ?.addEventListener('click', fecharModal);
  modal.addEventListener('click', (e) => { if (e.target === modal) fecharModal(); });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') fecharModal();
  });

  const tituloEvento = (id) => (eventos.find((e) => e.id_evento === id)?.titulo || '#' + id);
  const dinheiro = (v) => Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

  function mostrar(msg, tipo) {
    alerta.textContent = msg;
    alerta.className = 'alert alert-' + (tipo === 'error' ? 'danger' : tipo);
    alerta.style.display = 'flex';
  }

  function esconder() {
    alerta.style.display = 'none';
    alerta.textContent = '';
  }

  async function carregarTudo() {
    carregando.style.display = '';
    tabela.style.display = 'none';
    vazio.style.display = 'none';
    esconder();
    const resEventos = await api.get('/eventos');
    if (!resEventos || !resEventos.success) {
      if (sessaoExpirada(resEventos)) { sair(); return; }
      mostrar('Erro ao carregar eventos.', 'error');
      return;
    }
    eventos = resEventos.data.eventos || [];
    encherCombo(comboEvento, '');
    await carregar();
  }

  function encherCombo(select, selecionado) {
    select.innerHTML = '';
    const vazio = document.createElement('option');
    vazio.value = '';
    vazio.textContent = 'Selecione um evento...';
    select.appendChild(vazio);
    for (const e of eventos) {
      const op = document.createElement('option');
      op.value = e.id_evento;
      op.textContent = '#' + e.id_evento + ' — ' + e.titulo;
      if (String(e.id_evento) === String(selecionado)) op.selected = true;
      select.appendChild(op);
    }
  }

  async function carregar() {
    const res = await api.get('/ingressos');
    carregando.style.display = 'none';
    if (!res || !res.success) {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao carregar ingressos.', 'error');
      return;
    }
    desenhar(res.data.ingressos || []);
  }

  function desenhar(ingressos) {
    lista.innerHTML = '';
    contador.textContent = ingressos.length + (ingressos.length === 1 ? ' ingresso' : ' ingressos');
    if (!ingressos.length) {
      vazio.style.display = '';
      return;
    }
    tabela.style.display = '';
    for (const i of ingressos) {
      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td><span class="badge badge-neutral">#' + i.id_ingresso + '</span></td>' +
        '<td></td>' +
        '<td>' + dinheiro(i.preco) + '</td>' +
        '<td>' + i.quantidade_total + '</td>' +
        '<td><span class="badge badge-primary">' + i.quantidade_disponivel + '</span></td>' +
        '<td></td>' +
        '<td><div class="table-actions">' +
        (ADMIN
          ? '<button type="button" class="btn btn-secondary btn-sm">✎ Editar</button>' +
            '<button type="button" class="btn btn-danger btn-sm">🗑 Excluir</button>'
          : '<span class="muted">—</span>') +
        '</div></td>';
      tr.children[1].textContent = i.tipo;
      tr.children[5].textContent = tituloEvento(i.id_evento);
      if (ADMIN) {
        const [btnEditar, btnExcluir] = tr.querySelectorAll('button');
        btnEditar.addEventListener('click', () => abrirModal(i));
        btnExcluir.addEventListener('click', () => excluir(i));
      }
      lista.appendChild(tr);
    }
  }

  function lerNumero(id, minimo) {
    const v = Number(document.getElementById(id).value);
    return Number.isFinite(v) && v >= minimo ? v : null;
  }

  async function cadastrar(e) {
    e.preventDefault();
    const ingresso = {
      tipo: document.getElementById('tipo').value,
      preco: lerNumero('preco', 0),
      quantidade_total: lerNumero('quantidadeTotal', 1),
      id_evento: Number(document.getElementById('idEvento').value),
    };
    if (!ingresso.tipo || ingresso.preco === null || ingresso.quantidade_total === null || !ingresso.id_evento) {
      mostrar('Preencha todos os campos corretamente.', 'error');
      return;
    }
    const res = await api.post('/ingressos', { ingresso });
    if (res && res.success) {
      e.target.reset();
      await carregar();
      mostrar('Ingresso "' + ingresso.tipo + '" cadastrado com sucesso!', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao cadastrar ingresso.', 'error');
    }
  }

  function abrirModal(i) {
    document.getElementById('editId').value = i.id_ingresso;
    document.getElementById('editTipo').value = i.tipo;
    document.getElementById('editPreco').value = i.preco;
    document.getElementById('editQuantidadeTotal').value = i.quantidade_total;
    encherCombo(comboEditar, i.id_evento);
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
    const ingresso = {
      tipo: document.getElementById('editTipo').value,
      preco: lerNumero('editPreco', 0),
      quantidade_total: lerNumero('editQuantidadeTotal', 1),
      id_evento: Number(document.getElementById('editIdEvento').value),
    };
    if (!id || !ingresso.tipo || ingresso.preco === null || ingresso.quantidade_total === null || !ingresso.id_evento) {
      mostrar('Preencha todos os campos corretamente.', 'error');
      return;
    }
    const res = await api.put('/ingressos', id, { ingresso });
    if (res && res.success) {
      fecharModal();
      await carregar();
      mostrar('Ingresso atualizado com sucesso!', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao atualizar ingresso.', 'error');
    }
  }

  async function excluir(i) {
    if (!confirm('Excluir o ingresso "' + i.tipo + '" (ID ' + i.id_ingresso + ')?')) return;
    const res = await api.delete('/ingressos', i.id_ingresso);
    if (res && res.success) {
      carregar();
      mostrar('Ingresso excluído com sucesso.', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao excluir ingresso.', 'error');
      carregar();
    }
  }
});
