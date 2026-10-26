# -*- coding: utf-8 -*-
from flask import request

from api.service.organizador_service import OrganizadorService
from api.utils.resposta import responder


class OrganizadorControl:
    """Controller de Organizador: orquestra entrada/saída, sem regra de negócio."""

    def __init__(self, organizador_service_dependency: OrganizadorService):
        self.__organizador_service = organizador_service_dependency

    def index(self):
        filtros = {}
        for campo in ("nome", "email", "perfil"):
            valor = request.args.get(campo)
            if valor:
                filtros[campo] = valor.strip()

        organizadores = self.__organizador_service.find_all(filtros)
        return responder(200, "Busca realizada com sucesso", {"organizadores": organizadores})

    def show(self):
        id_organizador = request.view_args.get("id")
        organizador = self.__organizador_service.find_by_id(id_organizador)
        return responder(200, "Executado com sucesso", {"organizadores": [organizador]})

    def store(self):
        from flask import g

        dados = request.get_json().get("organizador")
        organizador = self.__organizador_service.create_organizador_como_administrador(
            dados, g.organizador
        )
        return responder(201, "Cadastro realizado com sucesso", {"organizadores": [organizador]})

    def update(self):
        from flask import g

        id_organizador = request.view_args.get("id")
        dados = request.get_json().get("organizador")
        organizador = self.__organizador_service.update_organizador(
            id_organizador, dados, g.organizador
        )
        return responder(200, "Organizador atualizado com sucesso", {"organizadores": [organizador]})

    def destroy(self):
        from flask import g

        id_organizador = request.view_args.get("id")
        self.__organizador_service.delete_organizador(id_organizador, g.organizador)
        return responder(200, "Organizador excluído com sucesso")
