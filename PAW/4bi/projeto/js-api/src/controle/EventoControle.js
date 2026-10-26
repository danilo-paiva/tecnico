const { responder } = require("../utils/resposta");

/**
 * Controller de Evento.
 */
module.exports = class EventoControle {
    #eventoService;

    constructor(eventoService) {
        this.#eventoService = eventoService;
    }

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

    show = async (request, response) => {
        const evento = await this.#eventoService.findById(request.params.id);
        return responder(response, 200, "Executado com sucesso", { eventos: [evento] });
    };

    store = async (request, response) => {
        const evento = await this.#eventoService.createEvento(request.body.evento);
        return responder(response, 201, "Cadastro realizado com sucesso", { eventos: [evento] });
    };

    update = async (request, response) => {
        const evento = await this.#eventoService.updateEvento(request.params.id, request.body.evento);
        return responder(response, 200, "Evento atualizado com sucesso", { eventos: [evento] });
    };

    destroy = async (request, response) => {
        await this.#eventoService.deleteEvento(request.params.id);
        return responder(response, 200, "Evento excluído com sucesso");
    };
};
