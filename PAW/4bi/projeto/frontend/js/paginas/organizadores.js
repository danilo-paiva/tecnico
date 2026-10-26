/**
 * CRUD de organizadores com filtros por nome, e-mail e perfil.
 */
(function iniciarOrganizadores() {
    if (!window.layoutPronto) return;

    const usuario = Sessao.usuario() || {};
    const ehAdministrador = usuario.perfil === "administrador";
    const estado = { itens: [], editando: null };

    const tbody = document.getElementById("tbody");
    const contador = document.getElementById("contador");
    const formFiltros = document.getElementById("formFiltros");
    const form = document.getElementById("formOrganizador");
    const modalTitulo = document.getElementById("modalTitulo");
    const modalDescricao = document.getElementById("modalDescricao");
    const btnSalvar = document.getElementById("btnSalvar");
    const campoPerfil = document.getElementById("perfil");
    const dicaSenha = document.getElementById("dicaSenha");
    const btnNovo = document.getElementById("btnNovo");

    if (!ehAdministrador) {
        btnNovo.hidden = true;
        campoPerfil.disabled = true;
        campoPerfil.value = usuario.perfil || "comum";
    }

    function filtros() {
        return {
            nome: document.getElementById("fNome").value.trim(),
            email: document.getElementById("fEmail").value.trim(),
            perfil: document.getElementById("fPerfil").value
        };
    }

    function podeEditar(item) {
        return ehAdministrador || Number(item.idOrganizador) === Number(usuario.idOrganizador);
    }

    function podeExcluir(item) {
        return ehAdministrador && Number(item.idOrganizador) !== Number(usuario.idOrganizador);
    }

    function renderizar() {
        contador.textContent = `${estado.itens.length} registro(s)`;

        if (estado.itens.length === 0) {
            tbody.innerHTML = UI.linhasVazias(5, "Nenhum organizador encontrado", "Ajuste os filtros ou cadastre um novo organizador.");
            return;
        }

        tbody.innerHTML = estado.itens.map((item) => {
            const editar = podeEditar(item);
            const excluir = podeExcluir(item);

            return `
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <span class="avatar" style="width:32px;height:32px;font-size:11px">${UI.esc(UI.iniciais(item.nome))}</span>
                            <div>
                                <span class="cell-title">${UI.esc(item.nome)}</span>
                                <span class="cell-sub">#${item.idOrganizador}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="cell-title">${UI.esc(item.email)}</span>
                        <span class="cell-sub">${UI.esc(item.telefone || "sem telefone")}</span>
                    </td>
                    <td>${UI.badgeStatus(item.perfil)}</td>
                    <td class="num">${UI.formatarData(item.createdAt)}</td>
                    <td>
                        <div class="row-actions">
                            <button type="button" class="btn btn-sm btn-icon" data-acao="editar" data-id="${item.idOrganizador}"
                                title="${editar ? "Editar organizador" : "Sem permissão"}" ${editar ? "" : "disabled"}>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20h4l10-10-4-4L4 16z"/><path d="M13.5 6.5l4 4"/></svg>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon" data-acao="excluir" data-id="${item.idOrganizador}"
                                title="${excluir ? "Excluir organizador" : "Somente administrador exclui outro usuário"}" ${excluir ? "" : "disabled"}>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>`;
        }).join("");
    }

    async function carregar() {
        tbody.innerHTML = UI.linhasCarregando(5);
        try {
            const dados = await Sessao.api().get("organizadores", filtros());
            estado.itens = dados.organizadores || [];
            renderizar();
        } catch (erro) {
            UI.tratarErro(erro);
            tbody.innerHTML = UI.linhasVazias(5, "Falha ao carregar", "Confira se a API está rodando.");
        }
    }

    function abrirNovo() {
        estado.editando = null;
        form.reset();
        document.getElementById("idOrganizador").value = "";
        modalTitulo.textContent = "Novo organizador";
        modalDescricao.textContent = "O organizador criado poderá entrar na API com este e-mail e senha.";
        dicaSenha.textContent = "Obrigatória no cadastro.";
        campoPerfil.disabled = !ehAdministrador;
        campoPerfil.value = ehAdministrador ? "comum" : (usuario.perfil || "comum");
        document.getElementById("senha").required = true;
        btnSalvar.textContent = "Cadastrar organizador";
        UI.abrirModal("modalForm");
    }

    function abrirEdicao(item) {
        estado.editando = item;
        document.getElementById("idOrganizador").value = item.idOrganizador;
        document.getElementById("nome").value = item.nome;
        document.getElementById("email").value = item.email;
        document.getElementById("telefone").value = item.telefone || "";
        document.getElementById("senha").value = "";
        campoPerfil.value = item.perfil;
        campoPerfil.disabled = !ehAdministrador;
        document.getElementById("senha").required = false;
        dicaSenha.textContent = "Deixe em branco para manter a senha atual.";
        modalTitulo.textContent = "Editar organizador";
        modalDescricao.textContent = `Atualizando #${item.idOrganizador} · ${item.email}`;
        btnSalvar.textContent = "Salvar alterações";
        UI.abrirModal("modalForm");
    }

    async function excluir(item) {
        const confirmou = await UI.confirmar({
            titulo: "Excluir organizador?",
            texto: `"${item.nome}" perderá o acesso. Organizadores com eventos vinculados não podem ser excluídos.`,
            rotulo: "Excluir"
        });
        if (!confirmou) return;

        try {
            await Sessao.api().delete("organizadores", item.idOrganizador);
            UI.toast("Organizador excluído.", "sucesso");
            await carregar();
        } catch (erro) {
            UI.tratarErro(erro);
        }
    }

    tbody.addEventListener("click", (evento) => {
        const botao = evento.target.closest("button[data-acao]");
        if (!botao || botao.disabled) return;

        const item = estado.itens.find((registro) => Number(registro.idOrganizador) === Number(botao.dataset.id));
        if (!item) return;

        if (botao.dataset.acao === "editar") abrirEdicao(item);
        if (botao.dataset.acao === "excluir") excluir(item);
    });

    btnNovo.addEventListener("click", abrirNovo);

    form.addEventListener("submit", async (evento) => {
        evento.preventDefault();

        const id = document.getElementById("idOrganizador").value;
        const nome = document.getElementById("nome").value.trim();
        const email = document.getElementById("email").value.trim();
        const telefone = document.getElementById("telefone").value.trim();
        const senha = document.getElementById("senha").value;

        if (nome.length < 3) return UI.toast("O nome deve ter pelo menos 3 caracteres.", "erro");
        if (!email.includes("@")) return UI.toast("Informe um e-mail válido.", "erro");
        if (!id && senha.length < 6) return UI.toast("A senha deve ter pelo menos 6 caracteres.", "erro");
        if (senha && senha.length < 6) return UI.toast("A senha deve ter pelo menos 6 caracteres.", "erro");

        const dados = {
            nome,
            email,
            telefone: telefone || null,
            perfil: campoPerfil.value
        };
        if (senha) dados.senha = senha;

        btnSalvar.disabled = true;
        try {
            const api = Sessao.api();
            if (id) {
                await api.put("organizadores", id, { organizador: dados });
                UI.toast("Organizador atualizado.", "sucesso");
            } else {
                await api.post("organizadores", { organizador: dados });
                UI.toast("Organizador cadastrado.", "sucesso");
            }
            UI.fecharModal("modalForm");
            await carregar();
        } catch (erro) {
            UI.tratarErro(erro);
        } finally {
            btnSalvar.disabled = false;
        }
    });

    formFiltros.addEventListener("submit", (evento) => {
        evento.preventDefault();
        carregar();
    });

    const aplicarFiltro = UI.debounce(() => carregar(), 450);
    document.getElementById("fNome").addEventListener("input", aplicarFiltro);
    document.getElementById("fEmail").addEventListener("input", aplicarFiltro);
    document.getElementById("fPerfil").addEventListener("change", () => carregar());

    document.getElementById("btnLimpar").addEventListener("click", () => {
        formFiltros.reset();
        carregar();
    });

    carregar();
})();
