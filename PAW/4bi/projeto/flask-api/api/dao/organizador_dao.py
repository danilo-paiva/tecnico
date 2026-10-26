# -*- coding: utf-8 -*-
from api.model.organizador import Organizador
from api.utils.serializacao import serializar, serializar_lista

SELECT = """
    SELECT id_organizador AS idOrganizador,
           nome,
           email,
           telefone,
           perfil,
           created_at AS createdAt
      FROM organizadores
"""


class OrganizadorDAO:
    """DAO de Organizador: todo o SQL fica isolado aqui."""

    def __init__(self, database_dependency):
        self.__database = database_dependency

    def find_all(self, filtros: dict = None) -> list[dict]:
        filtros = filtros or {}
        condicoes = []
        parametros = []

        if filtros.get("nome"):
            condicoes.append("nome LIKE %s")
            parametros.append(f"%{filtros['nome']}%")
        if filtros.get("email"):
            condicoes.append("email LIKE %s")
            parametros.append(f"%{filtros['email']}%")
        if filtros.get("perfil"):
            condicoes.append("perfil = %s")
            parametros.append(filtros["perfil"])

        where = f" WHERE {' AND '.join(condicoes)}" if condicoes else ""
        SQL = f"{SELECT}{where} ORDER BY nome"

        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(SQL, tuple(parametros))
                return serializar_lista(cursor.fetchall())

    def find_by_id(self, id_organizador: int) -> dict | None:
        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(f"{SELECT} WHERE id_organizador = %s", (id_organizador,))
                linhas = cursor.fetchall()
        return serializar(linhas[0]) if linhas else None

    def find_by_email(self, email: str) -> dict | None:
        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(f"{SELECT} WHERE email = %s", (email,))
                linhas = cursor.fetchall()
        return serializar(linhas[0]) if linhas else None

    def buscar_credenciais(self, email: str) -> dict | None:
        """Retorna também o hash da senha — usado apenas no login."""
        SQL = """
            SELECT id_organizador AS idOrganizador, nome, email, telefone, perfil, senha
              FROM organizadores
             WHERE email = %s
        """
        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(SQL, (email,))
                linhas = cursor.fetchall()
        return linhas[0] if linhas else None

    def existe_email(self, email: str, ignorar_id: int = None) -> bool:
        SQL = "SELECT COUNT(*) AS total FROM organizadores WHERE email = %s"
        parametros = [email]

        if ignorar_id is not None:
            SQL += " AND id_organizador <> %s"
            parametros.append(ignorar_id)

        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(SQL, tuple(parametros))
                return int(cursor.fetchone()["total"]) > 0

    def create(self, organizador: Organizador, senha_hash: str) -> int:
        SQL = """
            INSERT INTO organizadores (nome, email, senha, telefone, perfil)
            VALUES (%s, %s, %s, %s, %s)
        """
        params = (
            organizador.nome,
            organizador.email,
            senha_hash,
            organizador.telefone,
            organizador.perfil,
        )

        with self.__database.get_connection() as conn:
            with conn.cursor() as cursor:
                cursor.execute(SQL, params)
                conn.commit()
                insert_id = cursor.lastrowid

        if not insert_id:
            raise Exception("Falha ao inserir organizador.")
        return insert_id

    def update(self, organizador: Organizador, senha_hash: str = None) -> bool:
        campos = ["nome = %s", "email = %s", "telefone = %s", "perfil = %s"]
        parametros = [
            organizador.nome,
            organizador.email,
            organizador.telefone,
            organizador.perfil,
        ]

        if senha_hash:
            campos.append("senha = %s")
            parametros.append(senha_hash)

        parametros.append(organizador.idOrganizador)

        SQL = f"UPDATE organizadores SET {', '.join(campos)} WHERE id_organizador = %s"

        with self.__database.get_connection() as conn:
            with conn.cursor() as cursor:
                cursor.execute(SQL, tuple(parametros))
                conn.commit()
                # rowcount = 0 quando os valores enviados são iguais aos gravados;
                # a existência do registro já foi validada no Service.
                return True

    def delete(self, id_organizador: int) -> bool:
        SQL = "DELETE FROM organizadores WHERE id_organizador = %s"
        with self.__database.get_connection() as conn:
            with conn.cursor() as cursor:
                cursor.execute(SQL, (id_organizador,))
                conn.commit()
                return cursor.rowcount > 0
