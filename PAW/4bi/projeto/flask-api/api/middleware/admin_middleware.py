# -*- coding: utf-8 -*-
from functools import wraps

from flask import g

from api.utils.resposta import responder


class AdminMiddleware:
    """Middleware de autorização por perfil."""

    def validate_administrador(self, funcao):
        @wraps(funcao)
        def decorated_function(*args, **kwargs):
            organizador = getattr(g, "organizador", None) or {}
            if organizador.get("perfil") != "administrador":
                return responder(403, "Acesso negado", {
                    "mensagem": "Esta operação exige o perfil administrador."
                })
            return funcao(*args, **kwargs)

        return decorated_function
