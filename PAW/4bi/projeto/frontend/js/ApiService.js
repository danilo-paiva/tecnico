/**
 * Cliente HTTP da API (aula paw03x04 / paw04x01).
 * Suporta GET, POST, PUT e DELETE com token Bearer.
 */

/** Erro da API com o status HTTP (mesmo formato do ErrorResponse da aula). */
function ApiError(status, mensagem, dados) {
    const erro = new Error(mensagem);
    erro.name = "ApiError";
    erro.status = status;
    erro.dados = dados || null;
    return erro;
}

class ApiService {
    #baseUrl;
    #token;

    constructor(baseUrl, token) {
        this.#baseUrl = String(baseUrl).replace(/\/$/, "");
        this.#token = token || null;
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

    #montarUrl(caminho, query) {
        const limpo = String(caminho).replace(/^\//, "");
        let url = this.#baseUrl + "/" + limpo;

        if (query) {
            const partes = [];
            for (const chave in query) {
                const valor = query[chave];
                if (valor !== undefined && valor !== null && String(valor).trim() !== "") {
                    partes.push(chave + "=" + encodeURIComponent(valor));
                }
            }
            if (partes.length > 0) {
                url = url + "?" + partes.join("&");
            }
        }

        return url;
    }

    async #requisitar(metodo, caminho, opcoes) {
        const corpo = opcoes ? opcoes.corpo : null;
        const query = opcoes ? opcoes.query : null;
        const url = this.#montarUrl(caminho, query);

        const headers = {
            "Content-Type": "application/json"
        };
        if (this.#token) {
            headers["Authorization"] = "Bearer " + this.#token;
        }

        const configuracao = {
            method: metodo,
            headers: headers
        };
        if (corpo) {
            configuracao.body = JSON.stringify(corpo);
        }

        let resposta;
        try {
            resposta = await fetch(url, configuracao);
        } catch (erro) {
            throw ApiError(
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
            } catch (erro) {
                json = null;
            }
        }

        if (!resposta.ok) {
            const dados = json ? json.data : null;
            let mensagem = "Erro HTTP " + resposta.status;

            if (dados && dados.mensagem) {
                mensagem = dados.mensagem;
            } else if (dados && dados.detalhes && dados.detalhes.mensagem) {
                mensagem = dados.detalhes.mensagem;
            } else if (json && json.message) {
                mensagem = json.message;
            }

            throw ApiError(resposta.status, mensagem, dados);
        }

        return json && json.data ? json.data : json;
    }

    async get(caminho, query) {
        return this.#requisitar("GET", caminho, { query });
    }

    async post(caminho, corpo, query) {
        return this.#requisitar("POST", caminho, { corpo, query });
    }

    async put(caminho, id, corpo) {
        return this.#requisitar("PUT", caminho + "/" + id, { corpo });
    }

    async delete(caminho, id) {
        return this.#requisitar("DELETE", caminho + "/" + id);
    }
}

window.ApiService = ApiService;
window.ApiError = ApiError;
