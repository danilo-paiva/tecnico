const { responder } = require("../utils/resposta");

/**
 * Middleware de validação do parâmetro de rota :id.
 */
module.exports = class ParametroMiddleware {
    validarId = (request, response, next) => {
        const id = Number(request.params.id);
        if (!Number.isInteger(id) || id <= 0) {
            return responder(response, 400, "Parâmetro inválido", {
                campo: "id",
                mensagem: "O id da rota deve ser um número inteiro maior que zero."
            });
        }
        return next();
    };
};
