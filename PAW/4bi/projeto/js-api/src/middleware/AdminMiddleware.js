const { responder } = require("../utils/resposta");

/**
 * Middleware de autorização por perfil (aula paw04x02 - middleware transversal).
 */
module.exports = class AdminMiddleware {
    validarAdministrador = (request, response, next) => {
        if (request.organizador?.perfil !== "administrador") {
            return responder(response, 403, "Acesso negado", {
                mensagem: "Esta operação exige o perfil administrador."
            });
        }
        return next();
    };
};
