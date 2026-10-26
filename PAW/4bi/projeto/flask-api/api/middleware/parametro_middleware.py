# -*- coding: utf-8 -*-
from functools import wraps

from flask import request

from api.utils.resposta import responder


class ParametroMiddleware:
    """Validação do parâmetro de rota :id."""

    def validate_id(self, funcao):
        @wraps(funcao)
        def decorated_function(*args, **kwargs):
            valor = request.view_args.get("id") if request.view_args else None
            try:
                numero = int(valor)
            except (TypeError, ValueError):
                numero = 0

            if numero <= 0:
                return responder(400, "Parâmetro inválido", {
                    "campo": "id",
                    "mensagem": "O id da rota deve ser um número inteiro maior que zero.",
                })
            return funcao(*args, **kwargs)

        return decorated_function
