# -*- coding: utf-8 -*-
"""
Validações de domínio compartilhadas pelos Models (camada de utilidades).

Quem usa: apenas os setters dos Models, antes de guardar o valor.
Devolve: o valor já limpo/convertido.
Cada função levanta ValueError com uma mensagem pronta para o usuário;
o tratador global do server.py traduz esse ValueError para HTTP 400.
"""
import re
from datetime import datetime

EMAIL_REGEX = re.compile(r"^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$")


def texto(valor, campo, minimo=1, maximo=255, obrigatorio=True):
    """Recebe um texto e devolve com strip() e tamanho conferido; levanta ValueError se não der."""
    if valor is None or valor == "":
        # Campo opcional (telefone, descrição): vazio vira None em vez de erro.
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
    """Recebe número ou texto numérico e devolve int na faixa; levanta ValueError se não for inteiro."""
    if valor is None or valor == "":
        raise ValueError(f"{campo} é obrigatório.")

    # bool é subclasse de int em Python: sem esta checagem True viraria 1.
    if isinstance(valor, bool):
        raise ValueError(f"{campo} deve ser um número inteiro.")

    try:
        numero = int(valor)
    except (TypeError, ValueError):
        raise ValueError(f"{campo} deve ser um número inteiro.")

    # Recusa float "quebrado" (3.7): int() truncaria e aceitaria 3 em silêncio.
    if str(valor).strip() not in (str(numero), f"{numero}.0"):
        raise ValueError(f"{campo} deve ser um número inteiro.")

    if numero < minimo:
        raise ValueError(f"{campo} deve ser maior ou igual a {minimo}.")
    if numero > maximo:
        raise ValueError(f"{campo} deve ser menor ou igual a {maximo}.")
    return numero


def decimal(valor, campo, minimo=0, maximo=99999999):
    """Recebe um decimal e devolve float com 2 casas na faixa; levanta ValueError se não for número."""
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
    # 2 casas é o suficiente para preço em reais (centavos).
    return round(numero, 2)


def email(valor):
    """Recebe um e-mail, devolve em minúsculas e válido; levanta ValueError se o formato não casar."""
    # Minúsculo padroniza a comparação: Fulano@X e fulano@x são o mesmo e-mail.
    limpo = texto(valor, "email", 5, 150).lower()
    if not EMAIL_REGEX.match(limpo):
        raise ValueError("email deve ser um endereço válido.")
    return limpo


def telefone(valor):
    """Recebe telefone opcional e devolve texto limpo (ou None); levanta ValueError se sair de 8..20."""
    # Telefone é opcional no cadastro: ausente não é erro, vira None.
    if valor is None or str(valor).strip() == "":
        return None
    limpo = str(valor).strip()
    if len(limpo) < 8 or len(limpo) > 20:
        raise ValueError("telefone deve ter entre 8 e 20 caracteres.")
    return limpo


def data_hora(valor, campo):
    """Recebe datetime ou texto de data/hora e devolve datetime; levanta ValueError se não casar."""
    if not valor:
        raise ValueError(f"{campo} é obrigatório.")

    # Veio do DAO já convertido pelo MySQL: não precisa interpretar texto.
    if isinstance(valor, datetime):
        return valor

    # O "Z" do ISO/UTC é descartado: aqui se trabalha com hora local (sem timezone).
    limpo = str(valor).strip().replace("Z", "")
    # Formatos aceitos: ISO do frontend (2025-11-30T20:00) e o do MySQL (com espaço).
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
    """Recebe um datetime e devolve texto no formato DATETIME do MySQL (YYYY-MM-DD HH:mm:ss)."""
    # Usado só nos DAOs, na hora de montar o parâmetro que vai para o banco.
    return data.strftime("%Y-%m-%d %H:%M:%S")
