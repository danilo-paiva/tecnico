const MeuTokenJWT = require("../http/MeuTokenJWT");
const { responder } = require("../utils/resposta");

/**
 * Middleware transversal de autenticação.
 * Valida o Bearer token e disponibiliza os claims em request.organizador.
 * Só autentica (quem é): não decide perfil nem regra de negócio — quem autoriza
 * é o AdminMiddleware e quem decide é o Service.
 */
module.exports = class JwtMiddleware {
    /** @returns 401 sem token válido; caso contrário next() com request.organizador preenchido. */
    validarToken = (request, response, next) => {
        const autorizacao = request.headers.authorization;
        // Uma instância por requisição: é ela que guarda o payload decodificado.
        const tokenJWT = new MeuTokenJWT();

        if (!tokenJWT.validarToken(autorizacao)) {
            return responder(response, 401, "Token inválido ou expirado", {
                mensagem: "Envie o cabeçalho Authorization: Bearer <token>."
            });
        }

        // Claims do token (idOrganizador, nome, email, perfil): lidos pelo Controle
        // e pelo AdminMiddleware, sem consultar o banco.
        request.organizador = tokenJWT.payload;
        return next();
    };
};
