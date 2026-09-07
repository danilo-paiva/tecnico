/**
 * ApiService.js — padrão da Aula 4 (3bi-resumo.md)
 * Classe ES6 centralizando GET/POST/PUT/DELETE com Bearer Token.
 * Uso com module: import ApiService from './ApiService.js';
 * Uso legado: window.ApiService (carregado via <script src="js/ApiService.js">).
 */
export default class ApiService {
  #token;

  constructor(token = null) {
    this.#token = token || (typeof localStorage !== 'undefined' ? localStorage.getItem('token') : null);
  }

  #headers(extra = {}) {
    const headers = { 'Content-Type': 'application/json', ...extra };
    if (this.#token) headers['Authorization'] = `Bearer ${this.#token}`;
    return headers;
  }

  // GET sem headers (APIs públicas) — padrão da aula
  async simpleGet(uri) {
    try {
      const response = await fetch(uri);
      return await response.json();
    } catch (error) {
      console.error('Erro:', error.message);
      return [];
    }
  }

  // GET autenticado (com token se existir)
  async get(uri) {
    try {
      const response = await fetch(uri, { method: 'GET', headers: this.#headers() });
      return await response.json();
    } catch (error) {
      console.error('Erro:', error.message);
      return [];
    }
  }

  // GET por id → uri/id
  async getById(uri, id) {
    try {
      const response = await fetch(`${uri}/${encodeURIComponent(id)}`, {
        method: 'GET',
        headers: this.#headers(),
      });
      if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
      return await response.json();
    } catch (error) {
      console.error('Erro:', error.message);
      return null;
    }
  }

  // POST com corpo JSON
  async post(uri, jsonObject) {
    try {
      const response = await fetch(uri, {
        method: 'POST',
        headers: this.#headers(),
        body: JSON.stringify(jsonObject),
      });
      return await response.json();
    } catch (error) {
      console.error('Erro:', error.message);
      return [];
    }
  }

  // PUT para uri/id
  async put(uri, id, jsonObject) {
    try {
      const response = await fetch(`${uri}/${encodeURIComponent(id)}`, {
        method: 'PUT',
        headers: this.#headers(),
        body: JSON.stringify(jsonObject),
      });
      return await response.json();
    } catch (error) {
      console.error('Erro:', error.message);
      return null;
    }
  }

  // DELETE em uri/id
  async delete(uri, id) {
    try {
      const response = await fetch(`${uri}/${encodeURIComponent(id)}`, {
        method: 'DELETE',
        headers: this.#headers(),
      });
      if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
      return await response.json().catch(() => null); // pode não ter body
    } catch (error) {
      console.error('Erro:', error.message);
      return null;
    }
  }

  get token() {
    return this.#token;
  }

  set token(value) {
    this.#token = value;
    try {
      if (value) localStorage.setItem('token', value);
    } catch (_e) { /* ignore */ }
  }
}

// Expõe global para páginas sem type="module" (compat com api.js/auth.js)
try {
  if (typeof window !== 'undefined') window.ApiService = ApiService;
} catch (_e) { /* ignore */ }
