# -*- coding: utf-8 -*-
"""
Middleware de validação do corpo de Evento (camada transversal do MVCS).

Quem chama: EventoRoteador, nos métodos POST e PUT.
Devolve: a própria função da rota (decorada) ou uma resposta 400 no envelope.
Não tem regra de negócio: data no passado e duplicidade são do EventoService.
"""
from functools import wraps

from flask import request

from api.utils.resposta import responder

# Campos que o POST precisa trazer; a validação de conteúdo é do Model/Service.
CAMPOS_OBRIGATORIOS = {
    "POST": ("titulo", "dataEvento", "idOrganizador"),
}


class EventoMiddleware:
    """Validação do corpo de Evento.

    Responsabilidade: garantir o objeto "evento" e os campos obrigatórios do POST.
    NÃO valida data, status nem título repetido — isso é do Model/Service.
    """

    def validate_body(self, funcao):
        """Recebe a função da rota e devolve a versão decorada que exige o corpo de evento válido."""
        @wraps(funcao)
        def decorated_function(*args, **kwargs):
            # silent=True: corpo vazio ou que não é JSON vira {} em vez de erro do Flask.
            corpo = request.get_json(silent=True) or {}
            # O corpo do projeto é sempre { "evento": { ... } } (contrato da API Node.js).
            dados = corpo.get("evento")

            if not isinstance(dados, dict):
                return responder(400, "Erro na validação de dados", {
                    "mensagem": "O campo 'evento' é obrigatório no corpo da requisição."
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
