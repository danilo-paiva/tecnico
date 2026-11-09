/**
 * CRUD de lotes com filtros por nome, evento e faixa de preço.
 */
(function iniciarLotes() {
    if (!window.layoutPronto) return;

    const estado = { itens: [], eventos: [], editando: null };

    const tbody = document.getElementById("tbody");
    const contador = document.getElementById("contador");
    const formFiltros = document.getElementById("formFiltros");
    const form = document.getElementById("formLote");
    const selectEvento = document.getElementById("evento");
    const selectFiltroEvento = document.getElementById("fEvento");
    const modalTitulo = document.getElementById("modalTitulo");
    const modalDescricao = document.getElementById("modalDescricao");
    const btnSalvar = document.getElementById("btnSalvar");
    const campoTotal = document.getElementById("quantidadeTotal");
    const campoDisponivel = document.getElementById("quantidadeDisponivel");

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
            nome: document.getElementById("fNome").value.trim(),
            idEvento: selectFiltroEvento.value,
            precoMin: document.getElementById("fPrecoMin").value,
            precoMax: document.getElementById("fPrecoMax").value
        };
    }

    function renderizar() {
        contador.textContent = `${estado.itens.length} lote(s)`;

        if (estado.itens.length === 0) {
            tbody.innerHTML = UI.linhasVazias(5, "Nenhum lote encontrado", "Ajuste os filtros ou cadastre um novo lote.");
            return;
        }

        tbody.innerHTML = estado.itens.map((item) => {
            const total = Number(item.quantidadeTotal) || 0;
            const disponivel = Number(item.quantidadeDisponivel) || 0;
            const percentual = total > 0 ? Math.round((disponivel / total) * 100) : 0;
            const classeBarra = percentual === 0 ? "empty" : percentual < 25 ? "low" : "";

            return `
                <tr>
                    <td>
                        <span class="cell-title">${UI.esc(item.nome)}</span>
                        <span class="cell-sub">#${item.idLote}</span>
                    </td>
                    <td>${UI.esc(item.eventoTitulo || "—")}</td>
                    <td class="num">
                        ${Number(item.preco) === 0
                            ? '<span class="badge ok">Grátis</span>'
                            : `<strong>${UI.formatarMoeda(item.preco)}</strong>`}
                    </td>
                    <td>
                        <div class="meter" title="${disponivel} de ${total} disponíveis">
                            <div class="meter-bar"><span class="${classeBarra}" style="width:${Math.max(percentual, 2)}%"></span></div>
                            <span class="num">${percentual}%</span>
                        </div>
                        <span class="cell-sub">${disponivel} de ${total}</span>
                    </td>
                    <td>
                        <div class="row-actions">
                            <button type="button" class="btn btn-sm btn-icon" data-acao="editar" data-id="${item.idLote}" title="Editar lote">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20h4l10-10-4-4L4 16z"/><path d="M13.5 6.5l4 4"/></svg>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon" data-acao="excluir" data-id="${item.idLote}" title="Excluir lote">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>`;
        }).join("");
    }

    async function carregarEventos() {
        try {
            const dados = await Sessao.api().get("eventos");
            estado.eventos = dados.eventos || [];

            UI.preencherSelect(selectEvento, estado.eventos, {
                valor: "idEvento",
                rotulo: "titulo",
                placeholder: "Selecione o evento"
            });

            UI.preencherSelect(selectFiltroEvento, estado.eventos, {
                valor: "idEvento",
                rotulo: "titulo",
                placeholder: "Todos"
            });

            const eventoDaUrl = parametros.get("idEvento");
            if (eventoDaUrl) {
                selectFiltroEvento.value = eventoDaUrl;
            }
        } catch (erro) {
            UI.tratarErro(erro);
        }
    }

    async function carregar() {
        tbody.innerHTML = UI.linhasCarregando(5);
        try {
            const dados = await Sessao.api().get("lotes", filtros());
            estado.itens = dados.lotes || [];
            renderizar();
        } catch (erro) {
            UI.tratarErro(erro);
            tbody.innerHTML = UI.linhasVazias(5, "Falha ao carregar", "Confira se a API está rodando.");
        }
    }

    function abrirNovo() {
        estado.editando = null;
        form.reset();
        document.getElementById("idLote").value = "";
        document.getElementById("preco").value = "0";

        const eventoDaUrl = parametros.get("idEvento");
        if (eventoDaUrl && estado.eventos.some((evento) => String(evento.idEvento) === eventoDaUrl)) {
            selectEvento.value = eventoDaUrl;
        } else if (estado.eventos.length > 0) {
            selectEvento.value = estado.eventos[0].idEvento;
        }

        document.getElementById("dicaEvento").textContent = "Cada evento pode ter vários lotes.";
        modalTitulo.textContent = "Novo lote";
        modalDescricao.textContent = "O lote pertence a um evento e não pode repetir o nome nele.";
        btnSalvar.textContent = "Cadastrar lote";
        UI.abrirModal("modalForm");
    }

    function abrirEdicao(item) {
        estado.editando = item;
        document.getElementById("idLote").value = item.idLote;
        document.getElementById("nome").value = item.nome;
        document.getElementById("preco").value = item.preco;
        campoTotal.value = item.quantidadeTotal;
        campoDisponivel.value = item.quantidadeDisponivel;
        selectEvento.value = item.idEvento;
        document.getElementById("dicaEvento").textContent = `Lote #${item.idLote} · ${item.eventoTitulo}`;
        modalTitulo.textContent = "Editar lote";
        modalDescricao.textContent = item.nome;
        btnSalvar.textContent = "Salvar alterações";
        UI.abrirModal("modalForm");
    }

    async function excluir(item) {
        const confirmou = await UI.confirmar({
            titulo: "Excluir lote?",
            texto: `O lote "${item.nome}" (${item.eventoTitulo}) será removido.`,
            rotulo: "Excluir"
        });
        if (!confirmou) return;

        try {
            await Sessao.api().delete("lotes", item.idLote);
            UI.toast("Lote excluído.", "sucesso");
            await carregar();
        } catch (erro) {
            UI.tratarErro(erro);
        }
    }

    tbody.addEventListener("click", (evento) => {
        const botao = evento.target.closest("button[data-acao]");
        if (!botao) return;

        const item = estado.itens.find((registro) => Number(registro.idLote) === Number(botao.getAttribute("data-id")));
        if (!item) return;

        if (botao.getAttribute("data-acao") === "editar") abrirEdicao(item);
        if (botao.getAttribute("data-acao") === "excluir") excluir(item);
    });

    document.getElementById("btnNovo").addEventListener("click", () => {
        if (estado.eventos.length === 0) {
            UI.toast("Cadastre um evento antes de criar lotes.", "info");
            return;
        }
        abrirNovo();
    });

    // Sugere a quantidade disponível igual à total ao digitar.
    campoTotal.addEventListener("input", () => {
        if (!campoDisponivel.value && campoTotal.value) {
            campoDisponivel.placeholder = `Padrão: ${campoTotal.value}`;
        }
    });

    form.addEventListener("submit", async (evento) => {
        evento.preventDefault();

        const id = document.getElementById("idLote").value;
        const nome = document.getElementById("nome").value.trim();
        const preco = document.getElementById("preco").value;
        const quantidadeTotal = campoTotal.value;
        const quantidadeDisponivel = campoDisponivel.value;
        const idEvento = selectEvento.value;

        if (nome.length < 2) return UI.toast("O nome do lote deve ter pelo menos 2 caracteres.", "erro");
        if (!idEvento) return UI.toast("Selecione o evento do lote.", "erro");
        if (!quantidadeTotal || Number(quantidadeTotal) < 1) return UI.toast("Informe a quantidade total.", "erro");
        if (quantidadeDisponivel !== "" && Number(quantidadeDisponivel) > Number(quantidadeTotal)) {
            return UI.toast("A quantidade disponível não pode passar da total.", "erro");
        }

        btnSalvar.disabled = true;
        try {
            const api = Sessao.api();
            const dados = {
                nome,
                preco: preco === "" ? 0 : preco,
                quantidadeTotal,
                idEvento
            };
            if (quantidadeDisponivel !== "") dados.quantidadeDisponivel = quantidadeDisponivel;

            if (id) {
                await api.put("lotes", id, { lote: dados });
                UI.toast("Lote atualizado.", "sucesso");
            } else {
                await api.post("lotes", { lote: dados });
                UI.toast("Lote cadastrado.", "sucesso");
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

    selectFiltroEvento.addEventListener("change", () => carregar());

    document.getElementById("btnLimpar").addEventListener("click", () => {
        formFiltros.reset();
        selectFiltroEvento.value = "";
        carregar();
    });

    (async function iniciar() {
        await carregarEventos();
        await carregar();

        if (parametros.get("novo") === "1" && estado.eventos.length > 0) {
            abrirNovo();
        }
    })();
})();
