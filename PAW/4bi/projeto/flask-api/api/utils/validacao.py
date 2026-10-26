# -*- coding: utf-8 -*-
"""
Validações de domínio compartilhadas pelos Models.

Cada função levanta ValueError com uma mensagem pronta para o usuário.
O servidor traduz ValueError para HTTP 400.
"""
import re
from datetime import datetime

EMAIL_REGEX = re.compile(r"^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$")


def texto(valor, campo, minimo=1, maximo=255, obrigatorio=True):
    if valor is None or valor == "":
        if not obrigatorio:
            return None
        raise ValueError(f"{campo} é obrigatório.")

    if not isinstance(valor, str):
        raise ValueError(f"{campo} deve ser um texto.")

    limpo = valor.strip()
    if len(limpo) < minimo:
        raise ValueError(f"{campo} deve ter pelo menos {minimo} caracteres.")
    if len(limpo) > maximo:
        raise ValueError(f"{campo} deve ter no máximo {maximo} caracteres.")
    return limpo


def inteiro(valor, campo, minimo=0, maximo=10**12):
    if valor is None or valor == "":
        raise ValueError(f"{campo} é obrigatório.")

    if isinstance(valor, bool):
        raise ValueError(f"{campo} deve ser um número inteiro.")

    try:
        numero = int(valor)
    except (TypeError, ValueError):
        raise ValueError(f"{campo} deve ser um número inteiro.")

    if str(valor).strip() not in (str(numero), f"{numero}.0"):
        raise ValueError(f"{campo} deve ser um número inteiro.")

    if numero < minimo:
        raise ValueError(f"{campo} deve ser maior ou igual a {minimo}.")
    if numero > maximo:
        raise ValueError(f"{campo} deve ser menor ou igual a {maximo}.")
    return numero


def decimal(valor, campo, minimo=0, maximo=99999999):
    if valor is None or valor == "":
        raise ValueError(f"{campo} é obrigatório.")

    try:
        numero = float(valor)
    except (TypeError, ValueError):
        raise ValueError(f"{campo} deve ser um número.")

    if numero < minimo:
        raise ValueError(f"{campo} deve ser maior ou igual a {minimo}.")
    if numero > maximo:
        raise ValueError(f"{campo} deve ser menor ou igual a {maximo}.")
    return round(numero, 2)


def email(valor):
    limpo = texto(valor, "email", 5, 150).lower()
    if not EMAIL_REGEX.match(limpo):
        raise ValueError("email deve ser um endereço válido.")
    return limpo


def telefone(valor):
    if valor is None or str(valor).strip() == "":
        return None
    limpo = str(valor).strip()
    if len(limpo) < 8 or len(limpo) > 20:
        raise ValueError("telefone deve ter entre 8 e 20 caracteres.")
    return limpo


def data_hora(valor, campo):
    if not valor:
        raise ValueError(f"{campo} é obrigatório.")

    if isinstance(valor, datetime):
        return valor

    limpo = str(valor).strip().replace("Z", "")
    formatos = (
        "%Y-%m-%dT%H:%M:%S",
        "%Y-%m-%dT%H:%M",
        "%Y-%m-%d %H:%M:%S",
        "%Y-%m-%d %H:%M",
        "%Y-%m-%d",
    )
    for formato in formatos:
        try:
            return datetime.strptime(limpo, formato)
        except ValueError:
            continue

    raise ValueError(f"{campo} deve ser uma data/hora válida.")


def para_sql(data: datetime) -> str:
    """Formato aceito pelo MySQL: YYYY-MM-DD HH:mm:ss."""
    return data.strftime("%Y-%m-%d %H:%M:%S")
