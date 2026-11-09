const { responder } = require("../utils/resposta");

/**
 * Controller de Evento.
 * Só orquestra: lê a requisição, chama o EventoService e devolve o envelope.
 * Não tem regra de negócio — data no passado, título repetido e evento com
 * lotes são decididos no Service.
 */
module.exports = class EventoControle {
    #eventoService;

    constructor(eventoService) {
        this.#eventoService = eventoService;
    }

    /** GET /eventos?titulo=&status=&idOrganizador=&de=&ate= → 200 com a lista filtrada. */
    index = async (request, response) => {
        const filtros = {};
        if (request.query.titulo) filtros.titulo = String(request.query.titulo).trim();
        if (request.query.status) filtros.status = String(request.query.status).trim();
        if (request.query.idOrganizador) filtros.idOrganizador = request.query.idOrganizador;
        if (request.query.de) filtros.de = String(request.query.de).trim();
        if (request.query.ate) filtros.ate = String(request.query.ate).trim();

        const eventos = await this.#eventoService.findAll(filtros);
        return responder(response, 200, "Busca realizada com sucesso", { eventos });
    };

    /** GET /eventos/:id → 200 com um evento. @throws ErrorResponse 404 */
    show = async (request, response) => {
        const evento = await this.#eventoService.findById(request.params.id);
        return responder(response, 200, "Executado com sucesso", { eventos: [evento] });
    };

    /** POST /eventos → 201. @throws ErrorResponse 400 (organizador/data), 409 (duplicado) */
    store = async (request, response) => {
        const evento = await this.#eventoService.createEvento(request.body.evento);
        return responder(response, 201, "Cadastro realizado com sucesso", { eventos: [evento] });
    };

    /** PUT /eventos/:id → 200 com o evento atualizado. @throws 400, 404, 409 */
    update = async (request, response) => {
        const evento = await this.#eventoService.updateEvento(request.params.id, request.body.evento);
        return responder(response, 200, "Evento atualizado com sucesso", { eventos: [evento] });
    };

    /** DELETE /eventos/:id → 200. @throws ErrorResponse 404 (não existe), 409 (tem lotes) */
    destroy = async (request, response) => {
        await this.#eventoService.deleteEvento(request.params.id);
        return responder(response, 200, "Evento excluído com sucesso");
    };
};
