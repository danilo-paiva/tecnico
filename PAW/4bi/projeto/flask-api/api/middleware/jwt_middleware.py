# -*- coding: utf-8 -*-
"""
Middleware de autenticação JWT (camada transversal do MVCS).

Quem chama: os roteadores, como decorator (@jwt_middleware.validate_token).
Devolve: a própria função da rota (decorada) ou uma resposta 401 no envelope.
Não tem regra de negócio: só confere o token e publica o usuário em flask.g.
"""
from functools import wraps

from flask import g, request

from api.http.meu_token_jwt import MeuTokenJWT
from api.utils.resposta import responder


class JwtMiddleware:
    """Middleware transversal de autenticação.

    Valida o Bearer token e disponibiliza os claims em flask.g.organizador.
    flask.g é um "quadro negro" que vale só durante a requisição: é assim que
    o usuário logado viaja do middleware até o Control, sem ir em parâmetro.
    """

    def validate_token(self, funcao):
        """Recebe a função da rota e devolve a versão decorada que exige token válido."""
        @wraps(funcao)
        def decorated_function(*args, **kwargs):
            # Lê o header cru (Authorization: Bearer <token>); quem remove o "Bearer " é o MeuTokenJWT.
            autorizacao = request.headers.get("Authorization")
            # Uma instância por requisição: o payload validado não vaza de uma requisição para outra.
            token_jwt = MeuTokenJWT()

            if not token_jwt.validar_token(autorizacao):
                return responder(401, "Token inválido ou expirado", {
                    "mensagem": "Envie o cabeçalho Authorization: Bearer <token>."
                })

            # g.organizador é o que os Controls leem (idOrganizador, nome, email, perfil).
            g.organizador = token_jwt.payload
            return funcao(*args, **kwargs)

        return decorated_function
