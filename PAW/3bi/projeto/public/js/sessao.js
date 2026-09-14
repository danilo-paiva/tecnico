// sessao.js — login guardado no navegador (aula: localStorage + JSON).
// Usado por todas as páginas junto com ApiService.js (aula 4).
//
// Sobre o objeto 'window' usado abaixo:
// - window é o objeto global do navegador: representa a janela/aba aberta.
// - Tudo que é do navegador mora nele: document (a página), localStorage
//   (guardar dados), fetch (requisições) e location (o endereço da aba).
// - window.location.href é o endereço escrito na barra do navegador.
//   LER dá a página atual; ATRIBUIR um valor novo faz o navegador NAVEGAR
//   para lá (como clicar num link). É assim que trocamos de tela após o
//   login e que barramos quem tenta abrir página protegida sem token.
const TOKEN_KEY = 'token';
const USUARIO_KEY = 'usuario';

function salvarSessao(token, usuario) {
  localStorage.setItem(TOKEN_KEY, token);
  localStorage.setItem(USUARIO_KEY, JSON.stringify(usuario));
}

function obterToken() {
  return localStorage.getItem(TOKEN_KEY);
}

function obterUsuario() {
  try {
    return JSON.parse(localStorage.getItem(USUARIO_KEY));
  } catch {
    return null;
  }
}

function sair() {
  localStorage.removeItem(TOKEN_KEY);
  localStorage.removeItem(USUARIO_KEY);
  // Apaga o login e navega de volta para a tela de login.
  window.location.href = 'login.html';
}

// Sem token, volta pro login. Chame no topo das páginas protegidas.
function exigirLogin() {
  // Se não há token guardado, ninguém logou: manda para o login.
  if (!obterToken()) window.location.href = 'login.html';
}

// 'administrador' tem escrita; 'comum' só lista (e compra pra si).
function isAdmin() {
  return (obterUsuario()?.perfil || 'comum') === 'administrador';
}

// Preenche o topo: nome + perfil, esconde Login, liga os botões Sair.
function montarTopo() {
  const usuario = obterUsuario();
  const nome = document.getElementById('userName');
  if (nome && usuario) {
    nome.textContent = usuario.nome + ' (' + (usuario.perfil || 'comum') + ')';
    nome.style.display = 'inline-flex';
  }
  const navLogin = document.getElementById('navLogin');
  if (navLogin && usuario) navLogin.style.display = 'none';
  const btnSair = document.getElementById('btnLogout');
  if (btnSair) {
    if (usuario) btnSair.style.display = 'inline-flex';
    btnSair.addEventListener('click', (e) => { e.preventDefault(); sair(); });
  }
  document.querySelectorAll('[data-sair]').forEach((a) => {
    a.addEventListener('click', (e) => { e.preventDefault(); sair(); });
  });
}

// Se a API respondeu 401 (sem token / expirado), derruba a sessão.
function sessaoExpirada(resposta) {
  return !!resposta && resposta.success === false && /autorizado|token/i.test(resposta.message || '');
}
