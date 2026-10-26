# -*- coding: utf-8 -*-
"""Serialização de datas para o contrato JSON (igual à API Node.js)."""
from datetime import date, datetime

FORMATO_ISO = "%Y-%m-%dT%H:%M:%S"


def _valor(valor):
    if isinstance(valor, datetime):
        return valor.strftime(FORMATO_ISO)
    if isinstance(valor, date):
        return valor.strftime("%Y-%m-%d")
    return valor


def serializar(linha: dict | None) -> dict | None:
    if linha is None:
        return None
    return {chave: _valor(valor) for chave, valor in linha.items()}


def serializar_lista(linhas: list[dict]) -> list[dict]:
    return [serializar(linha) for linha in linhas]
