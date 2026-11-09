const DomainError = require("../http/DomainError");
const { texto, inteiro, decimal } = require("../utils/validacao");

/**
 * Model da entidade Lote (N:1 com Evento).
 * Guarda os dados e valida o domínio: preço com 2 casas, quantidades inteiras e
 * disponível nunca maior que o total.
 * Não faz SQL, não verifica se o evento existe e não impede nome repetido —
 * essas são regras de negócio do LoteService.
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
        this.#idLote = inteiro(valor, "idLote", 1);
    }

    get nome() {
        return this.#nome;
    }

    set nome(valor) {
        this.#nome = texto(valor, "nome", 2, 80);
    }

    get preco() {
        return this.#preco;
    }

    // 2 casas decimais: mesma precisão do DECIMAL(10,2) no banco.
    set preco(valor) {
        this.#preco = decimal(valor, "preco", 0, 999999.99);
    }

    get quantidadeTotal() {
        return this.#quantidadeTotal;
    }

    set quantidadeTotal(valor) {
        this.#quantidadeTotal = inteiro(valor, "quantidadeTotal", 1, 1000000);
    }

    get quantidadeDisponivel() {
        return this.#quantidadeDisponivel;
    }

    set quantidadeDisponivel(valor) {
        // Compara com o total já definido: por isso o Service define
        // quantidadeTotal antes de quantidadeDisponivel.
        const numero = inteiro(valor, "quantidadeDisponivel", 0, 1000000);
        if (this.#quantidadeTotal !== null && numero > this.#quantidadeTotal) {
            throw new DomainError("quantidadeDisponivel não pode ser maior que quantidadeTotal.");
        }
        this.#quantidadeDisponivel = numero;
    }

    get idEvento() {
        return this.#idEvento;
    }

    set idEvento(valor) {
        this.#idEvento = inteiro(valor, "idEvento", 1);
    }
};
