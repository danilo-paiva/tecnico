# -*- coding: utf-8 -*-
from flask import Blueprint

from api.control.auth_control import AuthControl
from api.middleware.jwt_middleware import JwtMiddleware
from api.middleware.organizador_middleware import OrganizadorMiddleware


class AuthRoteador:
    """Roteador de autenticação (login, cadastro público e perfil)."""

    def __init__(
        self,
        jwt_middleware: JwtMiddleware,
        organizador_middleware: OrganizadorMiddleware,
        auth_control: AuthControl,
    ):
        self.__jwt_middleware = jwt_middleware
        self.__organizador_middleware = organizador_middleware
        self.__auth_control = auth_control
        self.__blueprint = Blueprint("auth", __name__)

    def create_routes(self):
        @self.__blueprint.route("/login", methods=["POST"])
        @self.__organizador_middleware.validate_login_body
        def login():
            return self.__auth_control.login()

        @self.__blueprint.route("/registrar", methods=["POST"])
        @self.__organizador_middleware.validate_body
        def registrar():
            return self.__auth_control.registrar()

        @self.__blueprint.route("/perfil", methods=["GET"])
        @self.__jwt_middleware.validate_token
        def perfil():
            return self.__auth_control.perfil()

        return self.__blueprint
