const DomainError = require("../http/DomainError");
const { texto, inteiro, dataHora } = require("../utils/validacao");

/**
 * Model da entidade Evento (N:1 com Organizador).
 * Guarda os dados e valida o que é regra de domínio: status dentro da lista,
 * datas em formato válido e organizador com id > 0.
 * Título único por data e "data no futuro" são regras de negócio → EventoService.
 * Não faz SQL nem monta resposta HTTP.
 */
module.exports = class Evento {
    // Status possíveis; o banco guarda como VARCHAR e o Model garante o domínio.
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
        // Guarda um Date de verdade; quem formata para o MySQL é o DAO (paraSql).
        this.#dataEvento = dataHora(valor, "dataEvento");
    }

    get status() {
        return this.#status;
    }

    set status(valor) {
        // Aceita qualquer caixa ("Confirmado"), mas grava sempre minúsculo.
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
