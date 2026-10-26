# -*- coding: utf-8 -*-
from api.model.evento import Evento
from api.utils.serializacao import serializar, serializar_lista
from api.utils.validacao import para_sql

SELECT = """
    SELECT e.id_evento AS idEvento,
           e.titulo,
           e.descricao,
           e.data_evento AS dataEvento,
           e.status,
           e.id_organizador AS idOrganizador,
           o.nome AS organizadorNome,
           e.created_at AS createdAt
      FROM eventos e
      INNER JOIN organizadores o ON o.id_organizador = e.id_organizador
"""


class EventoDAO:
    """DAO de Evento."""

    def __init__(self, database_dependency):
        self.__database = database_dependency

    def find_all(self, filtros: dict = None) -> list[dict]:
        filtros = filtros or {}
        condicoes = []
        parametros = []

        if filtros.get("titulo"):
            condicoes.append("e.titulo LIKE %s")
            parametros.append(f"%{filtros['titulo']}%")
        if filtros.get("status"):
            condicoes.append("e.status = %s")
            parametros.append(filtros["status"])
        if filtros.get("idOrganizador"):
            condicoes.append("e.id_organizador = %s")
            parametros.append(int(filtros["idOrganizador"]))
        if filtros.get("de"):
            condicoes.append("e.data_evento >= %s")
            parametros.append(f"{filtros['de']} 00:00:00")
        if filtros.get("ate"):
            condicoes.append("e.data_evento <= %s")
            parametros.append(f"{filtros['ate']} 23:59:59")

        where = f" WHERE {' AND '.join(condicoes)}" if condicoes else ""
        SQL = f"{SELECT}{where} ORDER BY e.data_evento ASC"

        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(SQL, tuple(parametros))
                return serializar_lista(cursor.fetchall())

    def find_by_id(self, id_evento: int) -> dict | None:
        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(f"{SELECT} WHERE e.id_evento = %s", (id_evento,))
                linhas = cursor.fetchall()
        return serializar(linhas[0]) if linhas else None

    def existe_titulo_data(self, titulo: str, data_evento, ignorar_id: int = None) -> bool:
        SQL = "SELECT COUNT(*) AS total FROM eventos WHERE titulo = %s AND data_evento = %s"
        parametros = [titulo, para_sql(data_evento)]

        if ignorar_id is not None:
            SQL += " AND id_evento <> %s"
            parametros.append(ignorar_id)

        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(SQL, tuple(parametros))
                return int(cursor.fetchone()["total"]) > 0

    def count_by_organizador(self, id_organizador: int) -> int:
        SQL = "SELECT COUNT(*) AS total FROM eventos WHERE id_organizador = %s"
        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(SQL, (id_organizador,))
                return int(cursor.fetchone()["total"])

    def create(self, evento: Evento) -> int:
        SQL = """
            INSERT INTO eventos (titulo, descricao, data_evento, status, id_organizador)
            VALUES (%s, %s, %s, %s, %s)
        """
        params = (
            evento.titulo,
            evento.descricao,
            para_sql(evento.dataEvento),
            evento.status,
            evento.idOrganizador,
        )

        with self.__database.get_connection() as conn:
            with conn.cursor() as cursor:
                cursor.execute(SQL, params)
                conn.commit()
                insert_id = cursor.lastrowid

        if not insert_id:
            raise Exception("Falha ao inserir evento.")
        return insert_id

    def update(self, evento: Evento) -> bool:
        SQL = """
            UPDATE eventos
               SET titulo = %s, descricao = %s, data_evento = %s, status = %s,
                   id_organizador = %s
             WHERE id_evento = %s
        """
        params = (
            evento.titulo,
            evento.descricao,
            para_sql(evento.dataEvento),
            evento.status,
            evento.idOrganizador,
            evento.idEvento,
        )

        with self.__database.get_connection() as conn:
            with conn.cursor() as cursor:
                cursor.execute(SQL, params)
                conn.commit()
                # rowcount = 0 quando nada muda; existência validada no Service.
                return True

    def delete(self, id_evento: int) -> bool:
        SQL = "DELETE FROM eventos WHERE id_evento = %s"
        with self.__database.get_connection() as conn:
            with conn.cursor() as cursor:
                cursor.execute(SQL, (id_evento,))
                conn.commit()
                return cursor.rowcount > 0
