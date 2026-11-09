const { responder } = require("../utils/resposta");

/**
 * Middleware de autorização por perfil (aula paw04x02 - middleware transversal).
 * Depende do JwtMiddleware: é ele quem preenche request.organizador, então no
 * roteador este vem sempre depois do token.
 * Não olha corpo nem banco: só o perfil que veio no token.
 */
module.exports = class AdminMiddleware {
    /** @returns 403 quando o perfil do token não é administrador. */
    validarAdministrador = (request, response, next) => {
        const organizador = request.organizador;

        // Sem request.organizador não existe token: cai no 403 também.
        if (!organizador || organizador.perfil !== "administrador") {
            return responder(response, 403, "Acesso negado", {
                mensagem: "Esta operação exige o perfil administrador."
            });
        }
        return next();
    };
};
