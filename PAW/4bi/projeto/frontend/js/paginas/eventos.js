/**
 * CRUD de eventos com filtros por título, status, organizador e período.
 */
(function iniciarEventos() {
    if (!window.layoutPronto) return;

    const estado = { itens: [], organizadores: [], editando: null };

    const tbody = document.getElementById("tbody");
    const contador = document.getElementById("contador");
    const formFiltros = document.getElementById("formFiltros");
    const form = document.getElementById("formEvento");
    const selectOrganizador = document.getElementById("organizador");
    const selectFiltroOrganizador = document.getElementById("fOrganizador");
    const modalTitulo = document.getElementById("modalTitulo");
    const modalDescricao = document.getElementById("modalDescricao");
    const btnSalvar = document.getElementById("btnSalvar");

    const parametros = {
        get(chave) {
            const busca = window.location.search.replace("?", "");
            const itens = busca.split("&");
            for (let i = 0; i < itens.length; i++) {
                const par = itens[i].split("=");
                if (par[0] === chave) return decodeURIComponent(par[1] || "");
            }
            return null;
        }
    };

    function filtros() {
        return {
            titulo: document.getElementById("fTitulo").value.trim(),
            status: document.getElementById("fStatus").value,
            idOrganizador: selectFiltroOrganizador.value,
            de: document.getElementById("fDe").value,
            ate: document.getElementById("fAte").value
        };
    }

    function renderizar() {
        contador.textContent = `${estado.itens.length} evento(s)`;

        if (estado.itens.length === 0) {
            tbody.innerHTML = UI.linhasVazias(5, "Nenhum evento encontrado", "Ajuste os filtros ou cadastre um novo evento.");
            return;
        }

        tbody.innerHTML = estado.itens.map((item) => `
            <tr>
                <td>
                    <span class="cell-title">${UI.esc(item.titulo)}</span>
                    <span class="cell-sub clamp">${UI.esc(item.descricao || "Sem descrição")}</span>
                </td>
                <td class="num">${UI.formatarDataHora(item.dataEvento)}</td>
                <td>${UI.esc(item.organizadorNome || "—")}</td>
                <td>${UI.badgeStatus(item.status)}</td>
                <td>
                    <div class="row-actions">
                        <a class="btn btn-sm btn-icon" href="lotes.html?idEvento=${item.idEvento}" title="Ver lotes deste evento">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v1a2 2 0 0 0 0 4v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1a2 2 0 0 0 0-4z"/></svg>
                        </a>
                        <button type="button" class="btn btn-sm btn-icon" data-acao="editar" data-id="${item.idEvento}" title="Editar evento">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20h4l10-10-4-4L4 16z"/><path d="M13.5 6.5l4 4"/></svg>
                        </button>
                        <button type="button" class="btn btn-sm btn-icon" data-acao="excluir" data-id="${item.idEvento}" title="Excluir evento">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/></svg>
                        </button>
                    </div>
                </td>
            </tr>`).join("");
    }

    async function carregarOrganizadores() {
        try {
            const dados = await Sessao.api().get("organizadores");
            estado.organizadores = dados.organizadores || [];

            UI.preencherSelect(selectOrganizador, estado.organizadores, {
                valor: "idOrganizador",
                rotulo: "nome",
                placeholder: "Selecione um organizador"
            });

            UI.preencherSelect(selectFiltroOrganizador, estado.organizadores, {
                valor: "idOrganizador",
                rotulo: "nome",
                placeholder: "Todos"
            });
        } catch (erro) {
            UI.tratarErro(erro);
        }
    }

    async function carregar() {
        tbody.innerHTML = UI.linhasCarregando(5);
        try {
            const dados = await Sessao.api().get("eventos", filtros());
            estado.itens = dados.eventos || [];
            renderizar();
        } catch (erro) {
            UI.tratarErro(erro);
            tbody.innerHTML = UI.linhasVazias(5, "Falha ao carregar", "Confira se a API está rodando.");
        }
    }

    function abrirNovo() {
        estado.editando = null;
        form.reset();
        document.getElementById("idEvento").value = "";
        document.getElementById("status").value = "planejado";

        // Impede escolher data no passado direto no formulário.
        const agora = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        document.getElementById("dataEvento").min = agora;

        if (estado.organizadores.length > 0) {
            const logado = Sessao.usuario() || {};
            const proprio = estado.organizadores.find((org) => Number(org.idOrganizador) === Number(logado.idOrganizador));
            selectOrganizador.value = proprio ? proprio.idOrganizador : estado.organizadores[0].idOrganizador;
        }

        document.getElementById("dicaOrganizador").textContent = "Somente organizadores cadastrados podem ser responsáveis.";
        modalTitulo.textContent = "Novo evento";
        modalDescricao.textContent = "Eventos precisam de data futura e organizador existente.";
        btnSalvar.textContent = "Cadastrar evento";
        UI.abrirModal("modalForm");
    }

    function abrirEdicao(item) {
        estado.editando = item;
        document.getElementById("idEvento").value = item.idEvento;
        document.getElementById("titulo").value = item.titulo;
        document.getElementById("descricao").value = item.descricao || "";
        document.getElementById("dataEvento").value = UI.paraInputDateTime(item.dataEvento);
        document.getElementById("status").value = item.status;
        selectOrganizador.value = item.idOrganizador;
        document.getElementById("dicaOrganizador").textContent = `Evento #${item.idEvento}`;
        modalTitulo.textContent = "Editar evento";
        modalDescricao.textContent = item.titulo;
        btnSalvar.textContent = "Salvar alterações";
        UI.abrirModal("modalForm");
    }

    async function excluir(item) {
        const confirmou = await UI.confirmar({
            titulo: "Excluir evento?",
            texto: `"${item.titulo}" será removido. Eventos com lotes vinculados não podem ser excluídos.`,
            rotulo: "Excluir"
        });
        if (!confirmou) return;

        try {
            await Sessao.api().delete("eventos", item.idEvento);
            UI.toast("Evento excluído.", "sucesso");
            await carregar();
        } catch (erro) {
            UI.tratarErro(erro);
        }
    }

    tbody.addEventListener("click", (evento) => {
        const botao = evento.target.closest("button[data-acao]");
        if (!botao) return;

        const item = estado.itens.find((registro) => Number(registro.idEvento) === Number(botao.getAttribute("data-id")));
        if (!item) return;

        if (botao.getAttribute("data-acao") === "editar") abrirEdicao(item);
        if (botao.getAttribute("data-acao") === "excluir") excluir(item);
    });

    document.getElementById("btnNovo").addEventListener("click", () => {
        if (estado.organizadores.length === 0) {
            UI.toast("Cadastre um organizador antes de criar eventos.", "info");
            return;
        }
        abrirNovo();
    });

    form.addEventListener("submit", async (evento) => {
        evento.preventDefault();

        const id = document.getElementById("idEvento").value;
        const titulo = document.getElementById("titulo").value.trim();
        const descricao = document.getElementById("descricao").value.trim();
        const dataEvento = document.getElementById("dataEvento").value;
        const status = document.getElementById("status").value;
        const idOrganizador = selectOrganizador.value;

        if (titulo.length < 3) return UI.toast("O título deve ter pelo menos 3 caracteres.", "erro");
        if (!dataEvento) return UI.toast("Informe a data e a hora do evento.", "erro");
        if (!idOrganizador) return UI.toast("Selecione o organizador responsável.", "erro");

        btnSalvar.disabled = true;
        try {
            const api = Sessao.api();
            const dados = { titulo, descricao: descricao || null, dataEvento, status, idOrganizador };

            if (id) {
                await api.put("eventos", id, { evento: dados });
                UI.toast("Evento atualizado.", "sucesso");
            } else {
                await api.post("eventos", { evento: dados });
                UI.toast("Evento cadastrado.", "sucesso");
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

    document.getElementById("fStatus").addEventListener("change", () => carregar());
    selectFiltroOrganizador.addEventListener("change", () => carregar());
    document.getElementById("fDe").addEventListener("change", () => carregar());
    document.getElementById("fAte").addEventListener("change", () => carregar());

    document.getElementById("btnLimpar").addEventListener("click", () => {
        formFiltros.reset();
        selectFiltroOrganizador.value = "";
        carregar();
    });

    (async function iniciar() {
        await carregarOrganizadores();
        await carregar();

        if (parametros.get("novo") === "1" && estado.organizadores.length > 0) {
            abrirNovo();
        }
    })();
})();
