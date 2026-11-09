const Lote = require("../modelo/Lote");
const ErrorResponse = require("../http/ErrorResponse");

/**
 * Service de Lote.
 * Regras: evento existente, nome único dentro do evento,
 * quantidade disponível nunca maior que a total.
 */
module.exports = class LoteService {
    #loteDAO;
    #eventoDAO;

    constructor(loteDAO, eventoDAO) {
        this.#loteDAO = loteDAO;
        this.#eventoDAO = eventoDAO;
    }

    async createLote(dados = {}) {
        const lote = new Lote();
        lote.nome = dados.nome;
        lote.preco = (dados.preco !== undefined && dados.preco !== null ? dados.preco : 0);
        lote.quantidadeTotal = dados.quantidadeTotal;
        lote.quantidadeDisponivel = (dados.quantidadeDisponivel !== undefined && dados.quantidadeDisponivel !== null ? dados.quantidadeDisponivel : dados.quantidadeTotal);
        lote.idEvento = dados.idEvento;

        await this.#validarRegras(lote);

        const idLote = await this.#loteDAO.create(lote);
        return this.#loteDAO.findById(idLote);
    }

    async findAll(filtros = {}) {
        return this.#loteDAO.findAll(filtros);
    }

    async findById(idLote) {
        const lote = await this.#loteDAO.findById(idLote);
        if (!lote) {
            throw new ErrorResponse(404, "Lote não encontrado", {
                mensagem: `Não existe lote com o id ${idLote}.`
            });
        }
        return lote;
    }

    async updateLote(idLote, dados = {}) {
        const atual = await this.#loteDAO.findById(idLote);
        if (!atual) {
            throw new ErrorResponse(404, "Lote não encontrado", {
                mensagem: `Não existe lote com o id ${idLote}.`
            });
        }

        const lote = new Lote();
        lote.idLote = idLote;
        lote.nome = (dados.nome !== undefined && dados.nome !== null ? dados.nome : atual.nome);
        lote.preco = (dados.preco !== undefined && dados.preco !== null ? dados.preco : atual.preco);
        lote.quantidadeTotal = (dados.quantidadeTotal !== undefined && dados.quantidadeTotal !== null ? dados.quantidadeTotal : atual.quantidadeTotal);
        lote.quantidadeDisponivel = (dados.quantidadeDisponivel !== undefined && dados.quantidadeDisponivel !== null ? dados.quantidadeDisponivel : atual.quantidadeDisponivel);
        lote.idEvento = (dados.idEvento !== undefined && dados.idEvento !== null ? dados.idEvento : atual.idEvento);

        await this.#validarRegras(lote);

        const atualizou = await this.#loteDAO.update(lote);
        if (!atualizou) {
            throw new ErrorResponse(500, "Falha ao atualizar lote");
        }

        return this.#loteDAO.findById(idLote);
    }

    async deleteLote(idLote) {
        const lote = await this.#loteDAO.findById(idLote);
        if (!lote) {
            throw new ErrorResponse(404, "Lote não encontrado", {
                mensagem: `Não existe lote com o id ${idLote}.`
            });
        }
        return this.#loteDAO.delete(idLote);
    }

    async #validarRegras(lote) {
        const evento = await this.#eventoDAO.findById(lote.idEvento);
        if (!evento) {
            throw new ErrorResponse(400, "Evento inválido", {
                campo: "idEvento",
                mensagem: `Não existe evento com o id ${lote.idEvento}.`
            });
        }

        const duplicado = await this.#loteDAO.existeNomeEvento(
            lote.nome,
            lote.idEvento,
            lote.idLote
        );
        if (duplicado) {
            throw new ErrorResponse(409, "Lote duplicado", {
                mensagem: `O evento já possui um lote chamado "${lote.nome}".`
            });
        }
    }
};
