const DomainError = require("../http/DomainError");
const { texto, inteiro, decimal } = require("../utils/validacao");

/**
 * Model da entidade Lote (N:1 com Evento).
 */
module.exports = class Lote {
    #idLote = null;
    #nome = null;
    #preco = 0;
    #quantidadeTotal = null;
    #quantidadeDisponivel = null;
    #idEvento = null;

    get idLote() {
        return this.#idLote;
    }

    set idLote(valor) {
        this.#idLote = inteiro(valor, "idLote", { min: 1 });
    }

    get nome() {
        return this.#nome;
    }

    set nome(valor) {
        this.#nome = texto(valor, "nome", { min: 2, max: 80 });
    }

    get preco() {
        return this.#preco;
    }

    set preco(valor) {
        this.#preco = decimal(valor, "preco", { min: 0, max: 999999.99 });
    }

    get quantidadeTotal() {
        return this.#quantidadeTotal;
    }

    set quantidadeTotal(valor) {
        this.#quantidadeTotal = inteiro(valor, "quantidadeTotal", { min: 1, max: 1000000 });
    }

    get quantidadeDisponivel() {
        return this.#quantidadeDisponivel;
    }

    set quantidadeDisponivel(valor) {
        const numero = inteiro(valor, "quantidadeDisponivel", { min: 0, max: 1000000 });
        if (this.#quantidadeTotal !== null && numero > this.#quantidadeTotal) {
            throw new DomainError("quantidadeDisponivel não pode ser maior que quantidadeTotal.");
        }
        this.#quantidadeDisponivel = numero;
    }

    get idEvento() {
        return this.#idEvento;
    }

    set idEvento(valor) {
        this.#idEvento = inteiro(valor, "idEvento", { min: 1 });
    }
};
