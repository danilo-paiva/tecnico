# -*- coding: utf-8 -*-
"""
Roteador de autenticação (camada Roteador do MVCS — Blueprint do Flask).

Quem chama: Server.__configurar_modulos(), via register_blueprint com /api/v1/auth.
Devolve: o Blueprint com /login, /registrar e /perfil.
Não tem regra de negócio nem SQL: só liga rota -> middleware -> Control.
"""
from flask import Blueprint

from api.control.auth_control import AuthControl
from api.middleware.jwt_middleware import JwtMiddleware
from api.middleware.organizador_middleware import OrganizadorMiddleware


class AuthRoteador:
    """Roteador de autenticação (login, cadastro público e perfil).

    Ordem dos decorators (o de cima executa primeiro): middleware -> Control.
    Aqui as rotas públicas não têm JWT; só /perfil exige token.
    """

    def __init__(
        self,
        jwt_middleware: JwtMiddleware,
        organizador_middleware: OrganizadorMiddleware,
        auth_control: AuthControl,
    ):
        """Recebe os middlewares e o Control por injeção de dependência (não cria nada)."""
        self.__jwt_middleware = jwt_middleware
        self.__organizador_middleware = organizador_middleware
        self.__auth_control = auth_control
        self.__blueprint = Blueprint("auth", __name__)

    def create_routes(self):
        """Monta as três rotas no Blueprint e devolve o Blueprint para o register_blueprint."""
        # Login é público: o JWT ainda nem existe. Ordem: valida corpo -> AuthControl.login.
        @self.__blueprint.route("/login", methods=["POST"])
        @self.__organizador_middleware.validate_login_body
        def login():
            return self.__auth_control.login()

        # Cadastro público: sem token e sempre perfil comum (fixado no AuthControl).
        @self.__blueprint.route("/registrar", methods=["POST"])
        @self.__organizador_middleware.validate_body
        def registrar():
            return self.__auth_control.registrar()

        # Esta exige token: o middleware valida e coloca os claims em flask.g.
        @self.__blueprint.route("/perfil", methods=["GET"])
        @self.__jwt_middleware.validate_token
        def perfil():
            return self.__auth_control.perfil()

        return self.__blueprint
