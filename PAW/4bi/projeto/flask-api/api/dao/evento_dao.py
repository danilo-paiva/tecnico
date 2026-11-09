# -*- coding: utf-8 -*-
"""
DAO de Evento (camada DAO do MVCS).

Quem usa: EventoService (CRUD e regras) e OrganizadorService (contagem de vínculos).
Devolve: dicionários já serializados; nunca devolve o Model.
Todo o SQL de eventos fica aqui.
"""
from api.model.evento import Evento
from api.utils.serializacao import serializar, serializar_lista
from api.utils.validacao import para_sql

# INNER JOIN traz o nome do organizador junto: o frontend não precisa de outra chamada.
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
    """DAO de Evento: o SQL de eventos (e a contagem usada antes de excluir organizador)."""

    def __init__(self, database_dependency):
        """Recebe o MysqlDatabase por injeção de dependência."""
        self.__database = database_dependency

    def find_all(self, filtros: dict = None) -> list[dict]:
        """Recebe os filtros (titulo, status, idOrganizador, de, ate) e devolve os eventos por data."""
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
        # "de" e "ate" vêm só com a data (YYYY-MM-DD): completa o horário para pegar o dia inteiro.
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
        """Busca um evento pelo id e devolve o dicionário com o organizadorNome, ou None."""
        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(f"{SELECT} WHERE e.id_evento = %s", (id_evento,))
                linhas = cursor.fetchall()
        return serializar(linhas[0]) if linhas else None

    def existe_titulo_data(self, titulo: str, data_evento, ignorar_id: int = None) -> bool:
        """Diz se já existe evento com o mesmo título na mesma data/hora (ignorar_id serve à edição)."""
        SQL = "SELECT COUNT(*) AS total FROM eventos WHERE titulo = %s AND data_evento = %s"
        # para_sql() converte o datetime para o formato DATETIME que o MySQL compara.
        parametros = [titulo, para_sql(data_evento)]

        if ignorar_id is not None:
            SQL += " AND id_evento <> %s"
            parametros.append(ignorar_id)

        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(SQL, tuple(parametros))
                return int(cursor.fetchone()["total"]) > 0

    def count_by_organizador(self, id_organizador: int) -> int:
        """Conta os eventos do organizador — é o que impede excluir um organizador com eventos (409)."""
        SQL = "SELECT COUNT(*) AS total FROM eventos WHERE id_organizador = %s"
        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(SQL, (id_organizador,))
                return int(cursor.fetchone()["total"])

    def create(self, evento: Evento) -> int:
        """Insere o evento e devolve o id gerado pelo MySQL."""
        SQL = """
            INSERT INTO eventos (titulo, descricao, data_evento, status, id_organizador)
            VALUES (%s, %s, %s, %s, %s)
        """
        # data_evento vai como texto "YYYY-MM-DD HH:mm:ss": é o formato da coluna no MySQL.
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

        # lastrowid vazio = INSERT não gravou nada: falha inesperada (vira 500).
        if not insert_id:
            raise Exception("Falha ao inserir evento.")
        return insert_id

    def update(self, evento: Evento) -> bool:
        """Atualiza o evento inteiro pelo id e devolve True (existência já validada no Service)."""
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
        """Apaga pelo id e devolve True se alguma linha foi removida."""
        SQL = "DELETE FROM eventos WHERE id_evento = %s"
        with self.__database.get_connection() as conn:
            with conn.cursor() as cursor:
                cursor.execute(SQL, (id_evento,))
                conn.commit()
                return cursor.rowcount > 0
