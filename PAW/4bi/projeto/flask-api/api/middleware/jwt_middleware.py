# -*- coding: utf-8 -*-
from functools import wraps

from flask import g, request

from api.http.meu_token_jwt import MeuTokenJWT
from api.utils.resposta import responder


class JwtMiddleware:
    """Middleware transversal de autenticação.

    Valida o Bearer token e disponibiliza os claims em flask.g.organizador.
    """

    def validate_token(self, funcao):
        @wraps(funcao)
        def decorated_function(*args, **kwargs):
            autorizacao = request.headers.get("Authorization")
            token_jwt = MeuTokenJWT()

            if not token_jwt.validar_token(autorizacao):
                return responder(401, "Token inválido ou expirado", {
                    "mensagem": "Envie o cabeçalho Authorization: Bearer <token>."
                })

            g.organizador = token_jwt.payload
            return funcao(*args, **kwargs)

        return decorated_function
