# -*- coding: utf-8 -*-
"""
Serialização de datas para o contrato JSON (camada de utilidades).

Quem usa: os DAOs, antes de devolver as linhas do MySQL.
Devolve: o mesmo dicionário, com datetime/date já em texto ISO
— igual à API Node.js, para o frontend ler as duas APIs do mesmo jeito.
"""
from datetime import date, datetime

FORMATO_ISO = "%Y-%m-%dT%H:%M:%S"


def _valor(valor):
    """Converte um valor: datetime vira data e hora ISO, date vira a data, o resto segue igual."""
    # datetime primeiro: datetime é subclasse de date; na ordem inversa perderia a hora.
    if isinstance(valor, datetime):
        return valor.strftime(FORMATO_ISO)
    if isinstance(valor, date):
        return valor.strftime("%Y-%m-%d")
    return valor


def serializar(linha: dict | None) -> dict | None:
    """Recebe uma linha do banco (ou None) e devolve a mesma linha com as datas em texto."""
    if linha is None:
        return None
    return {chave: _valor(valor) for chave, valor in linha.items()}


def serializar_lista(linhas: list[dict]) -> list[dict]:
    """Recebe a lista de linhas do fetchall() e devolve a lista já serializada."""
    return [serializar(linha) for linha in linhas]
