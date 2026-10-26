/**
 * Cliente HTTP da API (aula paw03x04 / paw04x01).
 * Suporta GET, POST, PUT e DELETE com token Bearer.
 */
class ApiError extends Error {
    constructor(status, mensagem, dados = null) {
        super(mensagem);
        this.name = "ApiError";
        this.status = status;
        this.dados = dados;
    }
}

class ApiService {
    #baseUrl;
    #token;

    constructor(baseUrl, token = null) {
        this.#baseUrl = String(baseUrl).replace(/\/$/, "");
        this.#token = token;
    }

    get baseUrl() {
        return this.#baseUrl;
    }

    get token() {
        return this.#token;
    }

    set token(valor) {
        this.#token = valor;
    }

    #montarUrl(caminho, query = null) {
        const url = new URL(`${this.#baseUrl}/${String(caminho).replace(/^\//, "")}`);
        if (query) {
            for (const [chave, valor] of Object.entries(query)) {
                if (valor !== undefined && valor !== null && String(valor).trim() !== "") {
                    url.searchParams.set(chave, valor);
                }
            }
        }
        return url.toString();
    }

    async #requisitar(metodo, caminho, { corpo = null, query = null } = {}) {
        const headers = { "Content-Type": "application/json" };
        if (this.#token) {
            headers.Authorization = `Bearer ${this.#token}`;
        }

        let resposta;
        try {
            resposta = await fetch(this.#montarUrl(caminho, query), {
                method: metodo,
                headers,
                body: corpo ? JSON.stringify(corpo) : undefined
            });
        } catch (erro) {
            throw new ApiError(
                0,
                "Não foi possível falar com a API. Confira se o servidor está rodando.",
                { causa: erro.message }
            );
        }

        const texto = await resposta.text();
        let json = null;
        if (texto) {
            try {
                json = JSON.parse(texto);
            } catch {
                json = null;
            }
        }

        if (!resposta.ok) {
            const mensagem =
                json?.data?.mensagem ||
                json?.data?.detalhes?.mensagem ||
                json?.message ||
                `Erro HTTP ${resposta.status}`;
            throw new ApiError(resposta.status, mensagem, json?.data ?? null);
        }

        return json?.data ?? json;
    }

    async get(caminho, query = null) {
        return this.#requisitar("GET", caminho, { query });
    }

    async post(caminho, corpo, query = null) {
        return this.#requisitar("POST", caminho, { corpo, query });
    }

    async put(caminho, id, corpo) {
        return this.#requisitar("PUT", `${caminho}/${id}`, { corpo });
    }

    async delete(caminho, id) {
        return this.#requisitar("DELETE", `${caminho}/${id}`);
    }
}

window.ApiService = ApiService;
window.ApiError = ApiError;
