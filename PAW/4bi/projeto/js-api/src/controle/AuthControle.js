const { responder } = require("../utils/resposta");

/**
 * Controller de autenticação (login, cadastro público e perfil do token).
 */
module.exports = class AuthControle {
    #organizadorService;

    constructor(organizadorService) {
        this.#organizadorService = organizadorService;
    }

    login = async (request, response) => {
        const dados = request.body.organizador;
        const { organizador, token } = await this.#organizadorService.login(dados.email, dados.senha);

        return responder(response, 200, "Login realizado com sucesso", {
            organizadores: [organizador],
            token
        });
    };

    registrar = async (request, response) => {
        // Cadastro público cria sempre perfil "comum".
        const organizador = await this.#organizadorService.createOrganizador(
            request.body.organizador,
            { perfil: "comum" }
        );
        const { organizador: dados, token } = await this.#organizadorService.login(
            organizador.email,
            request.body.organizador.senha
        );

        return responder(response, 201, "Conta criada com sucesso", {
            organizadores: [dados],
            token
        });
    };

    perfil = async (request, response) => {
        const organizador = await this.#organizadorService.findById(request.organizador.idOrganizador);
        return responder(response, 200, "Executado com sucesso", {
            organizadores: [organizador]
        });
    };
};
