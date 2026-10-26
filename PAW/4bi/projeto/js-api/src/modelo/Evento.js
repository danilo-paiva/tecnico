const DomainError = require("../http/DomainError");
const { texto, inteiro, dataHora } = require("../utils/validacao");

/**
 * Model da entidade Evento (N:1 com Organizador).
 */
module.exports = class Evento {
    static STATUS = ["planejado", "confirmado", "realizado", "cancelado"];

    #idEvento = null;
    #titulo = null;
    #descricao = null;
    #dataEvento = null;
    #status = "planejado";
    #idOrganizador = null;

    get idEvento() {
        return this.#idEvento;
    }

    set idEvento(valor) {
        this.#idEvento = inteiro(valor, "idEvento", { min: 1 });
    }

    get titulo() {
        return this.#titulo;
    }

    set titulo(valor) {
        this.#titulo = texto(valor, "titulo", { min: 3, max: 150 });
    }

    get descricao() {
        return this.#descricao;
    }

    set descricao(valor) {
        this.#descricao = texto(valor, "descricao", { min: 1, max: 2000, obrigatorio: false });
    }

    get dataEvento() {
        return this.#dataEvento;
    }

    set dataEvento(valor) {
        this.#dataEvento = dataHora(valor, "dataEvento");
    }

    get status() {
        return this.#status;
    }

    set status(valor) {
        const limpo = String(valor || "").trim().toLowerCase();
        if (!Evento.STATUS.includes(limpo)) {
            throw new DomainError(`status deve ser ${Evento.STATUS.join(", ")}.`);
        }
        this.#status = limpo;
    }

    get idOrganizador() {
        return this.#idOrganizador;
    }

    set idOrganizador(valor) {
        this.#idOrganizador = inteiro(valor, "idOrganizador", { min: 1 });
    }
};
