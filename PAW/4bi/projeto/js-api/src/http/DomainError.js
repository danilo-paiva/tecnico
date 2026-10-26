/**
 * Erro de regra de domínio.
 * Lançado pelos setters dos Models quando o dado é inválido.
 * Não conhece HTTP: quem traduz para resposta é o tratador global de erros.
 */
module.exports = class DomainError extends Error {
    constructor(mensagem) {
        super(mensagem);
        this.name = "DomainError";
    }
};
