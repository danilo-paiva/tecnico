/**
 * Sessão do frontend: API selecionada, token JWT e usuário logado.
 * O mesmo token vale nas duas APIs (Node e Flask usam a mesma chave JWT).
 */
const APIS = {
    js: {
        id: "js",
        nome: "Node.js + Express",
        curto: "Node",
        base: "http://localhost:3000/api/v1",
        origem: "localhost:3000"
    },
    flask: {
        id: "flask",
        nome: "Python + Flask",
        curto: "Flask",
        base: "http://localhost:8080/api/v1",
        origem: "localhost:8080"
    }
};

const CHAVE_API = "pulse.api";
const CHAVE_TOKEN = "pulse.token";
const CHAVE_USUARIO = "pulse.usuario";

const Sessao = {
    APIS,

    apiAtual() {
        const escolhida = localStorage.getItem(CHAVE_API);
        return APIS[escolhida] ? escolhida : "js";
    },

    apiConfig() {
        return APIS[this.apiAtual()];
    },

    definirApi(id) {
        if (!APIS[id]) return;
        localStorage.setItem(CHAVE_API, id);
    },

    token() {
        return localStorage.getItem(CHAVE_TOKEN);
    },

    usuario() {
        try {
            return JSON.parse(localStorage.getItem(CHAVE_USUARIO) || "null");
        } catch {
            return null;
        }
    },

    estaLogado() {
        return Boolean(this.token());
    },

    entrar(token, organizador) {
        localStorage.setItem(CHAVE_TOKEN, token);
        localStorage.setItem(CHAVE_USUARIO, JSON.stringify(organizador));
    },

    sair() {
        localStorage.removeItem(CHAVE_TOKEN);
        localStorage.removeItem(CHAVE_USUARIO);
        window.location.replace("login.html");
    },

    expirar() {
        localStorage.removeItem(CHAVE_TOKEN);
        localStorage.removeItem(CHAVE_USUARIO);
        window.location.replace("login.html?sessao=expirada");
    },

    /** Retorna um ApiService já apontando para a API escolhida e com o token. */
    api() {
        return new ApiService(this.apiConfig().base, this.token());
    },

    /** Verifica se a API escolhida está online (health check). */
    async testarApi() {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 2500);
        try {
            const resposta = await fetch(`${this.apiConfig().base}/health`, {
                signal: controller.signal
            });
            return resposta.ok;
        } catch {
            return false;
        } finally {
            clearTimeout(timer);
        }
    }
};

window.Sessao = Sessao;
