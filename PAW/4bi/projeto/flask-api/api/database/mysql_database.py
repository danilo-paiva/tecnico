# -*- coding: utf-8 -*-
import mysql.connector
from mysql.connector import pooling

from api.config.config import Config


class MysqlDatabase:
    """
    Pool de conexões MySQL (singleton).

    O pool é criado uma única vez e reutilizado por todos os DAOs,
    que recebem esta classe por injeção de dependência.
    """

    __pool = None

    def __init__(self):
        self.__config = {
            "pool_name": Config.DB_POOL_NAME,
            "pool_size": Config.DB_POOL_SIZE,
            "pool_reset_session": True,
            "host": Config.DB_HOST,
            "port": Config.DB_PORT,
            "user": Config.DB_USER,
            "password": Config.DB_PASSWORD,
            "database": Config.DB_DATABASE,
            "charset": "utf8mb4",
            "use_pure": True,
        }

    def connect(self):
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
        return self.connect().get_connection()

    def testar_conexao(self):
        conexao = self.get_connection()
        try:
            cursor = conexao.cursor()
            cursor.execute("SELECT 1")
            cursor.fetchone()
            cursor.close()
        finally:
            conexao.close()
