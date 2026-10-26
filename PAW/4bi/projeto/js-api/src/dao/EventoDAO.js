const Banco = require("../database/Banco");
const { paraSql } = require("../utils/validacao");

const SELECT = `
    SELECT e.id_evento AS idEvento,
           e.titulo,
           e.descricao,
           DATE_FORMAT(e.data_evento, '%Y-%m-%dT%H:%i:%s') AS dataEvento,
           e.status,
           e.id_organizador AS idOrganizador,
           o.nome AS organizadorNome,
           DATE_FORMAT(e.created_at, '%Y-%m-%dT%H:%i:%s') AS createdAt
      FROM eventos e
      INNER JOIN organizadores o ON o.id_organizador = e.id_organizador`;

/**
 * DAO de Evento.
 */
module.exports = class EventoDAO {
    async findAll(filtros = {}) {
        const condicoes = [];
        const parametros = [];

        if (filtros.titulo) {
            condicoes.push("e.titulo LIKE ?");
            parametros.push(`%${filtros.titulo}%`);
        }
        if (filtros.status) {
            condicoes.push("e.status = ?");
            parametros.push(filtros.status);
        }
        if (filtros.idOrganizador) {
            condicoes.push("e.id_organizador = ?");
            parametros.push(Number(filtros.idOrganizador));
        }
        if (filtros.de) {
            condicoes.push("e.data_evento >= ?");
            parametros.push(`${filtros.de} 00:00:00`);
        }
        if (filtros.ate) {
            condicoes.push("e.data_evento <= ?");
            parametros.push(`${filtros.ate} 23:59:59`);
        }

        const where = condicoes.length > 0 ? ` WHERE ${condicoes.join(" AND ")}` : "";
        return Banco.consultar(`${SELECT}${where} ORDER BY e.data_evento ASC`, parametros);
    }

    async findById(idEvento) {
        const linhas = await Banco.consultar(`${SELECT} WHERE e.id_evento = ?`, [idEvento]);
        return linhas.length > 0 ? linhas[0] : null;
    }

    async existeTituloData(titulo, dataEvento, ignorarId = null) {
        const parametros = [titulo, paraSql(dataEvento)];
        let SQL = "SELECT COUNT(*) AS total FROM eventos WHERE titulo = ? AND data_evento = ?";
        if (ignorarId !== null) {
            SQL += " AND id_evento <> ?";
            parametros.push(ignorarId);
        }
        const linhas = await Banco.consultar(SQL, parametros);
        return Number(linhas[0].total) > 0;
    }

    async countByOrganizador(idOrganizador) {
        const linhas = await Banco.consultar(
            "SELECT COUNT(*) AS total FROM eventos WHERE id_organizador = ?",
            [idOrganizador]
        );
        return Number(linhas[0].total);
    }

    async create(evento) {
        const resultado = await Banco.executar(
            `INSERT INTO eventos (titulo, descricao, data_evento, status, id_organizador)
             VALUES (?, ?, ?, ?, ?)`,
            [
                evento.titulo,
                evento.descricao,
                paraSql(evento.dataEvento),
                evento.status,
                evento.idOrganizador
            ]
        );

        if (!resultado.insertId) {
            throw new Error("Falha ao inserir evento.");
        }
        return resultado.insertId;
    }

    async update(evento) {
        await Banco.executar(
            `UPDATE eventos
                SET titulo = ?, descricao = ?, data_evento = ?, status = ?, id_organizador = ?
              WHERE id_evento = ?`,
            [
                evento.titulo,
                evento.descricao,
                paraSql(evento.dataEvento),
                evento.status,
                evento.idOrganizador,
                evento.idEvento
            ]
        );
        // affectedRows = 0 quando nada muda; a existência já foi validada no Service.
        return true;
    }

    async delete(idEvento) {
        const resultado = await Banco.executar(
            "DELETE FROM eventos WHERE id_evento = ?",
            [idEvento]
        );
        return resultado.affectedRows > 0;
    }
};
