/**
 * Tela de login / criação de conta.
 */
(function iniciarLogin() {
    let modo = "login";

    const form = document.getElementById("formAuth");
    const mensagem = document.getElementById("mensagem");
    const botao = document.getElementById("btnEnviar");
    const titulo = document.getElementById("tituloAcao");
    const subtitulo = document.getElementById("subtituloAcao");
    const campoNome = document.getElementById("campoNome");
    const campoTelefone = document.getElementById("campoTelefone");
    const apiEscolhida = document.getElementById("apiEscolhida");

    if (Sessao.estaLogado()) {
        window.location.replace("index.html");
        return;
    }

    function mostrarMensagem(texto, tipo = "erro") {
        mensagem.textContent = texto;
        mensagem.className = `form-msg show ${tipo}`;
    }

    function limparMensagem() {
        mensagem.className = "form-msg";
        mensagem.textContent = "";
    }

    function atualizarApi() {
        const api = Sessao.apiConfig();
        apiEscolhida.textContent = `${api.nome} · ${api.base}`;
        document.querySelectorAll("#seletorApi button").forEach((item) => {
            item.classList.toggle("active", item.dataset.api === api.id);
        });

        Sessao.testarApi().then((online) => {
            if (!online) {
                mostrarMensagem(
                    `${api.nome} não respondeu em ${api.origem}. Ligue o servidor para entrar.`,
                    "erro"
                );
            } else if (mensagem.textContent.includes("não respondeu")) {
                limparMensagem();
            }
        });
    }

    function trocarModo(novoModo) {
        modo = novoModo;
        const registrando = modo === "registrar";

        document.querySelectorAll("#tabsAuth button").forEach((item) => {
            item.classList.toggle("active", item.dataset.modo === modo);
        });

        campoNome.hidden = !registrando;
        campoTelefone.hidden = !registrando;
        titulo.textContent = registrando ? "Criar conta" : "Entrar no painel";
        subtitulo.textContent = registrando
            ? "O cadastro cria um organizador com perfil comum."
            : "Use uma conta de organizador para acessar o painel.";
        botao.textContent = registrando ? "Criar conta e entrar" : "Entrar";
        document.getElementById("senha").autocomplete = registrando ? "new-password" : "current-password";
        limparMensagem();
    }

    document.querySelectorAll("#tabsAuth button").forEach((item) => {
        item.addEventListener("click", () => trocarModo(item.dataset.modo));
    });

    document.querySelectorAll("#seletorApi button").forEach((item) => {
        item.addEventListener("click", () => {
            Sessao.definirApi(item.dataset.api);
            atualizarApi();
            limparMensagem();
        });
    });

    form.addEventListener("submit", async (evento) => {
        evento.preventDefault();
        limparMensagem();

        const email = document.getElementById("email").value.trim();
        const senha = document.getElementById("senha").value;
        const nome = document.getElementById("nome").value.trim();
        const telefone = document.getElementById("telefone").value.trim();

        if (!email || !senha) {
            mostrarMensagem("Informe e-mail e senha.");
            return;
        }
        if (modo === "registrar" && nome.length < 3) {
            mostrarMensagem("Informe seu nome completo (mínimo 3 caracteres).");
            return;
        }
        if (senha.length < 6) {
            mostrarMensagem("A senha deve ter pelo menos 6 caracteres.");
            return;
        }

        botao.disabled = true;
        botao.textContent = "Aguarde…";

        try {
            const api = Sessao.api();
            const caminho = modo === "login" ? "auth/login" : "auth/registrar";
            const corpo = modo === "login"
                ? { organizador: { email, senha } }
                : { organizador: { nome, email, senha, telefone: telefone || null } };

            const dados = await api.post(caminho, corpo);
            const organizador = dados.organizadores?.[0];

            if (!dados.token || !organizador) {
                throw new Error("A API não retornou o token de acesso.");
            }

            Sessao.entrar(dados.token, organizador);
            window.location.replace("index.html");
        } catch (erro) {
            console.error(erro);
            const texto = erro?.status === 0
                ? `Não foi possível falar com ${Sessao.apiConfig().nome}. Confira se o servidor está rodando.`
                : erro.message;
            mostrarMensagem(texto);
            botao.disabled = false;
            botao.textContent = modo === "registrar" ? "Criar conta e entrar" : "Entrar";
        }
    });

    // Mensagem quando a sessão expira em outra página.
    const parametros = new URLSearchParams(window.location.search);
    if (parametros.get("sessao") === "expirada") {
        mostrarMensagem("Sua sessão expirou. Entre novamente.", "erro");
    }

    atualizarApi();
})();
