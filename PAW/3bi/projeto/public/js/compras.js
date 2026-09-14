// compras.js — compras de ingressos (aulas 2–4: DOM, fetch, ApiService + JWT).
// Todo logado pode COMPRAR (só para si, se for comum); editar/excluir é de admin.
import ApiService from './ApiService.js';
import { obterToken, obterUsuario, exigirLogin, montarTopo, isAdmin, sair, sessaoExpirada } from './sessao.js';

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
  const comboPart = document.getElementById('idParticipante');
  const comboIng = document.getElementById('idIngresso');
  const comboEditPart = document.getElementById('editIdParticipante');
  const comboEditIng = document.getElementById('editIdIngresso');
  let participantes = [];
  let ingressos = [];

  if (!ADMIN) {
    // Comum compra só para si: trava o combo no próprio id.
    const eu = obterUsuario();
    const meuId = eu ? String(eu.id_participante || eu.id || '') : '';
    if (meuId) {
      comboPart.value = meuId;
      comboPart.disabled = true;
    }
    mostrar('Você pode comprar ingressos para você mesmo. Editar ou excluir compra é só de administrador.', 'info');
  }

  carregarTudo();
  document.getElementById('formCadastro').addEventListener('submit', comprar);
  document.getElementById('formEditar').addEventListener('submit', salvarEdicao);
  document.getElementById('btnRecarregar').addEventListener('click', carregarTudo);
  document.querySelectorAll('.modal-close').forEach((b) => b.addEventListener('click', fecharModal));
  document.querySelector('#modalEditar .modal-footer .btn-secondary')
    ?.addEventListener('click', fecharModal);
  modal.addEventListener('click', (e) => { if (e.target === modal) fecharModal(); });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') fecharModal();
  });

  const dinheiro = (v) => Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  const nomePart = (id) => (participantes.find((p) => p.id_participante === id)?.nome || '#' + id);
  const tipoIng = (id) => (ingressos.find((i) => i.id_ingresso === id)?.tipo || '#' + id);

  function mostrar(msg, tipo) {
    alerta.textContent = msg;
    alerta.className = 'alert alert-' + (tipo === 'error' ? 'danger' : tipo);
    alerta.style.display = 'flex';
  }

  function esconder() {
    alerta.style.display = 'none';
    alerta.textContent = '';
  }

  function encherCombo(select, itens, texto) {
    select.innerHTML = '';
    const vazio = document.createElement('option');
    vazio.value = '';
    vazio.textContent = 'Selecione...';
    select.appendChild(vazio);
    for (const item of itens) {
      const op = document.createElement('option');
      op.value = item.id;
      op.textContent = texto(item);
      select.appendChild(op);
    }
  }

  async function carregarTudo() {
    carregando.style.display = '';
    tabela.style.display = 'none';
    vazio.style.display = 'none';
    esconder();
    const [resP, resI] = await Promise.all([api.get('/participantes'), api.get('/ingressos')]);
    if ((!resP || !resP.success) || (!resI || !resI.success)) {
      if (sessaoExpirada(resP) || sessaoExpirada(resI)) { sair(); return; }
      mostrar('Erro ao carregar participantes ou ingressos.', 'error');
      return;
    }
    participantes = resP.data.participantes || [];
    ingressos = resI.data.ingressos || [];
    encherCombo(comboPart, participantes.map((p) => ({ id: p.id_participante, t: p.nome + ' — ' + p.email })), (x) => x.t);
    encherCombo(comboIng, ingressos.map((i) => ({ id: i.id_ingresso, t: i.tipo + ' — ' + dinheiro(i.preco) + ' (disp: ' + i.quantidade_disponivel + ')' })), (x) => x.t);
    await carregar();
    if (!ADMIN) {
      // Reaplica a trava depois de recarregar os combos.
      const eu = obterUsuario();
      const meuId = eu ? String(eu.id_participante || eu.id || '') : '';
      if (meuId) {
        comboPart.value = meuId;
        comboPart.disabled = true;
      }
    }
  }

  async function carregar() {
    const res = await api.get('/compras');
    carregando.style.display = 'none';
    if (!res || !res.success) {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao carregar compras.', 'error');
      return;
    }
    desenhar(res.data.compras || []);
  }

  function desenhar(compras) {
    lista.innerHTML = '';
    contador.textContent = compras.length + (compras.length === 1 ? ' compra' : ' compras');
    if (!compras.length) {
      vazio.style.display = '';
      return;
    }
    tabela.style.display = '';
    for (const c of compras) {
      const tr = document.createElement('tr');
      tr.innerHTML =
        '<td><span class="badge badge-neutral">#' + c.id_compra + '</span></td>' +
        '<td></td>' +
        '<td>' + c.quantidade + '</td>' +
        '<td>' + dinheiro(c.valor_total) + '</td>' +
        '<td></td>' +
        '<td></td>' +
        '<td><div class="table-actions">' +
        (ADMIN
          ? '<button type="button" class="btn btn-secondary btn-sm">✎ Editar</button>' +
            '<button type="button" class="btn btn-danger btn-sm">🗑 Excluir</button>'
          : '<span class="muted">—</span>') +
        '</div></td>';
      tr.children[1].textContent = c.data_compra;
      tr.children[4].textContent = nomePart(c.id_participante);
      tr.children[5].textContent = tipoIng(c.id_ingresso);
      if (ADMIN) {
        const [btnEditar, btnExcluir] = tr.querySelectorAll('button');
        btnEditar.addEventListener('click', () => abrirModal(c));
        btnExcluir.addEventListener('click', () => excluir(c));
      }
      lista.appendChild(tr);
    }
  }

  async function comprar(e) {
    e.preventDefault();
    const compra = {
      id_participante: Number(comboPart.value),
      id_ingresso: Number(comboIng.value),
      quantidade: Number(document.getElementById('quantidade').value),
    };
    if (!compra.id_participante || !compra.id_ingresso || !(compra.quantidade >= 1)) {
      mostrar('Selecione participante, ingresso e informe quantidade válida.', 'error');
      return;
    }
    const res = await api.post('/compras', { compra });
    if (res && res.success) {
      e.target.reset();
      await carregarTudo();
      mostrar('Compra registrada com sucesso!', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao registrar compra (verifique o estoque).', 'error');
    }
  }

  function abrirModal(c) {
    document.getElementById('editId').value = c.id_compra;
    encherCombo(comboEditPart, participantes.map((p) => ({ id: p.id_participante, t: p.nome })), (x) => x.t);
    encherCombo(comboEditIng, ingressos.map((i) => ({ id: i.id_ingresso, t: i.tipo })), (x) => x.t);
    comboEditPart.value = c.id_participante;
    comboEditIng.value = c.id_ingresso;
    document.getElementById('editQuantidade').value = c.quantidade;
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
    const compra = {
      id_participante: Number(comboEditPart.value),
      id_ingresso: Number(comboEditIng.value),
      quantidade: Number(document.getElementById('editQuantidade').value),
    };
    if (!id || !compra.id_participante || !compra.id_ingresso || !(compra.quantidade >= 1)) {
      mostrar('Preencha todos os campos corretamente.', 'error');
      return;
    }
    const res = await api.put('/compras', id, { compra });
    if (res && res.success) {
      fecharModal();
      await carregar();
      mostrar('Compra atualizada com sucesso!', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao atualizar compra.', 'error');
    }
  }

  async function excluir(c) {
    if (!confirm('Excluir a compra #' + c.id_compra + '? O estoque será devolvido.')) return;
    const res = await api.delete('/compras', c.id_compra);
    if (res && res.success) {
      carregarTudo();
      mostrar('Compra excluída com sucesso.', 'success');
    } else {
      if (sessaoExpirada(res)) { sair(); return; }
      mostrar((res && res.message) || 'Erro ao excluir compra.', 'error');
      carregar();
    }
  }
});
