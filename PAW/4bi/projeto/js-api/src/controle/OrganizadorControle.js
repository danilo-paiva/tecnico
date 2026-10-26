const { responder } = require("../utils/resposta");

/**
 * Controller de Organizador.
 * Só orquestra entrada/saída: lê a requisição, chama o Service e monta o JSON.
 */
module.exports = class OrganizadorControle {
    #organizadorService;

    constructor(organizadorService) {
        this.#organizadorService = organizadorService;
    }

    index = async (request, response) => {
        const filtros = {};
        if (request.query.nome) filtros.nome = String(request.query.nome).trim();
        if (request.query.email) filtros.email = String(request.query.email).trim();
        if (request.query.perfil) filtros.perfil = String(request.query.perfil).trim();

        const organizadores = await this.#organizadorService.findAll(filtros);
        return responder(response, 200, "Busca realizada com sucesso", { organizadores });
    };

    show = async (request, response) => {
        const organizador = await this.#organizadorService.findById(request.params.id);
        return responder(response, 200, "Executado com sucesso", { organizadores: [organizador] });
    };

    store = async (request, response) => {
        const dados = request.body.organizador;
        const organizador = await this.#organizadorService.createOrganizadorComoAdministrador(
            dados,
            request.organizador
        );
        return responder(response, 201, "Cadastro realizado com sucesso", {
            organizadores: [organizador]
        });
    };

    update = async (request, response) => {
        const organizador = await this.#organizadorService.updateOrganizador(
            request.params.id,
            request.body.organizador,
            request.organizador
        );
        return responder(response, 200, "Organizador atualizado com sucesso", {
            organizadores: [organizador]
        });
    };

    destroy = async (request, response) => {
        await this.#organizadorService.deleteOrganizador(request.params.id, request.organizador);
        return responder(response, 200, "Organizador excluído com sucesso");
    };
};
