/**
 * Envelope único de resposta da API.
 * Todas as respostas seguem: { success, message, data } — inclusive os erros,
 * o que deixa o frontend com um único formato para ler.
 * Usado por Controles e Middlewares; é a única função que escreve no response.
 */

/**
 * @param {object} response objeto de resposta do Express
 * @param {number} status status HTTP (200, 201, 400, 401, 404, 409, 500...)
 * @param {string} mensagem texto mostrado ao usuário
 * @param {object} dados conteúdo da resposta (padrão: objeto vazio)
 * @returns {object} a própria resposta enviada
 */
const responder = (response, status, mensagem, dados = {}) => {
    // success é derivado do status: 2xx → true; qualquer erro → false.
    return response.status(status).send({
        success: status < 400,
        message: mensagem,
        data: dados
    });
};

module.exports = { responder };
