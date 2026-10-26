# -*- coding: utf-8 -*-
from functools import wraps

from flask import request

from api.utils.resposta import responder

CAMPOS_OBRIGATORIOS = {
    "POST": ("nome", "quantidadeTotal", "idEvento"),
}


class LoteMiddleware:
    """Validação do corpo de Lote."""

    def validate_body(self, funcao):
        @wraps(funcao)
        def decorated_function(*args, **kwargs):
            corpo = request.get_json(silent=True) or {}
            dados = corpo.get("lote")

            if not isinstance(dados, dict):
                return responder(400, "Erro na validação de dados", {
                    "mensagem": "O campo 'lote' é obrigatório no corpo da requisição."
                })

            if request.method == "POST":
                for campo in CAMPOS_OBRIGATORIOS["POST"]:
                    if dados.get(campo) in (None, ""):
                        return responder(400, "Erro na validação de dados", {
                            "campo": campo,
                            "mensagem": f"O campo '{campo}' é obrigatório.",
                        })

            return funcao(*args, **kwargs)

        return decorated_function
