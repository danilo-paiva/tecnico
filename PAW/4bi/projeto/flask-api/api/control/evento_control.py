# -*- coding: utf-8 -*-
"""
Controller de Evento (camada Control do MVCS).

Quem chama: EventoRoteador, depois dos middlewares de token/id/corpo.
Devolve: a resposta no envelope (responder) — 200 ou 201.
Não tem regra de negócio: data, duplicidade e permissão são do Service.
"""
from flask import request

from api.service.evento_service import EventoService
from api.utils.resposta import responder


class EventoControl:
    """Controller de Evento: orquestra entrada/saída, sem regra de negócio.

    Responsabilidade: ler query string, id da rota e corpo, chamar o Service e responder.
    NÃO valida data, NÃO confere duplicidade e NÃO monta SQL.
    """

    def __init__(self, evento_service_dependency: EventoService):
        """Recebe o EventoService por injeção de dependência."""
        self.__evento_service = evento_service_dependency

    def index(self):
        """GET /eventos: lê os filtros da query string e devolve a lista (200)."""
        filtros = {}
        # Só os filtros conhecidos são lidos (inclui o intervalo "de"/"ate" por data).
        for campo in ("titulo", "status", "idOrganizador", "de", "ate"):
            valor = request.args.get(campo)
            if valor:
                filtros[campo] = valor.strip()

        eventos = self.__evento_service.find_all(filtros)
        return responder(200, "Busca realizada com sucesso", {"eventos": eventos})

    def show(self):
        """GET /eventos/<id>: devolve um evento (200/404)."""
        # :id da rota — o ParametroMiddleware já garantiu que é inteiro maior que zero.
        id_evento = request.view_args.get("id")
        evento = self.__evento_service.find_by_id(id_evento)
        return responder(200, "Executado com sucesso", {"eventos": [evento]})

    def store(self):
        """POST /eventos: cria o evento e devolve o registro já com o organizadorNome (201/400/409)."""
        evento = self.__evento_service.create_evento(request.get_json().get("evento"))
        return responder(201, "Cadastro realizado com sucesso", {"eventos": [evento]})

    def update(self):
        """PUT /eventos/<id>: atualiza o evento; campo ausente mantém o valor atual (200/404/400/409)."""
        id_evento = request.view_args.get("id")
        evento = self.__evento_service.update_evento(
            id_evento, request.get_json().get("evento")
        )
        return responder(200, "Evento atualizado com sucesso", {"eventos": [evento]})

    def destroy(self):
        """DELETE /eventos/<id>: exclui o evento (200/404/409 se tiver lotes)."""
        id_evento = request.view_args.get("id")
        self.__evento_service.delete_evento(id_evento)
        return responder(200, "Evento excluído com sucesso")
