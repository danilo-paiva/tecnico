# -*- coding: utf-8 -*-
"""
Configuração central da API Flask (camada de configuração).

Quem usa: MysqlDatabase (banco) e MeuTokenJWT (chave/claims do token).
Devolve: só constantes de classe — sem lógica, sem estado, sem acesso a banco.
Os valores podem ser sobrescritos por variáveis de ambiente.
"""
import os


class Config:
    """Constantes do projeto: porta, prefixo das rotas, MySQL e JWT."""

    # A porta sai de PORT para permitir rodar as duas APIs lado a lado.
    PORTA = int(os.environ.get("PORT", "8080"))

    API_NOME = "Pulse Eventos API (Flask)"
    API_VERSAO = "1.0.0"
    API_PREFIXO = "/api/v1"

    # Padrões do ambiente de aula: MySQL local com o banco paw4bi_eventos.
    DB_HOST = os.environ.get("DB_HOST", "127.0.0.1")
    DB_PORT = int(os.environ.get("DB_PORT", "3306"))
    DB_USER = os.environ.get("DB_USER", "root")
    DB_PASSWORD = os.environ.get("DB_PASSWORD", "")
    DB_DATABASE = os.environ.get("DB_DATABASE", "paw4bi_eventos")
    DB_POOL_NAME = "pool_paw4bi"
    DB_POOL_SIZE = 10

    # A mesma chave da API Node.js: o token gerado em uma API vale na outra.
    JWT_KEY = os.environ.get(
        "JWT_KEY",
        "x9S4q0v+V0IjvHkG20uAxaHx1ijj+q1HWjHKv+ohxp/oK+77qyXkVj/l4QYHHTF3",
    )
    # HS256 é chave simétrica: a mesma chave assina e valida o token.
    JWT_ALG = "HS256"
    JWT_TIPO = "JWT"
    JWT_ISS = "http://localhost"
    JWT_AUD = "http://localhost"
    JWT_SUB = "acesso_sistema"
    # 30 dias em segundos: vira o exp do token em MeuTokenJWT.gerar_token.
    JWT_DURACAO_SEGUNDOS = 60 * 60 * 24 * 30
