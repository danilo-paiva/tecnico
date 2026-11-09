const { responder } = require("../utils/resposta");

/**
 * Controller de autenticação (login, cadastro público e perfil do token).
 * Só orquestra: lê request.body, chama o Service e monta o JSON pelo envelope.
 * Não tem regra de negócio — o 401/409 vem do Service; a única decisão aqui é
 * forçar o perfil "comum" porque esta é a rota pública de cadastro.
 */
module.exports = class AuthControle {
    #organizadorService;

    constructor(organizadorService) {
        this.#organizadorService = organizadorService;
    }

    /** POST /auth/login → 200 com o organizador e o token. @throws ErrorResponse 400 e 401 */
    login = async (request, response) => {
        const dados = request.body.organizador;
        // O frontend lê data.organizadores[0]: a chave vem no plural, como array.
        const { organizador, token } = await this.#organizadorService.login(dados.email, dados.senha);

        return responder(response, 200, "Login realizado com sucesso", {
            organizadores: [organizador],
            token
        });
    };

    /** POST /auth/registrar → 201 com a conta criada e já autenticada. @throws 409 (e-mail) */
    registrar = async (request, response) => {
        // Cadastro público cria sempre perfil "comum".
        const organizador = await this.#organizadorService.createOrganizador(
            request.body.organizador,
            { perfil: "comum" }
        );
        // Depois de criar já faz login: o frontend entra direto, sem pedir senha de novo.
        const { organizador: dados, token } = await this.#organizadorService.login(
            organizador.email,
            request.body.organizador.senha
        );

        return responder(response, 201, "Conta criada com sucesso", {
            organizadores: [dados],
            token
        });
    };

    /** GET /auth/perfil → dados do organizador do token (rota protegida por JWT). */
    perfil = async (request, response) => {
        // request.organizador foi preenchido pelo JwtMiddleware com os claims do token.
        const organizador = await this.#organizadorService.findById(request.organizador.idOrganizador);
        return responder(response, 200, "Executado com sucesso", {
            organizadores: [organizador]
        });
    };
};
