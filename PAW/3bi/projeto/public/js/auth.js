// auth.js — guarda o JWT no localStorage (aula paw03x02) e protege as paginas.
// Fluxo da aula paw03x01: login -> POST /login -> recebe token -> guarda ->
// envia `Authorization: Bearer <token>` em toda rota protegida.
import ApiService from "./ApiService.js";

const TOKEN_KEY = "token";
const USER_KEY = "participante";

export function salvarSessao(token, participante) {
  localStorage.setItem(TOKEN_KEY, token);
  localStorage.setItem(USER_KEY, JSON.stringify(participante));
}

export function obterToken() { return localStorage.getItem(TOKEN_KEY); }
export function obterUsuario() {
  try { return JSON.parse(localStorage.getItem(USER_KEY)); }
  catch { return null; }
}

export function sair() {
  localStorage.removeItem(TOKEN_KEY);
  localStorage.removeItem(USER_KEY);
  window.location.href = "login.html";
}

// Cria o ApiService ja com o token guardado.
export function apiAutenticada() {
  return new ApiService(obterToken());
}

// Chame no topo das paginas protegidas: sem token, volta ao login.
export function exigirLogin() {
  if (!obterToken()) window.location.href = "login.html";
}

// Preenche o topo (nome do usuario + botao sair). Opcional por pagina.
export function montarTopo(paginaAtiva = "") {
  const usuario = obterUsuario();
  const el = document.getElementById("usuarioLogado");
  if (el) el.textContent = usuario ? `Olá, ${usuario.nome}` : "";
  document.querySelectorAll(".topbar nav a").forEach((a) => {
    if (a.dataset.pagina === paginaAtiva) a.classList.add("ativo");
  });
  const btnSair = document.getElementById("btnSair");
  if (btnSair) btnSair.addEventListener("click", sair);
}

// Exibe mensagens no elemento #msg da pagina.
export function mostrarMsg(texto, tipo = "info") {
  const msg = document.getElementById("msg");
  if (!msg) return;
  msg.className = `msg ${tipo}`;
  msg.textContent = texto;
}

// Se a API responder 401 (token invalido/expirado), derruba a sessao.
export function tratarResposta(resposta) {
  if (resposta && resposta.success === false && /autorizado|token/i.test(resposta.message || "")) {
    mostrarMsg("Sessão expirada. Faça login novamente.", "erro");
    setTimeout(sair, 1500);
    return false;
  }
  return true;
}
