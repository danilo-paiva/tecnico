# -*- coding: utf-8 -*-
"""
Middleware de autorização por perfil (camada transversal do MVCS).

Quem chama: OrganizadorRoteador, sempre depois do JwtMiddleware.
Devolve: a própria função da rota (decorada) ou uma resposta 403 no envelope.
Não tem regra de negócio: só olha o perfil que o token trouxe.
"""
from functools import wraps

from flask import g

from api.utils.resposta import responder


class AdminMiddleware:
    """Middleware de autorização por perfil.

    Responsabilidade: barrar (403) quem não é administrador.
    Depende do JwtMiddleware: se o token não foi validado antes, g.organizador
    não existe e a resposta também é 403 — por isso ele vem depois no roteador.
    """

    def validate_administrador(self, funcao):
        """Recebe a função da rota e devolve a versão decorada que exige perfil administrador."""
        @wraps(funcao)
        def decorated_function(*args, **kwargs):
            # getattr com padrão {} evita AttributeError quando o JWT não rodou antes.
            organizador = getattr(g, "organizador", None) or {}
            if organizador.get("perfil") != "administrador":
                return responder(403, "Acesso negado", {
                    "mensagem": "Esta operação exige o perfil administrador."
                })
            return funcao(*args, **kwargs)

        return decorated_function
