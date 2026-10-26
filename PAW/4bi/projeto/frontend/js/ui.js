/**
 * Utilidades de interface: toasts, modais, formatação e estados de tabela.
 */
const UI = {
    esc(valor) {
        return String(valor ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#39;");
    },

    toast(mensagem, tipo = "sucesso", titulo = null) {
        let container = document.querySelector(".toasts");
        if (!container) {
            container = document.createElement("div");
            container.className = "toasts";
            document.body.appendChild(container);
        }

        const titulos = { sucesso: "Tudo certo", erro: "Não foi possível", info: "Aviso" };
        const elemento = document.createElement("div");
        elemento.className = `toast ${tipo}`;
        elemento.innerHTML = `
            <span class="dot"></span>
            <div>
                <b>${this.esc(titulo || titulos[tipo] || "")}</b>
                <span>${this.esc(mensagem)}</span>
            </div>`;
        container.appendChild(elemento);

        setTimeout(() => {
            elemento.style.transition = "opacity .2s, transform .2s";
            elemento.style.opacity = "0";
            elemento.style.transform = "translateY(6px)";
            setTimeout(() => elemento.remove(), 220);
        }, 4200);
    },

    tratarErro(erro) {
        console.error(erro);

        if (erro?.status === 401) {
            Sessao.expirar();
            return;
        }

        if (erro?.status === 0) {
            this.toast(
                "Confira se a API está rodando e se o banco foi importado.",
                "erro",
                "API fora do ar"
            );
            return;
        }

        this.toast(erro?.message || "Erro inesperado.", "erro");
    },

    abrirModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.classList.add("open");
        modal.setAttribute("aria-hidden", "false");

        modal.querySelectorAll("[data-fechar]").forEach((elemento) => {
            elemento.onclick = () => this.fecharModal(id);
        });

        const aoTeclar = (evento) => {
            if (evento.key === "Escape") {
                this.fecharModal(id);
                document.removeEventListener("keydown", aoTeclar);
            }
        };
        document.addEventListener("keydown", aoTeclar);

        const primeiro = modal.querySelector("input:not([type=hidden]), select, textarea");
        setTimeout(() => primeiro?.focus(), 80);
    },

    fecharModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove("open");
        modal.setAttribute("aria-hidden", "true");
    },

    /** Modal de confirmação reutilizável. Resolve true/false. */
    confirmar({ titulo = "Confirmar ação", texto = "", rotulo = "Confirmar", perigo = true } = {}) {
        return new Promise((resolve) => {
            const wrapper = document.createElement("div");
            wrapper.className = "modal open";
            wrapper.innerHTML = `
                <div class="modal-backdrop" data-cancelar></div>
                <div class="modal-card" style="width:min(430px,calc(100vw - 32px))" role="dialog" aria-modal="true">
                    <div class="modal-head">
                        <div>
                            <h3>${this.esc(titulo)}</h3>
                            <p>${this.esc(texto)}</p>
                        </div>
                    </div>
                    <div class="modal-foot" style="padding-top:22px">
                        <button type="button" class="btn" data-cancelar>Cancelar</button>
                        <button type="button" class="btn ${perigo ? "btn-danger" : "btn-primary"}" data-confirmar>${this.esc(rotulo)}</button>
                    </div>
                </div>`;
            document.body.appendChild(wrapper);

            const encerrar = (valor) => {
                wrapper.remove();
                document.removeEventListener("keydown", aoTeclar);
                resolve(valor);
            };
            const aoTeclar = (evento) => {
                if (evento.key === "Escape") encerrar(false);
            };

            wrapper.querySelectorAll("[data-cancelar]").forEach((elemento) => {
                elemento.addEventListener("click", () => encerrar(false));
            });
            wrapper.querySelector("[data-confirmar]").addEventListener("click", () => encerrar(true));
            document.addEventListener("keydown", aoTeclar);
            setTimeout(() => wrapper.querySelector("[data-confirmar]")?.focus(), 60);
        });
    },

    formatarDataHora(iso) {
        if (!iso) return "—";
        const data = new Date(iso);
        if (Number.isNaN(data.getTime())) return this.esc(iso);
        const dia = data.toLocaleDateString("pt-BR", { day: "2-digit", month: "2-digit", year: "numeric" });
        const hora = data.toLocaleTimeString("pt-BR", { hour: "2-digit", minute: "2-digit" });
        return `${dia} · ${hora}`;
    },

    formatarData(iso) {
        if (!iso) return "—";
        const data = new Date(iso);
        if (Number.isNaN(data.getTime())) return this.esc(iso);
        return data.toLocaleDateString("pt-BR", { day: "2-digit", month: "2-digit", year: "numeric" });
    },

    chipData(iso) {
        const data = new Date(iso);
        if (Number.isNaN(data.getTime())) return { dia: "--", mes: "---" };
        return {
            dia: String(data.getDate()).padStart(2, "0"),
            mes: data.toLocaleDateString("pt-BR", { month: "short" }).replace(".", "").toUpperCase()
        };
    },

    paraInputDateTime(iso) {
        if (!iso) return "";
        const texto = String(iso);
        return texto.length >= 16 ? texto.slice(0, 16) : texto;
    },

    formatarMoeda(valor) {
        return Number(valor || 0).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
    },

    iniciais(nome) {
        const partes = String(nome || "").trim().split(/\s+/);
        if (partes.length === 0 || !partes[0]) return "?";
        return (partes[0][0] + (partes.length > 1 ? partes[partes.length - 1][0] : "")).toUpperCase();
    },

    debounce(funcao, espera = 350) {
        let timer = null;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => funcao(...args), espera);
        };
    },

    badgeStatus(status) {
        const mapa = {
            planejado: "neutral",
            confirmado: "ok",
            realizado: "info",
            cancelado: "danger",
            administrador: "accent",
            comum: "neutral"
        };
        const classe = mapa[status] || "neutral";
        return `<span class="badge ${classe}">${this.esc(status || "—")}</span>`;
    },

    linhasVazias(colunas, titulo = "Nada por aqui", texto = "Registre o primeiro item ou ajuste os filtros.") {
        return `
            <tr>
                <td colspan="${colunas}">
                    <div class="empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                            <path d="M3 10h18M8 4v6"></path>
                        </svg>
                        <b>${this.esc(titulo)}</b>
                        ${this.esc(texto)}
                    </div>
                </td>
            </tr>`;
    },

    linhasCarregando(colunas) {
        return `
            <tr>
                <td colspan="${colunas}" style="padding:22px 20px">
                    <div class="skeleton" style="height:14px;width:46%;margin-bottom:10px"></div>
                    <div class="skeleton" style="height:14px;width:72%"></div>
                </td>
            </tr>`;
    },

    preencherSelect(select, itens, { valor = "id", rotulo = "nome", placeholder = null } = {}) {
        const atual = select.value;
        const opcoes = [];

        if (placeholder !== null) {
            opcoes.push(`<option value="">${this.esc(placeholder)}</option>`);
        }

        for (const item of itens) {
            opcoes.push(`<option value="${this.esc(item[valor])}">${this.esc(item[rotulo])}</option>`);
        }

        select.innerHTML = opcoes.join("");
        if (atual && [...select.options].some((opcao) => opcao.value === atual)) {
            select.value = atual;
        }
    }
};

window.UI = UI;
