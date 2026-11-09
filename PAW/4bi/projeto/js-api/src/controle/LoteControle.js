const { responder } = require("../utils/resposta");

/**
 * Controller de Lote.
 * Só orquestra: lê a requisição, chama o LoteService e devolve o envelope.
 * Não tem regra de negócio — evento inexistente, nome repetido e quantidade
 * disponível são decididos no Model e no Service.
 */
module.exports = class LoteControle {
    #loteService;

    constructor(loteService) {
        this.#loteService = loteService;
    }

    /** GET /lotes?nome=&idEvento=&precoMin=&precoMax= → 200 com a lista filtrada. */
    index = async (request, response) => {
        const filtros = {};
        if (request.query.nome) filtros.nome = String(request.query.nome).trim();
        if (request.query.idEvento) filtros.idEvento = request.query.idEvento;
        if (request.query.precoMin) filtros.precoMin = request.query.precoMin;
        if (request.query.precoMax) filtros.precoMax = request.query.precoMax;

        const lotes = await this.#loteService.findAll(filtros);
        return responder(response, 200, "Busca realizada com sucesso", { lotes });
    };

    /** GET /lotes/:id → 200 com um lote. @throws ErrorResponse 404 */
    show = async (request, response) => {
        const lote = await this.#loteService.findById(request.params.id);
        return responder(response, 200, "Executado com sucesso", { lotes: [lote] });
    };

    /** POST /lotes → 201. @throws ErrorResponse 400 (evento inválido), 409 (nome repetido) */
    store = async (request, response) => {
        const lote = await this.#loteService.createLote(request.body.lote);
        return responder(response, 201, "Cadastro realizado com sucesso", { lotes: [lote] });
    };

    /** PUT /lotes/:id → 200 com o lote atualizado. @throws 400, 404, 409 */
    update = async (request, response) => {
        const lote = await this.#loteService.updateLote(request.params.id, request.body.lote);
        return responder(response, 200, "Lote atualizado com sucesso", { lotes: [lote] });
    };

    /** DELETE /lotes/:id → 200. @throws ErrorResponse 404 quando não existe */
    destroy = async (request, response) => {
        await this.#loteService.deleteLote(request.params.id);
        return responder(response, 200, "Lote excluído com sucesso");
    };
};
