# -*- coding: utf-8 -*-
"""
Middleware de validação do corpo de Organizador (camada transversal do MVCS).

Quem chama: AuthRoteador (/login, /registrar) e OrganizadorRoteador (POST/PUT).
Devolve: a própria função da rota (decorada) ou uma resposta 400 no envelope.
Não tem regra de negócio: só confere formato e presença dos campos.
"""
from functools import wraps

from flask import request

from api.utils.resposta import responder

# Campos que o POST precisa trazer; a checagem de conteúdo é do Model/Service.
CAMPOS_OBRIGATORIOS = {
    "POST": ("nome", "email", "senha"),
}


class OrganizadorMiddleware:
    """Validação do corpo de Organizador.

    Responsabilidade: garantir o objeto "organizador" e os campos obrigatórios do POST.
    NÃO valida tamanho, formato de e-mail nem duplicidade — isso é do Model/Service.
    """

    def validate_body(self, funcao):
        """Recebe a função da rota e devolve a versão decorada que exige o corpo de organizador."""
        @wraps(funcao)
        def decorated_function(*args, **kwargs):
            # silent=True: corpo vazio ou que não é JSON vira {} em vez de erro do Flask.
            corpo = request.get_json(silent=True) or {}
            # O corpo do projeto é sempre { "organizador": { ... } } (contrato da API Node.js).
            dados = corpo.get("organizador")

            if not isinstance(dados, dict):
                return responder(400, "Erro na validação de dados", {
                    "mensagem": "O campo 'organizador' é obrigatório no corpo da requisição."
                })

            # Só o POST exige todos os campos: no PUT a atualização é parcial.
            if request.method == "POST":
                for campo in CAMPOS_OBRIGATORIOS["POST"]:
                    if dados.get(campo) in (None, ""):
                        return responder(400, "Erro na validação de dados", {
                            "campo": campo,
                            "mensagem": f"O campo '{campo}' é obrigatório.",
                        })

            return funcao(*args, **kwargs)

        return decorated_function

    def validate_login_body(self, funcao):
        """Como validate_body, mas para o login: exige apenas e-mail e senha."""
        @wraps(funcao)
        def decorated_function(*args, **kwargs):
            corpo = request.get_json(silent=True) or {}
            dados = corpo.get("organizador")

            if not isinstance(dados, dict):
                return responder(400, "Erro na validação de dados", {
                    "mensagem": "Envie o objeto 'organizador' com email e senha."
                })

            for campo in ("email", "senha"):
                if not dados.get(campo):
                    return responder(400, "Erro na validação de dados", {
                        "campo": campo,
                        "mensagem": f"O campo '{campo}' é obrigatório.",
                    })

            return funcao(*args, **kwargs)

        return decorated_function
