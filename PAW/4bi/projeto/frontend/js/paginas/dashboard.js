/**
 * Dashboard: contadores, próximos eventos e disponibilidade dos lotes.
 */
(function montarDashboard() {
    if (!window.layoutPronto) return;

    const usuario = Sessao.usuario() || {};
    const primeiroNome = String(usuario.nome || "organizador").split(" ")[0];

    document.getElementById("saudacao").textContent = `Olá, ${primeiroNome}`;
    document.getElementById("rodapeApi").textContent = Sessao.apiConfig().base;

    function renderizarContadores(eventos, lotes) {
        const confirmados = eventos.filter((evento) => evento.status === "confirmado").length;
        const disponiveis = lotes.reduce((total, lote) => total + Number(lote.quantidadeDisponivel || 0), 0);
        const totalIngressos = lotes.reduce((total, lote) => total + Number(lote.quantidadeTotal || 0), 0);
        const ocupacao = totalIngressos > 0 ? Math.round(((totalIngressos - disponiveis) / totalIngressos) * 100) : 0;

        document.getElementById("statEventos").textContent = eventos.length;
        document.getElementById("statEventosFoot").innerHTML = `<b>${confirmados}</b> confirmado(s)`;

        document.getElementById("statLotes").textContent = lotes.length;
        const gratuitos = lotes.filter((lote) => Number(lote.preco) === 0).length;
        document.getElementById("statLotesFoot").innerHTML = gratuitos > 0
            ? `<b>${gratuitos}</b> lote(s) gratuito(s)`
            : "todos com preço definido";

        document.getElementById("statDisponiveis").textContent = UI.formatarNumero(disponiveis);
        document.getElementById("statDisponiveisFoot").innerHTML = `<b>${ocupacao}%</b> já ocupado`;
    }

    function renderizarProximos(eventos) {
        const container = document.getElementById("proximos");
        const ordenados = eventos.slice().sort((a, b) => new Date(a.dataEvento) - new Date(b.dataEvento)).slice(0, 6);

        if (ordenados.length === 0) {
            container.innerHTML = `
                <div class="empty">
                    <b>Nenhum evento cadastrado</b>
                    Crie o primeiro evento para começar.
                </div>`;
            return;
        }

        container.innerHTML = ordenados.map((evento) => {
            const chip = UI.chipData(evento.dataEvento);
            return `
                <div class="upcoming-item">
                    <div class="date-chip"><b>${chip.dia}</b><small>${chip.mes}</small></div>
                    <div class="info">
                        <b>${UI.esc(evento.titulo)}</b>
                        <small>${UI.formatarDataHora(evento.dataEvento)} · ${UI.esc(evento.organizadorNome || "sem organizador")}</small>
                    </div>
                    ${UI.badgeStatus(evento.status)}
                </div>`;
        }).join("");
    }

    function renderizarDisponibilidade(lotes) {
        const container = document.getElementById("disponibilidade");

        if (lotes.length === 0) {
            container.innerHTML = `<div class="empty" style="padding:10px 0"><b>Sem lotes</b>Cadastre lotes para acompanhar o saldo.</div>`;
            return;
        }

        const comPercentual = lotes.map((lote) => {
            const total = Number(lote.quantidadeTotal) || 1;
            const disponivel = Number(lote.quantidadeDisponivel) || 0;
            const copia = Object.assign({}, lote);
            copia.percentual = Math.round((disponivel / total) * 100);
            return copia;
        });

        comPercentual.sort((a, b) => a.percentual - b.percentual);
        const ordenados = comPercentual.slice(0, 4);

        container.innerHTML = ordenados.map((lote) => `
            <div>
                <div style="display:flex;justify-content:space-between;gap:10px;margin-bottom:6px">
                    <span style="font-size:12.5px;font-weight:600">${UI.esc(lote.nome)} <span style="color:var(--muted);font-weight:400">· ${UI.esc(lote.eventoTitulo)}</span></span>
                    <span class="mono" style="font-size:11px;color:var(--muted)">${lote.quantidadeDisponivel}/${lote.quantidadeTotal}</span>
                </div>
                <div class="meter">
                    <div class="meter-bar">
                        <span class="${lote.percentual === 0 ? "empty" : lote.percentual < 25 ? "low" : ""}" style="width:${Math.max(lote.percentual, 2)}%"></span>
                    </div>
                </div>
            </div>`).join("");
    }

    async function carregar() {
        document.getElementById("proximos").innerHTML = `
            <div style="padding:18px 20px">
                <div class="skeleton" style="height:13px;width:42%;margin-bottom:10px"></div>
                <div class="skeleton" style="height:13px;width:68%"></div>
            </div>`;
        document.getElementById("disponibilidade").innerHTML = `<div class="skeleton" style="height:12px"></div>`;

        try {
            const api = Sessao.api();

            const respostaEventos = await api.get("eventos");
            const respostaLotes = await api.get("lotes");
            const respostaOrganizadores = await api.get("organizadores");

            const eventos = respostaEventos.eventos || [];
            const lotes = respostaLotes.lotes || [];
            const organizadores = respostaOrganizadores.organizadores || [];

            renderizarContadores(eventos, lotes);
            renderizarProximos(eventos);
            renderizarDisponibilidade(lotes);

            const confirmados = eventos.filter((evento) => evento.status === "confirmado").length;
            document.getElementById("statEventosFoot").innerHTML =
                `<b>${confirmados}</b> confirmado(s) · <b>${organizadores.length}</b> organizador(es)`;
        } catch (erro) {
            UI.tratarErro(erro);
            document.getElementById("proximos").innerHTML = `
                <div class="empty">
                    <b>Não foi possível carregar</b>
                    Confira se a API e o MySQL estão ligados.
                </div>`;
        }
    }

    carregar();
})();
