// sessao.js — login guardado no navegador (aula: localStorage + JSON).
// Usado por todas as páginas junto com ApiService.js (aula 4).
const TOKEN_KEY = 'token';
const USUARIO_KEY = 'usuario';

export function salvarSessao(token, usuario) {
  localStorage.setItem(TOKEN_KEY, token);
  localStorage.setItem(USUARIO_KEY, JSON.stringify(usuario));
}

export function obterToken() {
  return localStorage.getItem(TOKEN_KEY);
}

export function obterUsuario() {
  try {
    return JSON.parse(localStorage.getItem(USUARIO_KEY));
  } catch {
    return null;
  }
}

export function sair() {
  localStorage.removeItem(TOKEN_KEY);
  localStorage.removeItem(USUARIO_KEY);
  window.location.href = 'login.html';
}

// Sem token, volta pro login. Chame no topo das páginas protegidas.
export function exigirLogin() {
  if (!obterToken()) window.location.href = 'login.html';
}

// 'administrador' tem escrita; 'comum' só lista (e compra pra si).
export function isAdmin() {
  return (obterUsuario()?.perfil || 'comum') === 'administrador';
}

// Preenche o topo: nome + perfil, esconde Login, liga os botões Sair.
export function montarTopo() {
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
export function sessaoExpirada(resposta) {
  return !!resposta && resposta.success === false && /autorizado|token/i.test(resposta.message || '');
}
