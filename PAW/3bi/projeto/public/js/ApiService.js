// ApiService.js — aula paw03x04: centraliza o consumo da API REST.
// Todos os metodos autenticados enviam `Authorization: Bearer <token>`.
// Uso: const api = new ApiService(token); api.token = novoToken; ...
export default class ApiService {
  #token; // atributo privado (aula paw03x02)

  constructor(token = null) {
    this.#token = token;
  }

  get token() { return this.#token; }
  set token(value) { this.#token = value; }

  // Monta os headers padrao, com Bearer quando houver token.
  #headers() {
    const headers = { "Content-Type": "application/json" };
    if (this.#token) headers["Authorization"] = `Bearer ${this.#token}`;
    return headers;
  }

  // GET simples, sem headers (APIs publicas). Mantido da aula.
  async simpleGet(uri) {
    try {
      const response = await fetch(uri);
      const jsonObj = await response.json();
      return jsonObj;
    } catch (error) {
      console.error("Erro ao buscar dados:", error.message);
      return [];
    }
  }

  // GET autenticado.
  async get(uri) {
    try {
      const response = await fetch(uri, { method: "GET", headers: this.#headers() });
      const jsonObj = await response.json();
      return jsonObj;
    } catch (error) {
      console.error("Erro ao buscar dados:", error.message);
      return [];
    }
  }

  // GET autenticado em uri/id.
  async getById(uri, id) {
    try {
      const fullUri = `${uri}/${id}`;
      const response = await fetch(fullUri, { method: "GET", headers: this.#headers() });
      if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
      const jsonObj = await response.json();
      return jsonObj;
    } catch (error) {
      console.error("Erro ao buscar por ID:", error.message);
      return null;
    }
  }

  // POST autenticado (quando ha token) com corpo JSON.
  async post(uri, jsonObject) {
    try {
      const response = await fetch(uri, {
        method: "POST",
        headers: this.#headers(),
        body: JSON.stringify(jsonObject),
      });
      const jsonObj = await response.json();
      return jsonObj;
    } catch (error) {
      console.error("Erro ao cadastrar:", error.message);
      return [];
    }
  }

  // PUT autenticado em uri/id.
  async put(uri, id, jsonObject) {
    try {
      const fullUri = `${uri}/${id}`;
      const response = await fetch(fullUri, {
        method: "PUT",
        headers: this.#headers(),
        body: JSON.stringify(jsonObject),
      });
      const jsonObj = await response.json();
      return jsonObj;
    } catch (error) {
      console.error("Erro ao atualizar:", error.message);
      return null;
    }
  }

  // DELETE autenticado em uri/id.
  async delete(uri, id) {
    try {
      const fullUri = `${uri}/${id}`;
      const response = await fetch(fullUri, { method: "DELETE", headers: this.#headers() });
      if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
      const jsonObj = await response.json().catch(() => null);
      return jsonObj;
    } catch (error) {
      console.error("Erro ao excluir:", error.message);
      return null;
    }
  }
}
