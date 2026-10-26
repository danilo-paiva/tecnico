# -*- coding: utf-8 -*-
from flask import request

from api.service.evento_service import EventoService
from api.utils.resposta import responder


class EventoControl:
    """Controller de Evento."""

    def __init__(self, evento_service_dependency: EventoService):
        self.__evento_service = evento_service_dependency

    def index(self):
        filtros = {}
        for campo in ("titulo", "status", "idOrganizador", "de", "ate"):
            valor = request.args.get(campo)
            if valor:
                filtros[campo] = valor.strip()

        eventos = self.__evento_service.find_all(filtros)
        return responder(200, "Busca realizada com sucesso", {"eventos": eventos})

    def show(self):
        id_evento = request.view_args.get("id")
        evento = self.__evento_service.find_by_id(id_evento)
        return responder(200, "Executado com sucesso", {"eventos": [evento]})

    def store(self):
        evento = self.__evento_service.create_evento(request.get_json().get("evento"))
        return responder(201, "Cadastro realizado com sucesso", {"eventos": [evento]})

    def update(self):
        id_evento = request.view_args.get("id")
        evento = self.__evento_service.update_evento(
            id_evento, request.get_json().get("evento")
        )
        return responder(200, "Evento atualizado com sucesso", {"eventos": [evento]})

    def destroy(self):
        id_evento = request.view_args.get("id")
        self.__evento_service.delete_evento(id_evento)
        return responder(200, "Evento excluído com sucesso")
