// login.js — tela de login (aulas 2 e 3: DOM + fetch via ApiService).
import ApiService from './ApiService.js';
import { salvarSessao, obterToken } from './sessao.js';

document.addEventListener('DOMContentLoaded', () => {
  if (obterToken()) {
    window.location.href = 'dashboard.html';
    return;
  }

  const form = document.getElementById('loginForm');
  const alerta = document.getElementById('alert');
  const botao = document.getElementById('btnSubmit');
  const textoBotao = document.getElementById('btnText');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = document.getElementById('email').value.trim();
    const senha = document.getElementById('senha').value;
    if (!email || !senha) {
      mostrar('Preencha e-mail e senha.', 'danger');
      return;
    }

    botao.disabled = true;
    textoBotao.textContent = 'Entrando...';

    const api = new ApiService(); // login é público: sem token
    const res = await api.post('/login', { participante: { email, senha } });

    if (res && res.success) {
      salvarSessao(res.data.token, res.data.participante);
      mostrar('Login realizado! Redirecionando...', 'success');
      setTimeout(() => { window.location.href = 'dashboard.html'; }, 600);
    } else {
      mostrar((res && res.message) || 'Credenciais inválidas.', 'danger');
      botao.disabled = false;
      textoBotao.textContent = 'Entrar';
    }
  });

  function mostrar(msg, tipo) {
    alerta.textContent = msg;
    alerta.className = 'alert alert-' + tipo;
    alerta.style.display = 'flex';
  }
});
