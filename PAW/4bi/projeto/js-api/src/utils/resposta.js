/**
 * Envelope único de resposta da API.
 * Todas as respostas seguem: { success, message, data }.
 */
const responder = (response, status, mensagem, dados = {}) => {
    return response.status(status).send({
        success: status < 400,
        message: mensagem,
        data: dados
    });
};

module.exports = { responder };
