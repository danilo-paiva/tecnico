/**
 * Erro de aplicação com status HTTP.
 * Usado nas camadas de serviço/controle/middleware quando o status da resposta
 * já é conhecido (400, 403, 404, 409, 500).
 * Não formata JSON e não conhece MySQL: quem monta a resposta é o tratador
 * global de erros em Servidor.js, que lê status, message e detalhes.
 */
module.exports = class ErrorResponse extends Error {
    /**
     * @param {number} status status HTTP que deve sair na resposta
     * @param {string} mensagem texto principal mostrado ao usuário
     * @param {object|null} detalhes informação extra opcional (campo, mensagem auxiliar)
     */
    constructor(status, mensagem, detalhes = null) {
        super(mensagem);
        this.name = "ErrorResponse";
        this.status = status;
        this.detalhes = detalhes;
    }
};
