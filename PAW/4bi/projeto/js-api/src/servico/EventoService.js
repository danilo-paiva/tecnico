const Evento = require("../modelo/Evento");
const ErrorResponse = require("../http/ErrorResponse");

/**
 * Service de Evento.
 * Regras: organizador existente, data no futuro, título único por data
 * e bloqueio de exclusão quando existem lotes vinculados.
 * Não escreve SQL (usa os DAOs) nem monta resposta HTTP (Controle).
 */
module.exports = class EventoService {
    #eventoDAO;
    #organizadorDAO;
    #loteDAO;

    constructor(eventoDAO, organizadorDAO, loteDAO) {
        this.#eventoDAO = eventoDAO;
        this.#organizadorDAO = organizadorDAO;
        this.#loteDAO = loteDAO;
    }

    /**
     * Cria um evento depois de validar as regras que dependem do banco.
     * @param {object} dados titulo, descricao, dataEvento, status, idOrganizador
     * @returns {Promise<object>} evento salvo (já com o nome do organizador)
     * @throws ErrorResponse 400 (organizador/data inválidos), 409 (duplicado)
     */
    async createEvento(dados = {}) {
        const evento = new Evento();
        evento.titulo = dados.titulo;
        evento.descricao = dados.descricao;
        evento.dataEvento = dados.dataEvento;
        // Sem status informado, o evento entra como "planejado" (mesmo padrão do banco).
        evento.status = dados.status || "planejado";
        evento.idOrganizador = dados.idOrganizador;

        await this.#validarRegras(evento);

        const idEvento = await this.#eventoDAO.create(evento);
        return this.#eventoDAO.findById(idEvento);
    }

    /** Lista com filtros de título, status, organizador e período. */
    async findAll(filtros = {}) {
        return this.#eventoDAO.findAll(filtros);
    }

    /** Busca por id. @throws ErrorResponse 404 quando não existe */
    async findById(idEvento) {
        const evento = await this.#eventoDAO.findById(idEvento);
        if (!evento) {
            throw new ErrorResponse(404, "Evento não encontrado", {
                mensagem: `Não existe evento com o id ${idEvento}.`
            });
        }
        return evento;
    }

    /**
     * Atualiza o evento; campo não enviado mantém o valor atual.
     * @throws ErrorResponse 404 (não existe), 400 e 409 (mesmas regras do create)
     */
    async updateEvento(idEvento, dados = {}) {
        const atual = await this.#eventoDAO.findById(idEvento);
        if (!atual) {
            throw new ErrorResponse(404, "Evento não encontrado", {
                mensagem: `Não existe evento com o id ${idEvento}.`
            });
        }

        const evento = new Evento();
        evento.idEvento = idEvento;
        evento.titulo = (dados.titulo !== undefined && dados.titulo !== null ? dados.titulo : atual.titulo);
        evento.descricao = dados.descricao !== undefined ? dados.descricao : atual.descricao;
        evento.dataEvento = (dados.dataEvento !== undefined && dados.dataEvento !== null ? dados.dataEvento : atual.dataEvento);
        evento.status = (dados.status !== undefined && dados.status !== null ? dados.status : atual.status);
        evento.idOrganizador = (dados.idOrganizador !== undefined && dados.idOrganizador !== null ? dados.idOrganizador : atual.idOrganizador);

        await this.#validarRegras(evento);

        const atualizou = await this.#eventoDAO.update(evento);
        if (!atualizou) {
            throw new ErrorResponse(500, "Falha ao atualizar evento");
        }

        return this.#eventoDAO.findById(idEvento);
    }

    /**
     * Exclui o evento, desde que não tenha lote nenhum.
     * @throws ErrorResponse 404 (não existe), 409 (tem lotes)
     */
    async deleteEvento(idEvento) {
        const evento = await this.#eventoDAO.findById(idEvento);
        if (!evento) {
            throw new ErrorResponse(404, "Evento não encontrado", {
                mensagem: `Não existe evento com o id ${idEvento}.`
            });
        }

        // A FK do banco já impediria (ON DELETE NO ACTION); o 409 explica o motivo.
        const totalLotes = await this.#loteDAO.countByEvento(idEvento);
        if (totalLotes > 0) {
            throw new ErrorResponse(409, "Evento possui lotes vinculados", {
                mensagem: `Exclua os ${totalLotes} lote(s) deste evento antes de removê-lo.`
            });
        }

        return this.#eventoDAO.delete(idEvento);
    }

    /**
     * Valida as regras comuns ao create e ao update. No update ela roda sobre os
     * dados já mesclados com o registro atual.
     * @throws ErrorResponse 400 (organizador inexistente ou data no passado), 409 (título repetido)
     */
    async #validarRegras(evento) {
        const organizador = await this.#organizadorDAO.findById(evento.idOrganizador);
        if (!organizador) {
            throw new ErrorResponse(400, "Organizador inválido", {
                campo: "idOrganizador",
                mensagem: `Não existe organizador com o id ${evento.idOrganizador}.`
            });
        }

        // Importante: comparar com o "agora", não com a data de criação do registro.
        if (evento.dataEvento.getTime() < Date.now()) {
            throw new ErrorResponse(400, "Data inválida", {
                campo: "dataEvento",
                mensagem: "A data do evento não pode estar no passado."
            });
        }

        // idEvento é ignorado na comparação: no update o próprio evento não conflita consigo.
        const duplicado = await this.#eventoDAO.existeTituloData(
            evento.titulo,
            evento.dataEvento,
            evento.idEvento
        );
        if (duplicado) {
            throw new ErrorResponse(409, "Evento duplicado", {
                mensagem: "Já existe um evento com este título nesta data/hora."
            });
        }
    }
};
