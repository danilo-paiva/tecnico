# -*- coding: utf-8 -*-
from api.model.lote import Lote
from api.utils.serializacao import serializar, serializar_lista

SELECT = """
    SELECT l.id_lote AS idLote,
           l.nome,
           CAST(l.preco AS DOUBLE) AS preco,
           l.quantidade_total AS quantidadeTotal,
           l.quantidade_disponivel AS quantidadeDisponivel,
           l.id_evento AS idEvento,
           e.titulo AS eventoTitulo,
           l.created_at AS createdAt
      FROM lotes l
      INNER JOIN eventos e ON e.id_evento = l.id_evento
"""


class LoteDAO:
    """DAO de Lote."""

    def __init__(self, database_dependency):
        self.__database = database_dependency

    def find_all(self, filtros: dict = None) -> list[dict]:
        filtros = filtros or {}
        condicoes = []
        parametros = []

        if filtros.get("nome"):
            condicoes.append("l.nome LIKE %s")
            parametros.append(f"%{filtros['nome']}%")
        if filtros.get("idEvento"):
            condicoes.append("l.id_evento = %s")
            parametros.append(int(filtros["idEvento"]))
        if filtros.get("precoMin") not in (None, ""):
            condicoes.append("l.preco >= %s")
            parametros.append(float(filtros["precoMin"]))
        if filtros.get("precoMax") not in (None, ""):
            condicoes.append("l.preco <= %s")
            parametros.append(float(filtros["precoMax"]))

        where = f" WHERE {' AND '.join(condicoes)}" if condicoes else ""
        SQL = f"{SELECT}{where} ORDER BY e.data_evento ASC, l.preco ASC"

        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(SQL, tuple(parametros))
                return serializar_lista(cursor.fetchall())

    def find_by_id(self, id_lote: int) -> dict | None:
        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(f"{SELECT} WHERE l.id_lote = %s", (id_lote,))
                linhas = cursor.fetchall()
        return serializar(linhas[0]) if linhas else None

    def existe_nome_evento(self, nome: str, id_evento: int, ignorar_id: int = None) -> bool:
        SQL = "SELECT COUNT(*) AS total FROM lotes WHERE nome = %s AND id_evento = %s"
        parametros = [nome, id_evento]

        if ignorar_id is not None:
            SQL += " AND id_lote <> %s"
            parametros.append(ignorar_id)

        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(SQL, tuple(parametros))
                return int(cursor.fetchone()["total"]) > 0

    def count_by_evento(self, id_evento: int) -> int:
        SQL = "SELECT COUNT(*) AS total FROM lotes WHERE id_evento = %s"
        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(SQL, (id_evento,))
                return int(cursor.fetchone()["total"])

    def create(self, lote: Lote) -> int:
        SQL = """
            INSERT INTO lotes (nome, preco, quantidade_total, quantidade_disponivel, id_evento)
            VALUES (%s, %s, %s, %s, %s)
        """
        params = (
            lote.nome,
            lote.preco,
            lote.quantidadeTotal,
            lote.quantidadeDisponivel,
            lote.idEvento,
        )

        with self.__database.get_connection() as conn:
            with conn.cursor() as cursor:
                cursor.execute(SQL, params)
                conn.commit()
                insert_id = cursor.lastrowid

        if not insert_id:
            raise Exception("Falha ao inserir lote.")
        return insert_id

    def update(self, lote: Lote) -> bool:
        SQL = """
            UPDATE lotes
               SET nome = %s, preco = %s, quantidade_total = %s,
                   quantidade_disponivel = %s, id_evento = %s
             WHERE id_lote = %s
        """
        params = (
            lote.nome,
            lote.preco,
            lote.quantidadeTotal,
            lote.quantidadeDisponivel,
            lote.idEvento,
            lote.idLote,
        )

        with self.__database.get_connection() as conn:
            with conn.cursor() as cursor:
                cursor.execute(SQL, params)
                conn.commit()
                # rowcount = 0 quando nada muda; existência validada no Service.
                return True

    def delete(self, id_lote: int) -> bool:
        SQL = "DELETE FROM lotes WHERE id_lote = %s"
        with self.__database.get_connection() as conn:
            with conn.cursor() as cursor:
                cursor.execute(SQL, (id_lote,))
                conn.commit()
                return cursor.rowcount > 0
