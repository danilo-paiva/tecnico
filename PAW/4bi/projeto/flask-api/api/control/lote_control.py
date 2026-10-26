# -*- coding: utf-8 -*-
from flask import request

from api.service.lote_service import LoteService
from api.utils.resposta import responder


class LoteControl:
    """Controller de Lote."""

    def __init__(self, lote_service_dependency: LoteService):
        self.__lote_service = lote_service_dependency

    def index(self):
        filtros = {}
        for campo in ("nome", "idEvento", "precoMin", "precoMax"):
            valor = request.args.get(campo)
            if valor:
                filtros[campo] = valor.strip()

        lotes = self.__lote_service.find_all(filtros)
        return responder(200, "Busca realizada com sucesso", {"lotes": lotes})

    def show(self):
        id_lote = request.view_args.get("id")
        lote = self.__lote_service.find_by_id(id_lote)
        return responder(200, "Executado com sucesso", {"lotes": [lote]})

    def store(self):
        lote = self.__lote_service.create_lote(request.get_json().get("lote"))
        return responder(201, "Cadastro realizado com sucesso", {"lotes": [lote]})

    def update(self):
        id_lote = request.view_args.get("id")
        lote = self.__lote_service.update_lote(id_lote, request.get_json().get("lote"))
        return responder(200, "Lote atualizado com sucesso", {"lotes": [lote]})

    def destroy(self):
        id_lote = request.view_args.get("id")
        self.__lote_service.delete_lote(id_lote)
        return responder(200, "Lote excluído com sucesso")
