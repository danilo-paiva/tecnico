const { responder } = require("../utils/resposta");

/**
 * Middleware de validação do parâmetro de rota :id.
 * Garante que o Controle receba um inteiro positivo; se o registro existe é
 * assunto do Service (que responde 404).
 */
module.exports = class ParametroMiddleware {
    /** @returns 400 quando :id não é inteiro maior que zero; caso contrário next(). */
    validarId = (request, response, next) => {
        const id = Number(request.params.id);
        // Number() aceita "12" (a URL traz texto) e recusa "abc", que vira NaN.
        if (!Number.isInteger(id) || id <= 0) {
            return responder(response, 400, "Parâmetro inválido", {
                campo: "id",
                mensagem: "O id da rota deve ser um número inteiro maior que zero."
            });
        }
        return next();
    };
};
