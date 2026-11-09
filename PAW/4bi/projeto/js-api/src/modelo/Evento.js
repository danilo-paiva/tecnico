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
        this.#idEvento = inteiro(valor, "idEvento", 1);
    }

    get titulo() {
        return this.#titulo;
    }

    set titulo(valor) {
        this.#titulo = texto(valor, "titulo", 3, 150);
    }

    get descricao() {
        return this.#descricao;
    }

    set descricao(valor) {
        this.#descricao = texto(valor, "descricao", 1, 2000, false);
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
        if (Evento.STATUS.indexOf(limpo) === -1) {
            throw new DomainError(`status deve ser ${Evento.STATUS.join(", ")}.`);
        }
        this.#status = limpo;
    }

    get idOrganizador() {
        return this.#idOrganizador;
    }

    set idOrganizador(valor) {
        this.#idOrganizador = inteiro(valor, "idOrganizador", 1);
    }
};
