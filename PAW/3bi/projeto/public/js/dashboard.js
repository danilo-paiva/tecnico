// dashboard.js — painel com prévia ao vivo da API (aula 3: fetch + async).
import ApiService from './ApiService.js';
import { obterToken, exigirLogin, montarTopo, sair } from './sessao.js';

document.addEventListener('DOMContentLoaded', async () => {
  exigirLogin();
  montarTopo();

  const api = new ApiService(obterToken());
  const eu = await api.get('/auth/me');
  if (!eu || !eu.success) {
    sair(); // 401: token inválido ou expirado
    return;
  }
  const user = eu.data;

  document.getElementById('userNameInline').textContent = user.nome;
  document.getElementById('welcomeSub').textContent =
    'Você está autenticado. Escolha uma entidade acima para gerenciar (todas exigem JWT).';
  document.getElementById('userEmail').textContent = user.email || '—';
  document.getElementById('userId').textContent =
    'ID: ' + (user.id_participante || '—') + ' · ' + (user.perfil || 'comum');
  document.getElementById('userMeta').hidden = false;

  // Botões "Prévia API": GET /entidade com Bearer e mostra o JSON.
  const previa = document.getElementById('preview');
  const titulo = document.getElementById('previewTitle');
  const corpo = document.getElementById('previewBody');

  document.querySelectorAll('[data-action]').forEach((el) => {
    el.addEventListener('click', async (e) => {
      e.preventDefault();
      const entidade = el.getAttribute('data-entity');
      titulo.textContent = 'Carregando ' + entidade + '...';
      corpo.textContent = 'Buscando em /' + entidade + ' com Authorization: Bearer <token>...';
      previa.hidden = false;
      const dados = await api.get('/' + entidade);
      titulo.textContent = entidade + ' — listagem (com JWT)';
      corpo.textContent = JSON.stringify(dados, null, 2);
      previa.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  });
});
