/**
 * api.js — Helper centralizado para consumo da API com JWT
 * Vanilla JS | fetch | localStorage
 *
 * Funcionalidades:
 *  - getToken / setToken / removeToken / getUser / setUser
 *  - apiFetch(url, options) injeta Authorization: Bearer <token> e trata 401
 *  - Helpers para todas as entidades: locais, eventos, participantes, ingressos, compras e auth
 */

(function (global) {
  'use strict';

  // ---------------------------------------------------------------------------
  // Config
  // ---------------------------------------------------------------------------
  /** Base da API. Vazio = mesma origem (ex: http://localhost:8001). Ajuste se usar porta/host diferente. */
  const API_BASE = ''; // ex: 'http://localhost:8001'

  const TOKEN_KEY = 'token';
  const USER_KEY = 'usuario'; // também aceita 'user' por compatibilidade
  const USER_KEY_ALT = 'user';

  // ---------------------------------------------------------------------------
  // Token / User helpers
  // ---------------------------------------------------------------------------

  /**
   * Retorna o token JWT salvo no localStorage ou null.
   * @returns {string|null}
   */
  function getToken() {
    try {
      return localStorage.getItem(TOKEN_KEY);
    } catch (_e) {
      return null;
    }
  }

  /**
   * Salva o token JWT.
   * @param {string} token
   */
  function setToken(token) {
    localStorage.setItem(TOKEN_KEY, token);
  }

  /**
   * Remove token e dados do usuário (logout local).
   */
  function removeToken() {
    try {
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(USER_KEY);
      localStorage.removeItem(USER_KEY_ALT);
    } catch (_e) { /* ignore */ }
  }

  /**
   * Salva dados do usuário logado (objeto retornado em login.data.usuario).
   * @param {object} user
   */
  function setUser(user) {
    try {
      const raw = JSON.stringify(user);
      localStorage.setItem(USER_KEY, raw);
      localStorage.setItem(USER_KEY_ALT, raw);
    } catch (_e) { /* ignore */ }
  }

  /**
   * Retorna o usuário logado salvo no localStorage ou null.
   * Tenta parsear JSON; se falhar retorna string bruta.
   * @returns {object|null}
   */
  function getUser() {
    try {
      const raw = localStorage.getItem(USER_KEY) || localStorage.getItem(USER_KEY_ALT);
      if (!raw) return null;
      try { return JSON.parse(raw); } catch (_e) { return raw; }
    } catch (_e) {
      return null;
    }
  }

  /**
   * Remove apenas dados do usuário (mantém token se desejado).
   */
  function removeUser() {
    try {
      localStorage.removeItem(USER_KEY);
      localStorage.removeItem(USER_KEY_ALT);
    } catch (_e) { /* ignore */ }
  }

  /**
   * Retorna true se existe token salvo.
   * @returns {boolean}
   */
  function isAuthenticated() {
    const t = getToken();
    return typeof t === 'string' && t.trim() !== '';
  }

  // ---------------------------------------------------------------------------
  // apiFetch — fetch com JWT + tratamento 401
  // ---------------------------------------------------------------------------

  /**
   * Resolve URL contra API_BASE se necessário.
   * - Se url já começa com http:// ou https:// retorna como está.
   * - Se API_BASE estiver vazio, retorna url original (relativo à origem).
   */
  function resolveUrl(url) {
    if (/^https?:\/\//i.test(url)) return url;
    if (!API_BASE) return url;
    // garante apenas uma barra entre base e path
    return API_BASE.replace(/\/$/, '') + '/' + String(url).replace(/^\//, '');
  }

  /**
   * Detecta se estamos já na página de login para evitar loop de redirect.
   */
  function isLoginPage() {
    try {
      const p = window.location.pathname || '';
      return p.endsWith('login.html') || p.endsWith('/login');
    } catch (_e) { return false; }
  }

  /**
   * Fetch autenticado.
   * - Injeta Authorization: Bearer <token> se existir token.
   * - Define Content-Type: application/json automaticamente quando body é objeto.
   * - Em 401 limpa storage e redireciona para login.html (exceto se já estiver lá).
   * - Lança Error com mensagem da API em caso de falha.
   *
   * @param {string} url - ex: '/auth/me' , '/locais' , 'http://localhost:8001/eventos/1'
   * @param {RequestInit} [options]
   * @returns {Promise<any>} — JSON parseado (ou texto se não for JSON)
   */
  async function apiFetch(url, options = {}) {
    const token = getToken();
    const finalUrl = resolveUrl(url);

    const headers = new Headers(options.headers || {});

    // Injeta JWT se houver token e ainda não houver Authorization
    if (token && !headers.has('Authorization') && !headers.has('authorization')) {
      headers.set('Authorization', 'Bearer ' + token);
    }

    // Se body é objeto puro (não FormData/Blob), serializa para JSON
    let body = options.body;
    const hasContentType = headers.has('Content-Type') || headers.has('content-type');
    if (
      body !== undefined &&
      body !== null &&
      typeof body === 'object' &&
      !(body instanceof FormData) &&
      !(body instanceof Blob) &&
      !(body instanceof ArrayBuffer) &&
      !(body instanceof URLSearchParams)
    ) {
      if (!hasContentType) headers.set('Content-Type', 'application/json');
      // só stringifica se ainda não for string
      if (headers.get('Content-Type') && headers.get('Content-Type').includes('application/json') && typeof body !== 'string') {
        body = JSON.stringify(body);
      }
    }

    let response;
    try {
      response = await fetch(finalUrl, { ...options, headers, body });
    } catch (networkError) {
      // Erro de rede (servidor fora, CORS etc)
      const err = new Error(networkError.message || 'Falha de rede ao conectar na API.');
      err.cause = networkError;
      err.status = 0;
      throw err;
    }

    // Trata 401: sessão expirada / token inválido
    if (response.status === 401) {
      // tenta ler corpo para mensagem mais amigável, mas não é obrigatório
      let payload = null;
      try { payload = await response.clone().json(); } catch (_e) { /* ignora */ }
      const msg = (payload && (payload.message || payload.error)) || 'Sessão expirada. Faça login novamente.';

      // limpa credenciais
      removeToken();

      // redireciona para login.html se não estiver já lá
      if (!isLoginPage()) {
        // preserva destino para retorno pós-login
        const current = window.location.pathname + window.location.search;
        const loginUrl = 'login.html';
        // evita loop se já estiver redirecionando
        if (!sessionStorage.getItem('__redirecting_401')) {
          try { sessionStorage.setItem('__redirecting_401', '1'); } catch (_e) {}
          // Guarda mensagem para exibir no login
          try { sessionStorage.setItem('auth_error', typeof msg === 'string' ? msg : JSON.stringify(msg)); } catch (_e) {}
          try { sessionStorage.setItem('redirect_after_login', current); } catch (_e) {}
          window.location.href = loginUrl;
        }
      }

      const error = new Error(typeof msg === 'string' ? msg : 'Não autorizado (401).');
      error.status = 401;
      error.payload = payload;
      error.response = response;
      throw error;
    }

    // Tenta parsear JSON quando houver corpo
    const contentType = response.headers.get('content-type') || '';
    const isJson = contentType.includes('application/json');

    let data = null;
    const text = await response.text();
    if (text) {
      if (isJson) {
        try { data = JSON.parse(text); } catch (_e) { data = text; }
      } else {
        // tenta JSON mesmo sem header, senão retorna texto
        try { data = JSON.parse(text); } catch (_e) { data = text; }
      }
    }

    if (!response.ok) {
      // Extrai mensagem amigável da API: { success, message, error }
      let message = 'Erro na requisição.';
      if (data && typeof data === 'object') {
        message = data.message || data.error?.message || data.error || message;
        if (typeof message !== 'string') {
          try { message = JSON.stringify(message); } catch (_e) { message = String(message); }
        }
      } else if (typeof data === 'string' && data.trim()) {
        message = data;
      } else {
        message = response.statusText || message;
      }
      const error = new Error(message);
      error.status = response.status;
      error.payload = data;
      error.response = response;
      throw error;
    }

    return data;
  }

  // ---------------------------------------------------------------------------
  // Helpers de entidade
  // ---------------------------------------------------------------------------
  // Todos retornam a Promise do apiFetch (JSON já parseado).
  // Use try/catch no chamador para tratamento de erro.

  // ---- Auth ----
  function authLogin(email, senha) {
    return apiFetch('/auth/login', {
      method: 'POST',
      body: { email, senha }
    });
  }

  function authRegister(dados) {
    // dados: { nome, email, cpf, telefone, senha } ou { participante: {...} }
    const body = dados.participante ? dados : { participante: dados };
    // AuthController aceita ambos: { email, senha } direto ou { participante: { ... } }
    // Para register, normaliza para participante
    const payload = body.participante ? body : { participante: body };
    // Se quem chamou já mandou { nome, email... } sem wrapper, envelopa
    if (!payload.participante.email && payload.email) {
      return apiFetch('/auth/register', { method: 'POST', body: { participante: payload } });
    }
    return apiFetch('/auth/register', { method: 'POST', body: payload });
  }

  function authMe() {
    return apiFetch('/auth/me', { method: 'GET' });
  }

  // ---- Locais ----
  function getLocais() { return apiFetch('/locais', { method: 'GET' }); }
  function countLocais() { return apiFetch('/locais/count', { method: 'GET' }); }
  function getLocal(id) { return apiFetch('/locais/' + encodeURIComponent(id), { method: 'GET' }); }
  /** alias */
  function getLocalById(id) { return getLocal(id); }
  function searchLocais(nome) { return apiFetch('/locais/nome/' + encodeURIComponent(nome), { method: 'GET' }); }
  function searchLocaisQuery(nome) { return apiFetch('/locais/busca?nome=' + encodeURIComponent(nome), { method: 'GET' }); }
  function createLocal(dados) { return apiFetch('/locais', { method: 'POST', body: { local: dados } }); }
  function updateLocal(id, dados) { return apiFetch('/locais/' + encodeURIComponent(id), { method: 'PUT', body: { local: dados } }); }
  function deleteLocal(id) { return apiFetch('/locais/' + encodeURIComponent(id), { method: 'DELETE' }); }

  // ---- Eventos ----
  function getEventos() { return apiFetch('/eventos', { method: 'GET' }); }
  function countEventos() { return apiFetch('/eventos/count', { method: 'GET' }); }
  function getEvento(id) { return apiFetch('/eventos/' + encodeURIComponent(id), { method: 'GET' }); }
  function getEventoById(id) { return getEvento(id); }
  function getEventosByLocal(idLocal) { return apiFetch('/eventos/local/' + encodeURIComponent(idLocal), { method: 'GET' }); }
  function createEvento(dados) { return apiFetch('/eventos', { method: 'POST', body: { evento: dados } }); }
  function updateEvento(id, dados) { return apiFetch('/eventos/' + encodeURIComponent(id), { method: 'PUT', body: { evento: dados } }); }
  function deleteEvento(id) { return apiFetch('/eventos/' + encodeURIComponent(id), { method: 'DELETE' }); }

  // ---- Participantes ----
  function getParticipantes() { return apiFetch('/participantes', { method: 'GET' }); }
  function countParticipantes() { return apiFetch('/participantes/count', { method: 'GET' }); }
  function getParticipante(id) { return apiFetch('/participantes/' + encodeURIComponent(id), { method: 'GET' }); }
  function getParticipanteById(id) { return getParticipante(id); }
  function getParticipanteByEmail(email) { return apiFetch('/participantes/email/' + encodeURIComponent(email), { method: 'GET' }); }
  function getParticipanteByCpf(cpf) { return apiFetch('/participantes/cpf/' + encodeURIComponent(cpf), { method: 'GET' }); }
  function createParticipante(dados) { return apiFetch('/participantes', { method: 'POST', body: { participante: dados } }); }
  function updateParticipante(id, dados) { return apiFetch('/participantes/' + encodeURIComponent(id), { method: 'PUT', body: { participante: dados } }); }
  function deleteParticipante(id) { return apiFetch('/participantes/' + encodeURIComponent(id), { method: 'DELETE' }); }

  // ---- Ingressos ----
  function getIngressos() { return apiFetch('/ingressos', { method: 'GET' }); }
  function countIngressos() { return apiFetch('/ingressos/count', { method: 'GET' }); }
  function getIngresso(id) { return apiFetch('/ingressos/' + encodeURIComponent(id), { method: 'GET' }); }
  function getIngressoById(id) { return getIngresso(id); }
  function getIngressosByEvento(idEvento) { return apiFetch('/ingressos/evento/' + encodeURIComponent(idEvento), { method: 'GET' }); }
  function getIngressoByTipoEvento(tipo, idEvento) {
    return apiFetch('/ingressos/tipo/' + encodeURIComponent(tipo) + '/evento/' + encodeURIComponent(idEvento), { method: 'GET' });
  }
  function createIngresso(dados) { return apiFetch('/ingressos', { method: 'POST', body: { ingresso: dados } }); }
  function updateIngresso(id, dados) { return apiFetch('/ingressos/' + encodeURIComponent(id), { method: 'PUT', body: { ingresso: dados } }); }
  function deleteIngresso(id) { return apiFetch('/ingressos/' + encodeURIComponent(id), { method: 'DELETE' }); }

  // ---- Compras ----
  function getCompras() { return apiFetch('/compras', { method: 'GET' }); }
  function countCompras() { return apiFetch('/compras/count', { method: 'GET' }); }
  function getCompra(id) { return apiFetch('/compras/' + encodeURIComponent(id), { method: 'GET' }); }
  function getCompraById(id) { return getCompra(id); }
  function getComprasByParticipante(idParticipante) { return apiFetch('/compras/participante/' + encodeURIComponent(idParticipante), { method: 'GET' }); }
  function getComprasByIngresso(idIngresso) { return apiFetch('/compras/ingresso/' + encodeURIComponent(idIngresso), { method: 'GET' }); }
  function createCompra(dados) { return apiFetch('/compras', { method: 'POST', body: { compra: dados } }); }
  function updateCompra(id, dados) { return apiFetch('/compras/' + encodeURIComponent(id), { method: 'PUT', body: { compra: dados } }); }
  function deleteCompra(id) { return apiFetch('/compras/' + encodeURIComponent(id), { method: 'DELETE' }); }

  // ---------------------------------------------------------------------------
  // Exporta para escopo global
  // ---------------------------------------------------------------------------
  const Api = {
    // config
    API_BASE,
    TOKEN_KEY,
    USER_KEY,
    // token/user
    getToken,
    setToken,
    removeToken,
    getUser,
    setUser,
    removeUser,
    isAuthenticated,
    // core
    apiFetch,
    resolveUrl,
    // auth
    authLogin,
    authRegister,
    authMe,
    login: authLogin,
    register: authRegister,
    me: authMe,
    // locais
    getLocais,
    countLocais,
    getLocal,
    getLocalById,
    searchLocais,
    searchLocaisQuery,
    createLocal,
    updateLocal,
    deleteLocal,
    // eventos
    getEventos,
    countEventos,
    getEvento,
    getEventoById,
    getEventosByLocal,
    createEvento,
    updateEvento,
    deleteEvento,
    // participantes
    getParticipantes,
    countParticipantes,
    getParticipante,
    getParticipanteById,
    getParticipanteByEmail,
    getParticipanteByCpf,
    createParticipante,
    updateParticipante,
    deleteParticipante,
    // ingressos
    getIngressos,
    countIngressos,
    getIngresso,
    getIngressoById,
    getIngressosByEvento,
    getIngressoByTipoEvento,
    createIngresso,
    updateIngresso,
    deleteIngresso,
    // compras
    getCompras,
    countCompras,
    getCompra,
    getCompraById,
    getComprasByParticipante,
    getComprasByIngresso,
    createCompra,
    updateCompra,
    deleteCompra,
  };

  // Alias genérico para dashboard antigo que usa Api.get('/entity')
  Api.get = function(path, opts) { return apiFetch(path, { method: 'GET', ...(opts||{}) }); };
  Api.post = function(path, body, opts) { return apiFetch(path, { method: 'POST', body: body, ...(opts||{}) }); };
  Api.put = function(path, body, opts) { return apiFetch(path, { method: 'PUT', body: body, ...(opts||{}) }); };
  Api.del = function(path, opts) { return apiFetch(path, { method: 'DELETE', ...(opts||{}) }); };
  Api.isAuthenticated = isAuthenticated;

  // Expõe globalmente: window.Api e também funções soltas para compatibilidade
  global.Api = Api;
  global.apiFetch = apiFetch;
  global.getToken = getToken;
  global.setToken = setToken;
  global.removeToken = removeToken;
  global.getUser = getUser;
  global.setUser = setUser;
  global.isAuthenticated = isAuthenticated;

})(window);
