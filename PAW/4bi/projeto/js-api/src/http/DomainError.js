/**
 * Erro de regra de domínio.
 * Lançado pelos setters dos Models quando o dado é inválido.
 * Não conhece HTTP: quem traduz para resposta é o tratador global de erros,
 * que converte qualquer DomainError em 400 "Erro de validação" (Servidor.js).
 * Regra de domínio é o que só depende da entidade (tamanho, formato, faixa).
 */
module.exports = class DomainError extends Error {
    /** @param {string} mensagem texto pronto para o usuário, ex.: "email é obrigatório." */
    constructor(mensagem) {
        super(mensagem);
        this.name = "DomainError";
    }
};
