# -*- coding: utf-8 -*-
import mysql.connector
from mysql.connector import pooling

from api.config.config import Config


class MysqlDatabase:
    """
    Pool de conexões MySQL (singleton), isolado do resto da aplicação.

    O pool é criado uma única vez e reutilizado por todos os DAOs,
    que recebem esta classe por injeção de dependência.
    NÃO conhece entidade nem SQL de negócio: só entrega conexões.
    """

    __pool = None

    def __init__(self):
        """Monta o dicionário de configuração do pool a partir da Config (nada de conectar aqui)."""
        self.__config = {
            "pool_name": Config.DB_POOL_NAME,
            "pool_size": Config.DB_POOL_SIZE,
            # pool_reset_session limpa a sessão (transação/temporários) ao devolver a conexão.
            "pool_reset_session": True,
            "host": Config.DB_HOST,
            "port": Config.DB_PORT,
            "user": Config.DB_USER,
            "password": Config.DB_PASSWORD,
            "database": Config.DB_DATABASE,
            "charset": "utf8mb4",
            # use_pure=True usa o conector só em Python: não exige biblioteca C compilada.
            "use_pure": True,
        }

    def connect(self):
        """Devolve o pool, criando-o na primeira chamada (singleton preguiçoso)."""
        if MysqlDatabase.__pool is None:
            try:
                MysqlDatabase.__pool = mysql.connector.pooling.MySQLConnectionPool(
                    **self.__config
                )
            except mysql.connector.Error as erro:
                print(f"[ERRO] Falha ao conectar ao MySQL: {erro}")
                raise
        return MysqlDatabase.__pool

    def get_connection(self):
        """Tira uma conexão nova do pool; o DAO é quem devolve com close()."""
        return self.connect().get_connection()

    def testar_conexao(self):
        """Abre uma conexão e roda SELECT 1; levanta se o MySQL estiver fora (init e /health)."""
        conexao = self.get_connection()
        try:
            cursor = conexao.cursor()
            # SELECT 1 é a consulta mais barata possível: só prova que a conexão abre.
            cursor.execute("SELECT 1")
            cursor.fetchone()
            cursor.close()
        # finally garante devolver a conexão ao pool mesmo se o SELECT falhar.
        finally:
            conexao.close()
