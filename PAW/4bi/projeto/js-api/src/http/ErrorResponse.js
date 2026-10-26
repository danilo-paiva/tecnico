/**
 * Erro de aplicação com status HTTP.
 * Usado nas camadas de serviço/controle/middleware para padronizar as respostas.
 */
module.exports = class ErrorResponse extends Error {
    constructor(status, mensagem, detalhes = null) {
        super(mensagem);
        this.name = "ErrorResponse";
        this.status = status;
        this.detalhes = detalhes;
    }
};
