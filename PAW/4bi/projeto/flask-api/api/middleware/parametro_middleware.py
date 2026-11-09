# -*- coding: utf-8 -*-
"""
Middleware de parâmetro de rota (camada transversal do MVCS).

Quem chama: os roteadores, como decorator (@parametro_middleware.validate_id).
Devolve: a própria função da rota (decorada) ou uma resposta 400 no envelope.
Não tem regra de negócio: só garante que o :id é inteiro positivo.
"""
from functools import wraps

from flask import request

from api.utils.resposta import responder


class ParametroMiddleware:
    """Validação do parâmetro de rota :id.

    Responsabilidade: transformar "abc", "0" ou id ausente em 400 antes do Control.
    NÃO verifica se o registro existe: isso é do Service (404).
    """

    def validate_id(self, funcao):
        """Recebe a função da rota e devolve a versão decorada que exige :id inteiro maior que zero."""
        @wraps(funcao)
        def decorated_function(*args, **kwargs):
            # view_args guarda o :id da URL (a rota ainda não chamou a função).
            valor = request.view_args.get("id") if request.view_args else None
            # int() aceita "3" e recusa "abc"; qualquer falha cai no 400 logo abaixo.
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
