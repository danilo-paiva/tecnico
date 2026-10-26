const MeuTokenJWT = require("../http/MeuTokenJWT");
const { responder } = require("../utils/resposta");

/**
 * Middleware transversal de autenticação.
 * Valida o Bearer token e disponibiliza os claims em request.organizador.
 */
module.exports = class JwtMiddleware {
    validarToken = (request, response, next) => {
        const autorizacao = request.headers.authorization;
        const tokenJWT = new MeuTokenJWT();

        if (!tokenJWT.validarToken(autorizacao)) {
            return responder(response, 401, "Token inválido ou expirado", {
                mensagem: "Envie o cabeçalho Authorization: Bearer <token>."
            });
        }

        request.organizador = tokenJWT.payload;
        return next();
    };
};
