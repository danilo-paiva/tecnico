/**
 * Utilidades de interface: toasts, modais, formatação e estados de tabela.
 */
const UI = {
    esc(valor) {
        return String(valor || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    },

    doisDigitos(n) {
        return n < 10 ? "0" + n : String(n);
    },

    toast(mensagem, tipo, titulo) {
        if (!tipo) tipo = "sucesso";

        let container = document.querySelector(".toasts");
        if (!container) {
            container = document.createElement("div");
            container.className = "toasts";
            document.body.appendChild(container);
        }

        const titulos = { sucesso: "Tudo certo", erro: "Não foi possível", info: "Aviso" };
        const elemento = document.createElement("div");
        elemento.className = "toast " + tipo;
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
            setTimeout(() => elemento.parentNode.removeChild(elemento), 220);
        }, 4200);
    },

    tratarErro(erro) {
        console.error(erro);

        if (erro && erro.status === 401) {
            Sessao.expirar();
            return;
        }

        if (erro && erro.status === 0) {
            this.toast(
                "Confira se a API está rodando e se o banco foi importado.",
                "erro",
                "API fora do ar"
            );
            return;
        }

        this.toast((erro && erro.message) || "Erro inesperado.", "erro");
    },

    abrirModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.className = modal.className.replace("modal", "modal open");
        modal.setAttribute("aria-hidden", "false");

        const fechar = modal.querySelectorAll("[data-fechar]");
        for (let i = 0; i < fechar.length; i++) {
            fechar[i].onclick = () => this.fecharModal(id);
        }

        const aoTeclar = (evento) => {
            if (evento.key === "Escape") {
                this.fecharModal(id);
            }
        };
        modal._aoTeclar = aoTeclar;
        document.addEventListener("keydown", aoTeclar);

        const primeiro = modal.querySelector("input:not([type=hidden]), select, textarea");
        setTimeout(() => {
            if (primeiro) primeiro.focus();
        }, 80);
    },

    fecharModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.className = modal.className.replace(" open", "");
        modal.setAttribute("aria-hidden", "true");

        if (modal._aoTeclar) {
            document.removeEventListener("keydown", modal._aoTeclar);
            modal._aoTeclar = null;
        }
    },

    /**
     * Confirmação de exclusão (usa o confirm do navegador, como no exemplo da aula).
     * Continua sendo chamada com await para não mudar as páginas.
     */
    confirmar(opcoes) {
        const titulo = (opcoes && opcoes.titulo) || "Confirmar ação";
        const texto = (opcoes && opcoes.texto) || "";
        return window.confirm(titulo + " " + texto);
    },

    formatarData(iso) {
        if (!iso) return "—";
        const data = new Date(iso);
        if (isNaN(data.getTime())) return this.esc(iso);
        return this.doisDigitos(data.getDate()) + "/" +
            this.doisDigitos(data.getMonth() + 1) + "/" +
            data.getFullYear();
    },

    formatarDataHora(iso) {
        if (!iso) return "—";
        const data = new Date(iso);
        if (isNaN(data.getTime())) return this.esc(iso);
        return this.formatarData(iso) + " · " +
            this.doisDigitos(data.getHours()) + ":" +
            this.doisDigitos(data.getMinutes());
    },

    chipData(iso) {
        const data = new Date(iso);
        const meses = ["JAN", "FEV", "MAR", "ABR", "MAI", "JUN", "JUL", "AGO", "SET", "OUT", "NOV", "DEZ"];
        if (isNaN(data.getTime())) return { dia: "--", mes: "---" };
        return {
            dia: this.doisDigitos(data.getDate()),
            mes: meses[data.getMonth()]
        };
    },

    paraInputDateTime(iso) {
        if (!iso) return "";
        const texto = String(iso);
        return texto.length >= 16 ? texto.slice(0, 16) : texto;
    },

    formatarNumero(valor) {
        const numero = Math.round(Number(valor || 0));
        const texto = String(numero);
        let comMilhar = "";
        let contador = 0;

        for (let i = texto.length - 1; i >= 0; i--) {
            comMilhar = texto[i] + comMilhar;
            contador++;
            if (contador === 3 && i > 0) {
                comMilhar = "." + comMilhar;
                contador = 0;
            }
        }

        return comMilhar;
    },

    formatarMoeda(valor) {
        const numero = Number(valor || 0);
        const partes = numero.toFixed(2).split(".");
        const inteiro = partes[0];
        const centavos = partes[1];

        let comMilhar = "";
        let contador = 0;
        for (let i = inteiro.length - 1; i >= 0; i--) {
            comMilhar = inteiro[i] + comMilhar;
            contador++;
            if (contador === 3 && i > 0) {
                comMilhar = "." + comMilhar;
                contador = 0;
            }
        }

        return "R$ " + comMilhar + "," + centavos;
    },

    iniciais(nome) {
        const partes = String(nome || "").trim().split(" ");
        let primeira = "";
        let ultima = "";

        for (let i = 0; i < partes.length; i++) {
            if (partes[i] !== "") {
                if (primeira === "") primeira = partes[i];
                ultima = partes[i];
            }
        }

        if (primeira === "") return "?";
        return (primeira.charAt(0) + (ultima !== primeira ? ultima.charAt(0) : "")).toUpperCase();
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

    linhasVazias(colunas, titulo, texto) {
        if (!titulo) titulo = "Nada por aqui";
        if (!texto) texto = "Registre o primeiro item ou ajuste os filtros.";
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

    preencherSelect(select, itens, opcoes) {
        const valor = (opcoes && opcoes.valor) || "id";
        const rotulo = (opcoes && opcoes.rotulo) || "nome";
        const placeholder = opcoes ? opcoes.placeholder : null;
        const atual = select.value;
        const lista = [];

        if (placeholder !== null && placeholder !== undefined) {
            lista.push(`<option value="">${this.esc(placeholder)}</option>`);
        }

        for (let i = 0; i < itens.length; i++) {
            lista.push(`<option value="${this.esc(itens[i][valor])}">${this.esc(itens[i][rotulo])}</option>`);
        }

        select.innerHTML = lista.join("");

        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].value === atual) {
                select.value = atual;
                break;
            }
        }
    }
};

window.UI = UI;
