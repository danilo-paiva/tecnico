const { responder } = require("../utils/resposta");

/**
 * Controller de Organizador.
 * Só orquestra entrada/saída: lê a requisição, chama o Service e monta o JSON.
 * Não tem regra de negócio — 403, 404 e 409 são decididos no Middleware e no
 * Service.
 */
module.exports = class OrganizadorControle {
    #organizadorService;

    constructor(organizadorService) {
        this.#organizadorService = organizadorService;
    }

    /** GET /organizadores?nome=&email=&perfil= → 200 com a lista filtrada. */
    index = async (request, response) => {
        // Filtros opcionais: só entram no objeto se vieram na query string.
        const filtros = {};
        if (request.query.nome) filtros.nome = String(request.query.nome).trim();
        if (request.query.email) filtros.email = String(request.query.email).trim();
        if (request.query.perfil) filtros.perfil = String(request.query.perfil).trim();

        const organizadores = await this.#organizadorService.findAll(filtros);
        return responder(response, 200, "Busca realizada com sucesso", { organizadores });
    };

    /** GET /organizadores/:id → 200 com um organizador. @throws ErrorResponse 404 */
    show = async (request, response) => {
        const organizador = await this.#organizadorService.findById(request.params.id);
        return responder(response, 200, "Executado com sucesso", { organizadores: [organizador] });
    };

    /** POST /organizadores → 201. @throws ErrorResponse 403 (comum), 409 (e-mail repetido) */
    store = async (request, response) => {
        const dados = request.body.organizador;
        // request.organizador são os claims do token: o Service usa para barrar o comum.
        const organizador = await this.#organizadorService.createOrganizadorComoAdministrador(
            dados,
            request.organizador
        );
        return responder(response, 201, "Cadastro realizado com sucesso", {
            organizadores: [organizador]
        });
    };

    /** PUT /organizadores/:id → 200 com o registro atualizado. @throws 403, 404, 409 */
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

    /** DELETE /organizadores/:id → 200. @throws 403, 400 (a si mesmo), 404, 409 (eventos) */
    destroy = async (request, response) => {
        await this.#organizadorService.deleteOrganizador(request.params.id, request.organizador);
        return responder(response, 200, "Organizador excluído com sucesso");
    };
};
