const DomainError = require("../http/DomainError");
const { texto, inteiro, email, telefone } = require("../utils/validacao");

/**
 * Model da entidade Organizador (também é o usuário autenticado da API).
 * Responsabilidade: guardar os dados e garantir as regras de domínio da entidade.
 */
module.exports = class Organizador {
    static PERFIS = ["administrador", "comum"];

    #idOrganizador = null;
    #nome = null;
    #email = null;
    #senha = null;
    #telefone = null;
    #perfil = "comum";

    get idOrganizador() {
        return this.#idOrganizador;
    }

    set idOrganizador(valor) {
        this.#idOrganizador = inteiro(valor, "idOrganizador", 1);
    }

    get nome() {
        return this.#nome;
    }

    set nome(valor) {
        this.#nome = texto(valor, "nome", 3, 120);
    }

    get email() {
        return this.#email;
    }

    set email(valor) {
        this.#email = email(valor);
    }

    get senha() {
        return this.#senha;
    }

    /** Recebe a senha em texto puro; o hash é gerado na camada de serviço. */
    set senha(valor) {
        const limpa = texto(valor, "senha", 6, 72);
        if (limpa.indexOf(" ") !== -1) {
            throw new DomainError("senha não pode conter espaços.");
        }
        this.#senha = limpa;
    }

    get telefone() {
        return this.#telefone;
    }

    set telefone(valor) {
        this.#telefone = telefone(valor);
    }

    get perfil() {
        return this.#perfil;
    }

    set perfil(valor) {
        const limpo = String(valor || "").trim().toLowerCase();
        if (Organizador.PERFIS.indexOf(limpo) === -1) {
            throw new DomainError(`perfil deve ser ${Organizador.PERFIS.join(" ou ")}.`);
        }
        this.#perfil = limpo;
    }
};
