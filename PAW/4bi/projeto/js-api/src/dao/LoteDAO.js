const Banco = require("../database/Banco");

const SELECT = `
    SELECT l.id_lote AS idLote,
           l.nome,
           CAST(l.preco AS DOUBLE) AS preco,
           l.quantidade_total AS quantidadeTotal,
           l.quantidade_disponivel AS quantidadeDisponivel,
           l.id_evento AS idEvento,
           e.titulo AS eventoTitulo,
           DATE_FORMAT(l.created_at, '%Y-%m-%dT%H:%i:%s') AS createdAt
      FROM lotes l
      INNER JOIN eventos e ON e.id_evento = l.id_evento`;

/**
 * DAO de Lote.
 */
module.exports = class LoteDAO {
    async findAll(filtros = {}) {
        const condicoes = [];
        const parametros = [];

        if (filtros.nome) {
            condicoes.push("l.nome LIKE ?");
            parametros.push(`%${filtros.nome}%`);
        }
        if (filtros.idEvento) {
            condicoes.push("l.id_evento = ?");
            parametros.push(Number(filtros.idEvento));
        }
        if (filtros.precoMin !== undefined && filtros.precoMin !== "") {
            condicoes.push("l.preco >= ?");
            parametros.push(Number(filtros.precoMin));
        }
        if (filtros.precoMax !== undefined && filtros.precoMax !== "") {
            condicoes.push("l.preco <= ?");
            parametros.push(Number(filtros.precoMax));
        }

        const where = condicoes.length > 0 ? ` WHERE ${condicoes.join(" AND ")}` : "";
        return Banco.consultar(`${SELECT}${where} ORDER BY e.data_evento ASC, l.preco ASC`, parametros);
    }

    async findById(idLote) {
        const linhas = await Banco.consultar(`${SELECT} WHERE l.id_lote = ?`, [idLote]);
        return linhas.length > 0 ? linhas[0] : null;
    }

    async existeNomeEvento(nome, idEvento, ignorarId = null) {
        const parametros = [nome, idEvento];
        let SQL = "SELECT COUNT(*) AS total FROM lotes WHERE nome = ? AND id_evento = ?";
        if (ignorarId !== null) {
            SQL += " AND id_lote <> ?";
            parametros.push(ignorarId);
        }
        const linhas = await Banco.consultar(SQL, parametros);
        return Number(linhas[0].total) > 0;
    }

    async countByEvento(idEvento) {
        const linhas = await Banco.consultar(
            "SELECT COUNT(*) AS total FROM lotes WHERE id_evento = ?",
            [idEvento]
        );
        return Number(linhas[0].total);
    }

    async create(lote) {
        const resultado = await Banco.executar(
            `INSERT INTO lotes (nome, preco, quantidade_total, quantidade_disponivel, id_evento)
             VALUES (?, ?, ?, ?, ?)`,
            [
                lote.nome,
                lote.preco,
                lote.quantidadeTotal,
                lote.quantidadeDisponivel,
                lote.idEvento
            ]
        );

        if (!resultado.insertId) {
            throw new Error("Falha ao inserir lote.");
        }
        return resultado.insertId;
    }

    async update(lote) {
        await Banco.executar(
            `UPDATE lotes
                SET nome = ?, preco = ?, quantidade_total = ?, quantidade_disponivel = ?, id_evento = ?
              WHERE id_lote = ?`,
            [
                lote.nome,
                lote.preco,
                lote.quantidadeTotal,
                lote.quantidadeDisponivel,
                lote.idEvento,
                lote.idLote
            ]
        );
        // affectedRows = 0 quando nada muda; a existência já foi validada no Service.
        return true;
    }

    async delete(idLote) {
        const resultado = await Banco.executar(
            "DELETE FROM lotes WHERE id_lote = ?",
            [idLote]
        );
        return resultado.affectedRows > 0;
    }
};
