// eventos.js — CRUD de eventos (aulas 2–4: DOM, fetch, ApiService + JWT).
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
  const comboLocal = document.getElementById('idLocal');
  const comboEditar = document.getElementById('editIdLocal');
  let locais = [];

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

  // datetime-local (2026-09-15T08:00) <-> API (2026-09-15 08:00:00)
  const paraApi = (v) => (v.length === 16 ? v.replace('T', ' ') + ':00' : v.replace('T', ' '));
  const paraInput = (v) => (v || '').slice(0, 16).replace(' ', 'T');
  const nomeLocal = (id) => (locais.find((l) => l.id_local === id)?.nome || '#' + id);

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
    const resLocais = await api.get('/locais');
    if (!resLocais || !resLocais.success) {
      if (sessaoExpirada(resLocais)) { sair(); return; }
      mostrar('Erro ao carregar locais.', 'error');
      return;
    }
    locais = resLocais.data.locais || [];
    encherCombo(comboLocal, '');
    await carregar();
  }

  function encherCombo(select, selecionado) {
    select.innerHTML = '';
    const vazio = document.createElement('option');
    vazio.value = '';
    vazio.textContent = 'Selecione um local...';
    select.appendChild(vazio);
    for (const l of locais) {
      const op = document.createElement('option');
      op.value = l.id_local;
      op.textContent = '#' + l.id_local + ' — ' + l.nome;
      if (String(l.id_local) === String(selecionado)) op.selected = true;
      select.appendChild(op);
    }
  }

  async function carregar() {
    const res = await api.get('/eventos');
    carregando.style.display = 'none';
    if (!res || !res.success) {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao carregar eventos.', 'error');
      return;
    }
    desenhar(res.data.eventos || []);
  }

  function desenhar(eventos) {
    lista.innerHTML = '';
    contador.textContent = eventos.length + (eventos.length === 1 ? ' evento' : ' eventos');
    if (!eventos.length) {
      vazio.style.display = '';
      return;
    }
    tabela.style.display = '';
    for (const e of eventos) {
      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td><span class="badge badge-neutral">#' + e.id_evento + '</span></td>' +
        '<td><strong></strong><br><small class="text-muted"></small></td>' +
        '<td><span class="badge badge-primary">' + e.status + '</span></td>' +
        '<td></td>' +
        '<td><div class="table-actions">' +
        (ADMIN
          ? '<button type="button" class="btn btn-secondary btn-sm">✎ Editar</button>' +
            '<button type="button" class="btn btn-danger btn-sm">🗑 Excluir</button>'
          : '<span class="muted">—</span>') +
        '</div></td>';
      tr.children[1].querySelector('strong').textContent = e.titulo;
      tr.children[1].querySelector('small').textContent = e.data_evento;
      tr.children[3].textContent = nomeLocal(e.id_local);
      if (ADMIN) {
        const [btnEditar, btnExcluir] = tr.querySelectorAll('button');
        btnEditar.addEventListener('click', () => abrirModal(e));
        btnExcluir.addEventListener('click', () => excluir(e));
      }
      lista.appendChild(tr);
    }
  }


  async function cadastrar(e) {
    e.preventDefault();
    const evento = {
      titulo: document.getElementById('titulo').value.trim(),
      descricao: document.getElementById('descricao').value.trim(),
      data_evento: paraApi(document.getElementById('dataEvento').value),
      status: document.getElementById('status').value,
      id_local: Number(document.getElementById('idLocal').value),
    };
    if (!evento.titulo || !evento.data_evento || !evento.id_local) {
      mostrar('Preencha título, data e local.', 'error');
      return;
    }
    const res = await api.post('/eventos', { evento });
    if (res && res.success) {
      e.target.reset();
      await carregar();
      mostrar('Evento cadastrado com sucesso!', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao cadastrar evento.', 'error');
    }
  }

  function abrirModal(e) {
    document.getElementById('editId').value = e.id_evento;
    document.getElementById('editTitulo').value = e.titulo || '';
    document.getElementById('editDescricao').value = e.descricao || '';
    document.getElementById('editDataEvento').value = paraInput(e.data_evento);
    document.getElementById('editStatus').value = e.status;
    encherCombo(comboEditar, e.id_local);
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
    const evento = {
      titulo: document.getElementById('editTitulo').value.trim(),
      descricao: document.getElementById('editDescricao').value.trim(),
      data_evento: paraApi(document.getElementById('editDataEvento').value),
      status: document.getElementById('editStatus').value,
      id_local: Number(document.getElementById('editIdLocal').value),
    };
    if (!id || !evento.titulo || !evento.data_evento || !evento.id_local) {
      mostrar('Preencha todos os campos obrigatórios.', 'error');
      return;
    }
    const res = await api.put('/eventos', id, { evento });
    if (res && res.success) {
      fecharModal();
      await carregar();
      mostrar('Evento atualizado com sucesso!', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao atualizar evento.', 'error');
    }
  }

  async function excluir(e) {
    if (!confirm('Excluir o evento "' + e.titulo + '" (ID ' + e.id_evento + ')?')) return;
    const res = await api.delete('/eventos', e.id_evento);
    if (res && res.success) {
      carregar();
      mostrar('Evento excluído com sucesso.', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao excluir evento.', 'error');
      carregar();
    }
  }
});
