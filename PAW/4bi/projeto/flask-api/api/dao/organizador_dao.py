# -*- coding: utf-8 -*-
"""
DAO de Organizador (camada DAO do MVCS).

Quem usa: OrganizadorService e EventoService (que precisam conferir se existe).
Devolve: dicionários já serializados (ou None/True/False) — nunca o Model.
Todo o SQL de organizadores fica aqui: nenhuma outra camada escreve SELECT/INSERT.
"""
from api.model.organizador import Organizador
from api.utils.serializacao import serializar, serializar_lista

# Projeção padrão com apelidos: a chave do JSON sai igual à do Model (idOrganizador, createdAt).
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
        """Recebe o MysqlDatabase por injeção de dependência; não abre conexão agora."""
        self.__database = database_dependency

    def find_all(self, filtros: dict = None) -> list[dict]:
        """Recebe os filtros opcionais (nome, email, perfil) e devolve a lista de organizadores."""
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

        # WHERE montado só com os filtros recebidos; valores sempre via %s (evita SQL injection).
        where = f" WHERE {' AND '.join(condicoes)}" if condicoes else ""
        SQL = f"{SELECT}{where} ORDER BY nome"

        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                # tuple() porque o conector espera uma sequência de parâmetros, mesmo vazia.
                cursor.execute(SQL, tuple(parametros))
                return serializar_lista(cursor.fetchall())

    def find_by_id(self, id_organizador: int) -> dict | None:
        """Busca um organizador pelo id e devolve o dicionário, ou None se não existir."""
        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(f"{SELECT} WHERE id_organizador = %s", (id_organizador,))
                linhas = cursor.fetchall()
        return serializar(linhas[0]) if linhas else None

    def find_by_email(self, email: str) -> dict | None:
        """Busca pelo e-mail exato e devolve o dicionário, ou None (usado no login e na duplicidade)."""
        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(f"{SELECT} WHERE email = %s", (email,))
                linhas = cursor.fetchall()
        return serializar(linhas[0]) if linhas else None

    def buscar_credenciais(self, email: str) -> dict | None:
        """Como find_by_email, mas traz também o hash da senha — usado apenas no login."""
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
        """Diz se o e-mail já está gravado; ignorar_id permite editar sem acusar o próprio registro."""
        SQL = "SELECT COUNT(*) AS total FROM organizadores WHERE email = %s"
        parametros = [email]

        # No UPDATE o registro atual entra no "<>" para não conflitar consigo mesmo.
        if ignorar_id is not None:
            SQL += " AND id_organizador <> %s"
            parametros.append(ignorar_id)

        with self.__database.get_connection() as conn:
            with conn.cursor(dictionary=True) as cursor:
                cursor.execute(SQL, tuple(parametros))
                return int(cursor.fetchone()["total"]) > 0

    def create(self, organizador: Organizador, senha_hash: str) -> int:
        """Insere o organizador (senha já em hash) e devolve o id gerado pelo MySQL."""
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

        # lastrowid é o AUTO_INCREMENT do INSERT; vazio significa que nada foi gravado.
        if not insert_id:
            raise Exception("Falha ao inserir organizador.")
        return insert_id

    def update(self, organizador: Organizador, senha_hash: str = None) -> bool:
        """Atualiza o organizador; o hash da senha só entra no SET quando foi informado novo."""
        campos = ["nome = %s", "email = %s", "telefone = %s", "perfil = %s"]
        parametros = [
            organizador.nome,
            organizador.email,
            organizador.telefone,
            organizador.perfil,
        ]

        # Sem senha nova o campo não entra no UPDATE e a senha atual é preservada.
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
        """Apaga pelo id e devolve True se alguma linha foi removida."""
        SQL = "DELETE FROM organizadores WHERE id_organizador = %s"
        with self.__database.get_connection() as conn:
            with conn.cursor() as cursor:
                cursor.execute(SQL, (id_organizador,))
                conn.commit()
                return cursor.rowcount > 0
