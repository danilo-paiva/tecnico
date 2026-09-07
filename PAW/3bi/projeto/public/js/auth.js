/**
 * auth.js — Lógica de autenticação para o front-end
 * Vanilla JS | fetch | localStorage | api.js
 *
 * Requer que api.js seja carregado ANTES deste arquivo:
 *   <script src="js/api.js"></script>
 *   <script src="js/auth.js"></script>
 *
 * Cobre:
 *  - Login (login.html): submit -> POST /auth/login -> salva token/usuario -> redirect dashboard.html
 *  - Verificação de páginas protegidas: checkAuth(), verifyAuth(), logout(), displayUser()
 */

(function () {
  'use strict';

  // ---------------------------------------------------------------------------
  // Utilidades de DOM / Mensagens
  // ---------------------------------------------------------------------------

  /**
   * Busca elemento por múltiplos seletores possíveis (tolerante a diferenças de HTML).
   * @param {string[]} selectors
   * @returns {HTMLElement|null}
   */
  function pickElement(selectors) {
    for (const sel of selectors) {
      const el = document.querySelector(sel);
      if (el) return el;
    }
    return null;
  }

  function showError(message, containerSelectors) {
    const el = pickElement(containerSelectors || [
      '#errorMessage', '#error-message', '#erro', '.error-message',
      '#loginError', '#authError', '[data-error]'
    ]);
    const text = (message && typeof message === 'string') ? message : (message?.message || 'Erro ao realizar login.');
    if (el) {
      el.textContent = text;
      el.style.display = 'block';
      el.classList.remove('hidden');
      el.classList.add('show');
    } else {
      // fallback: alert ou console
      console.error('[auth] erro:', text);
    }
  }

  function hideError(containerSelectors) {
    const el = pickElement(containerSelectors || ['#errorMessage', '#error-message', '#erro', '.error-message', '#loginError']);
    if (el) {
      el.textContent = '';
      el.style.display = 'none';
      el.classList.add('hidden');
      el.classList.remove('show');
    }
  }

  function showSuccess(message, containerSelectors) {
    const el = pickElement(containerSelectors || ['#successMessage', '#success-message', '.success-message']);
    if (el) {
      el.textContent = message;
      el.style.display = 'block';
      el.classList.remove('hidden');
    }
  }

  function setLoading(form, isLoading) {
    if (!form) return;
    const btn = form.querySelector('button[type="submit"]') || pickElement(['#loginBtn', '#btnLogin', 'button[type="submit"]']);
    const inputs = form.querySelectorAll('input, button');
    if (btn) {
      btn.disabled = isLoading;
      if (!btn.dataset.originalText) btn.dataset.originalText = btn.textContent;
      btn.textContent = isLoading ? 'Entrando...' : btn.dataset.originalText;
    }
    inputs.forEach((i) => { if (i !== btn) i.disabled = isLoading; });
  }

  // ---------------------------------------------------------------------------
  // Auth helpers (dependem de api.js)
  // ---------------------------------------------------------------------------

  function requireApi() {
    if (typeof window.apiFetch !== 'function' || typeof window.getToken !== 'function') {
      console.error('[auth.js] api.js não foi carregado. Certifique-se de incluir <script src="js/api.js"></script> antes de auth.js');
      return false;
    }
    return true;
  }

  /**
   * Verifica se existe token; se não houver redireciona para login.html.
   * Use no topo de páginas protegidas (dashboard, CRUDs).
   * @param {object} [opts]
   * @param {string} [opts.redirectTo='login.html']
   * @param {boolean} [opts.verifyServer=false] - se true, chama /auth/me para validar token no servidor
   * @returns {boolean} true se autenticado (e não redirecionou), false se redirecionou
   */
  function checkAuth(opts) {
    opts = opts || {};
    const redirectTo = opts.redirectTo || 'login.html';
    if (!requireApi()) return false;

    const token = window.getToken();
    if (!token) {
      // guarda página atual para voltar depois do login
      try { sessionStorage.setItem('redirect_after_login', window.location.pathname + window.location.search); } catch (_e) {}
      window.location.href = redirectTo;
      return false;
    }

    // Se solicitado, valida no servidor assincronamente
    if (opts.verifyServer) {
      verifyAuth().catch(function () { /* verifyAuth já trata 401 + redirect */ });
    }

    return true;
  }

  /**
   * Valida o token no servidor chamando GET /auth/me.
   * - Em sucesso, atualiza localStorage com dados do usuário retornados.
   * - Em 401, limpa storage e redireciona para login.html.
   * @returns {Promise<object>} dados do usuário
   */
  async function verifyAuth() {
    if (!requireApi()) throw new Error('api.js não carregado');
    const token = window.getToken();
    if (!token) throw new Error('Sem token');

    try {
      const res = await window.apiFetch('/auth/me', { method: 'GET' });
      // Resposta padrão: { success, data: { id, email, nome, ... } }
      const userData = res && (res.data || res.usuario || res.user || res);
      // Normaliza para salvar formato consistente { id, email, nome } ou { idParticipante ... }
      if (userData) {
        // se data contém id/email/nome (payload JWT), mapeia para usuario
        const normalized = userData.data ? userData.data : userData;
        // preserva o que já existe em storage, mas atualiza com o frescor do servidor
        try {
          // tenta mesclar com usuário existente
          const existing = window.getUser && window.getUser();
          const merged = { ...(existing || {}), ...normalized };
          if (window.setUser) window.setUser(merged);
          else localStorage.setItem('usuario', JSON.stringify(merged));
        } catch (_e) { /* ignore */ }
        // Atualiza exibição
        displayUser();
      }
      // limpa flag de redirect loop
      try { sessionStorage.removeItem('__redirecting_401'); } catch (_e) {}
      return res;
    } catch (err) {
      // 401 já é tratado por apiFetch (removeToken + redirect). Apenas propaga.
      if (err && err.status === 401) {
        // apiFetch já redirecionou; evita mensagem duplicada
        throw err;
      }
      // Outros erros: mostra mas não desloga
      console.warn('[verifyAuth] falha ao verificar sessão:', err.message);
      throw err;
    }
  }

  /**
   * Realiza logout: limpa token/usuario e redireciona para login.html.
   * @param {string} [redirectTo='login.html']
   */
  function logout(redirectTo) {
    redirectTo = redirectTo || 'login.html';
    try {
      if (window.removeToken) window.removeToken();
      else {
        localStorage.removeItem('token');
        localStorage.removeItem('usuario');
        localStorage.removeItem('user');
      }
      sessionStorage.removeItem('__redirecting_401');
      sessionStorage.removeItem('redirect_after_login');
    } catch (_e) { /* ignore */ }
    window.location.href = redirectTo;
  }

  /**
   * Exibe nome/email do usuário logado em elementos comuns.
   * Procura por: #userName, #user-name, #nomeUsuario, [data-user-name], #userEmail etc.
   * Se não encontrar, não faz nada (sem erro).
   */
  function displayUser() {
    let user = null;
    try { user = window.getUser ? window.getUser() : JSON.parse(localStorage.getItem('usuario') || localStorage.getItem('user') || 'null'); } catch (_e) { user = null; }
    if (!user || typeof user !== 'object') return;

    const nome = user.nome || user.name || user.nomeUsuario || user.email || '';
    const email = user.email || '';

    const nameEls = document.querySelectorAll('#userName, #user-name, #nomeUsuario, #nome-usuario, [data-user-name], .user-name');
    nameEls.forEach(function (el) { el.textContent = nome; });

    const emailEls = document.querySelectorAll('#userEmail, #user-email, [data-user-email], .user-email');
    emailEls.forEach(function (el) { el.textContent = email; });

    // Também preenche elemento único se existir apenas um
    const singleName = pickElement(['#userName', '#user-name', '#nomeUsuario']);
    if (singleName && !singleName.textContent) singleName.textContent = nome;
  }

  // ---------------------------------------------------------------------------
  // Lógica de login.html
  // ---------------------------------------------------------------------------

  function initLoginPage() {
    if (!requireApi()) return;

    // Evita duplo submit: login.html possui handler inline próprio.
    // Se a página já marcou o form como tratado, não anexa segundo listener.
    try {
      if (window.__loginFormHandled) return;
    } catch (_e) {}

    // Se já estiver autenticado, opcionalmente redireciona direto para dashboard
    // Só faz se estiver em login.html / register.html para evitar loop em outras páginas
    const path = (window.location.pathname || '').toLowerCase();
    const isLoginLike = path.endsWith('login.html') || path.endsWith('/login') || path.endsWith('/login.html'.toLowerCase());
    // Não força redirect automático se o usuário acessou login intencionalmente com ?force=1 ou ?logout
    const params = new URLSearchParams(window.location.search || '');
    const forceStay = params.has('force') || params.has('logout');

    if (isLoginLike && !forceStay && window.isAuthenticated && window.isAuthenticated()) {
      // Valida rapidamente se token ainda é válido antes de redirecionar
      // Se preferir redirect imediato sem verificar servidor, descomente a linha abaixo e comente o bloco verify:
      // window.location.href = 'dashboard.html'; return;
      // Verifica no servidor para evitar redirect com token expirado
      window.apiFetch('/auth/me', { method: 'GET' }).then(function () {
        const redirect = sessionStorage.getItem('redirect_after_login') || 'dashboard.html';
        try { sessionStorage.removeItem('redirect_after_login'); } catch (_e) {}
        window.location.href = redirect;
      }).catch(function () {
        // token inválido — permanece no login, apiFetch já limpou
      });
    }

    // Exibe erro pendente deixado por apiFetch 401
    try {
      const pendingError = sessionStorage.getItem('auth_error');
      if (pendingError && isLoginLike) {
        showError(pendingError);
        sessionStorage.removeItem('auth_error');
        sessionStorage.removeItem('__redirecting_401');
      }
    } catch (_e) {}

    // Encontra formulário de login — tolerante a variações de id
    const form = pickElement([
      '#loginForm', '#formLogin', '#form-login', 'form[data-login]',
      'form#login', 'form.login-form'
    ]) || document.querySelector('form');

    // Se não houver form (página não é login), encerra aqui
    if (!form) return;
    // Guarda anti-duplo-bind (aula 2: um listener por evento)
    if (form.dataset.authJsBound === '1') return;
    form.dataset.authJsBound = '1';
    // Heurística: só trata como login se houver campo de senha/email
    const hasPasswordField = form.querySelector('input[type="password"]') || form.querySelector('#senha') || form.querySelector('[name="senha"]');
    const hasEmailField = form.querySelector('input[type="email"]') || form.querySelector('#email') || form.querySelector('[name="email"]');
    if (!hasPasswordField && !hasEmailField) return;

    // Mapeia campos — tolerante a id/name diferentes
    function getFieldValue(names) {
      for (const n of names) {
        const el = form.querySelector('#' + CSS.escape(n)) || form.querySelector('[name="' + n + '"]') || document.getElementById(n);
        if (el && el.value !== undefined) return el.value.trim();
      }
      return '';
    }

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      hideError();

      const email = getFieldValue(['email', 'e-mail', 'usuario', 'userEmail']);
      const senha = (function () {
        // senha pode estar em #senha, #password, [name="senha"] etc — precisa valor sem trim agressivo
        const selectors = ['#senha', '#password', '[name="senha"]', '[name="password"]', 'input[type="password"]'];
        for (const sel of selectors) {
          const el = form.querySelector(sel);
          if (el && typeof el.value === 'string') return el.value;
        }
        return '';
      })();

      if (!email || !senha) {
        showError('Preencha e-mail e senha.');
        return;
      }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError('E-mail inválido.');
        return;
      }

      setLoading(form, true);
      try {
        // api.js expõe authLogin; fallback para fetch direto se não houver
        let result;
        if (window.Api && typeof window.Api.authLogin === 'function') {
          result = await window.Api.authLogin(email, senha);
        } else if (typeof window.apiFetch === 'function') {
          result = await window.apiFetch('/auth/login', {
            method: 'POST',
            body: { email: email, senha: senha }
          });
        } else {
          throw new Error('Helper de API não carregado.');
        }

        // Espera { success, data: { token, usuario, expiresIn } } ou variações
        const payload = result && (result.data || result);
        const token = payload && (payload.token || payload.accessToken || result.token);
        const usuario = payload && (payload.usuario || payload.user || payload.participante || result.usuario);

        if (!token) {
          throw new Error((result && result.message) || 'Login falhou: token não retornado pela API.');
        }

        // Salva token e usuário
        window.setToken(token);
        if (usuario) {
          if (window.setUser) window.setUser(usuario);
          else localStorage.setItem('usuario', JSON.stringify(usuario));
        } else {
          // Se API não retornou usuario, decodifica payload do JWT para extrair nome/email (fallback)
          try {
            const base64 = token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/');
            const json = JSON.parse(atob(base64));
            const fallbackUser = { id: json.sub, email: json.email || email, nome: json.nome || '' };
            if (window.setUser) window.setUser(fallbackUser);
            else localStorage.setItem('usuario', JSON.stringify(fallbackUser));
          } catch (_e) { /* ignore */ }
        }

        // Limpa flags de erro
        try {
          sessionStorage.removeItem('auth_error');
          sessionStorage.removeItem('__redirecting_401');
        } catch (_e) {}

        showSuccess('Login realizado! Redirecionando...');

        // Redireciona: respeita redirect_after_login se existir
        let target = 'dashboard.html';
        try {
          const saved = sessionStorage.getItem('redirect_after_login');
          if (saved && saved !== window.location.pathname && !saved.includes('login.html')) {
            target = saved;
            sessionStorage.removeItem('redirect_after_login');
          }
        } catch (_e) {}

        // Pequeno delay para UX
        setTimeout(function () { window.location.href = target; }, 600);

      } catch (err) {
        // Extrai mensagem amigável
        let msg = err && err.message ? err.message : 'Falha ao fazer login. Verifique suas credenciais.';
        // Se payload contém message específica
        if (err && err.payload && err.payload.message) msg = err.payload.message;
        // Mensagens comuns
        if (err && err.status === 401) msg = 'E-mail ou senha incorretos.';
        else if (err && err.status === 0) msg = 'Não foi possível conectar ao servidor. Verifique se a API está rodando.';
        else if (err && err.status >= 500) msg = 'Erro interno no servidor. Tente novamente mais tarde.';

        showError(msg);
        console.error('[login] erro:', err);
      } finally {
        setLoading(form, false);
      }
    });

    // Também vincula botão de logout se existir na mesma página
    bindLogoutButtons();
  }

  function bindLogoutButtons() {
    const logoutSelectors = ['#logoutBtn', '#btnLogout', '#logout', '[data-logout]', '.logout-btn', 'a[data-action="logout"]'];
    logoutSelectors.forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (btn) {
        if (btn.dataset.logoutBound) return;
        btn.dataset.logoutBound = '1';
        btn.addEventListener('click', function (e) {
          e.preventDefault();
          logout();
        });
      });
    });
  }

  // ---------------------------------------------------------------------------
  // Inicialização automática por tipo de página
  // ---------------------------------------------------------------------------

  document.addEventListener('DOMContentLoaded', function () {
    const path = (window.location.pathname || '').toLowerCase();

    const isLoginPage = path.endsWith('login.html') || path.endsWith('/login');
    const isRegisterPage = path.endsWith('register.html') || path.endsWith('cadastro.html') || path.endsWith('/register');
    const isPublicPage = isLoginPage || isRegisterPage || path.endsWith('index.html') || path === '/' || path === '';

    // Sempre tenta exibir nome do usuário se houver
    displayUser();
    bindLogoutButtons();

    if (isLoginPage || document.querySelector('#loginForm') || document.querySelector('form[data-login]')) {
      initLoginPage();
      return;
    }

    // Páginas protegidas: verifica auth
    // Heurística: se a página tem atributo data-protected ou está em lista conhecida
    const protectedHints = document.querySelector('[data-protected]') ||
      path.includes('dashboard') ||
      path.includes('locais') ||
      path.includes('eventos') ||
      path.includes('participantes') ||
      path.includes('ingressos') ||
      path.includes('compras');

    // Se não for página pública e houver hint de proteção, ou se explicitamente marcada, valida
    // Para não quebrar páginas públicas sem hint, só auto-verifica se for dashboard ou data-protected
    if (!isPublicPage) {
      const shouldAutoCheck = protectedHints || document.body.hasAttribute('data-auth') || document.documentElement.hasAttribute('data-auth');
      if (shouldAutoCheck) {
        const ok = checkAuth({ redirectTo: 'login.html', verifyServer: false });
        if (ok) {
          // Valida token no servidor em background e atualiza display
          verifyAuth().catch(function () {});
          displayUser();
        }
      } else {
        // Mesmo sem auto-check, se houver token tenta validar silenciosamente para manter usuario atualizado
        if (requireApi() && window.getToken && window.getToken()) {
          verifyAuth().catch(function () {});
        }
      }
    } else {
      // Em páginas públicas, se houver token, ainda tenta refrescar dados do usuário silenciosamente
      if (requireApi() && window.getToken && window.getToken()) {
        // não redireciona em caso de falha — apenas loga
        verifyAuth().catch(function () {});
      }
    }
  });

  // Expõe funções globais para uso inline: onclick="logout()" etc.
  window.checkAuth = checkAuth;
  window.verifyAuth = verifyAuth;
  window.logout = logout;
  window.displayUser = displayUser;
  window.initLoginPage = initLoginPage;

  // Helpers globais showAlert/hideAlert compatíveis com login.html/register.html (esperam elemento)
  window.showAlert = function(a,b,c){
    // suporta showAlert(alertEl, 'msg', 'error') e showAlert('msg', 'error')
    var el = null, msg = '', type = 'error';
    if (a && a.nodeType === 1) { el = a; msg = b || ''; type = c || 'error'; }
    else { msg = a || ''; type = b || 'error'; el = pickElement(['#alert', '#errorMessage', '.alert']); }
    if (!el) { console.error('[showAlert]', msg); return; }
    el.textContent = msg;
    el.className = 'alert alert-' + (type==='error' ? 'danger' : type);
    el.hidden = false; el.style.display = 'flex';
  };
  window.hideAlert = function(a){
    var el = (a && a.nodeType===1) ? a : pickElement(['#alert', '#errorMessage', '.alert']);
    if (!el) return;
    el.hidden = true; el.style.display='none'; el.textContent='';
  };
  window.showError = window.showAlert; window.hideError = window.hideAlert;

  // Compatibilidade: páginas antigas usam Auth.requireAuth etc., e Auth.login/Auth.register
  async function authLoginWrapper(email, senha){
    if (!requireApi()) throw new Error('api.js não carregado');
    var result;
    if (window.Api && typeof window.Api.authLogin === 'function') {
      result = await window.Api.authLogin(email, senha);
    } else {
      result = await window.apiFetch('/auth/login', { method:'POST', body:{ email: email, senha: senha } });
    }
    // result pode ser {success,data:{token,usuario}} ou direto {token,...}
    var payload = result && result.data ? result.data : result;
    var token = payload && (payload.token || payload.accessToken || payload.jwt);
    var usuario = payload && (payload.usuario || payload.user || payload.data);
    if (!token && result && result.token) token = result.token;
    // Fallback: decodifica JWT para extrair usuario se não veio
    if (!usuario && token) {
      try { var parts = token.split('.'); var b64 = parts[1].replace(/-/g,'+').replace(/_/g,'/'); var json = JSON.parse(atob(b64)); usuario = { id: json.sub, idParticipante: json.sub, email: json.email, nome: json.nome }; } catch(e){}
    }
    if (!token) throw new Error('Resposta de login sem token');
    if (window.setToken) window.setToken(token); else localStorage.setItem('token', token);
    if (usuario && window.setUser) window.setUser(usuario);
    return result;
  }
  async function authRegisterWrapper(data){
    if (!requireApi()) throw new Error('api.js não carregado');
    if (window.Api && typeof window.Api.authRegister === 'function') return await window.Api.authRegister(data);
    return await window.apiFetch('/auth/register', { method:'POST', body: data });
  }
  window.Auth = {
    requireAuth: checkAuth,
    verifyAuth: verifyAuth,
    logout: logout,
    displayUser: displayUser,
    initLoginPage: initLoginPage,
    redirectIfAuthenticated: function(target) {
      if (window.isAuthenticated && window.isAuthenticated()) {
        window.apiFetch('/auth/me', { method: 'GET' }).then(function(){ window.location.href = target || 'dashboard.html'; }).catch(function(){});
      }
    },
    getToken: function(){ return window.getToken ? window.getToken() : localStorage.getItem('token'); },
    getUser: function(){ return window.getUser ? window.getUser() : null; },
    login: authLoginWrapper,
    register: authRegisterWrapper
  };

})();
