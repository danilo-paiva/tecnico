# -*- coding: utf-8 -*-
from flask import request

from api.service.organizador_service import OrganizadorService
from api.utils.resposta import responder


class AuthControl:
    """Controller de autenticação (login, cadastro público e perfil do token)."""

    def __init__(self, organizador_service_dependency: OrganizadorService):
        self.__organizador_service = organizador_service_dependency

    def login(self):
        dados = request.get_json().get("organizador")
        organizador, token = self.__organizador_service.login(
            dados.get("email"), dados.get("senha")
        )
        return responder(200, "Login realizado com sucesso", {
            "organizadores": [organizador],
            "token": token,
        })

    def registrar(self):
        organizador = self.__organizador_service.create_organizador(
            request.get_json().get("organizador"), perfil="comum"
        )
        dados, token = self.__organizador_service.login(
            organizador["email"], request.get_json().get("organizador").get("senha")
        )
        return responder(201, "Conta criada com sucesso", {
            "organizadores": [dados],
            "token": token,
        })

    def perfil(self):
        from flask import g

        organizador = self.__organizador_service.find_by_id(g.organizador.get("idOrganizador"))
        return responder(200, "Executado com sucesso", {"organizadores": [organizador]})
