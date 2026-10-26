const { responder } = require("../utils/resposta");

/**
 * Controller de Lote.
 */
module.exports = class LoteControle {
    #loteService;

    constructor(loteService) {
        this.#loteService = loteService;
    }

    index = async (request, response) => {
        const filtros = {};
        if (request.query.nome) filtros.nome = String(request.query.nome).trim();
        if (request.query.idEvento) filtros.idEvento = request.query.idEvento;
        if (request.query.precoMin) filtros.precoMin = request.query.precoMin;
        if (request.query.precoMax) filtros.precoMax = request.query.precoMax;

        const lotes = await this.#loteService.findAll(filtros);
        return responder(response, 200, "Busca realizada com sucesso", { lotes });
    };

    show = async (request, response) => {
        const lote = await this.#loteService.findById(request.params.id);
        return responder(response, 200, "Executado com sucesso", { lotes: [lote] });
    };

    store = async (request, response) => {
        const lote = await this.#loteService.createLote(request.body.lote);
        return responder(response, 201, "Cadastro realizado com sucesso", { lotes: [lote] });
    };

    update = async (request, response) => {
        const lote = await this.#loteService.updateLote(request.params.id, request.body.lote);
        return responder(response, 200, "Lote atualizado com sucesso", { lotes: [lote] });
    };

    destroy = async (request, response) => {
        await this.#loteService.deleteLote(request.params.id);
        return responder(response, 200, "Lote excluído com sucesso");
    };
};
