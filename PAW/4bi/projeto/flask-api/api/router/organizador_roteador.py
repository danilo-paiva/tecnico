# -*- coding: utf-8 -*-
"""
Roteador de Organizador (camada Roteador do MVCS — Blueprint do Flask).

Quem chama: Server.__configurar_modulos(), via register_blueprint com /api/v1/organizadores.
Devolve: o Blueprint com as cinco rotas de organizador.
Não tem regra de negócio nem SQL: só liga rota -> middlewares -> Control.
"""
from flask import Blueprint

from api.control.organizador_control import OrganizadorControl
from api.middleware.admin_middleware import AdminMiddleware
from api.middleware.jwt_middleware import JwtMiddleware
from api.middleware.organizador_middleware import OrganizadorMiddleware
from api.middleware.parametro_middleware import ParametroMiddleware


class OrganizadorRoteador:
    """Roteador de Organizador: JWT em todas as rotas; exclusão exige admin.

    Ordem dos decorators (o de cima executa primeiro):
    token (quem é o usuário) -> admin (perfil) -> validação de corpo -> parâmetro -> Control.
    """

    def __init__(
        self,
        jwt_middleware: JwtMiddleware,
        admin_middleware: AdminMiddleware,
        parametro_middleware: ParametroMiddleware,
        organizador_middleware: OrganizadorMiddleware,
        organizador_control: OrganizadorControl,
    ):
        """Recebe os middlewares e o Control por injeção de dependência (não cria nada)."""
        self.__jwt_middleware = jwt_middleware
        self.__admin_middleware = admin_middleware
        self.__parametro_middleware = parametro_middleware
        self.__organizador_middleware = organizador_middleware
        self.__organizador_control = organizador_control
        self.__blueprint = Blueprint("organizador", __name__)

    def create_routes(self):
        """Monta as cinco rotas no Blueprint e devolve o Blueprint para o register_blueprint."""
        # strict_slashes=False aceita /organizadores e /organizadores/ (o frontend varia).
        @self.__blueprint.route("/", methods=["GET"], strict_slashes=False)
        @self.__jwt_middleware.validate_token
        def index():
            return self.__organizador_control.index()

        # Leitura de um registro: token -> id da rota -> Control.
        @self.__blueprint.route("/<int:id>", methods=["GET"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        def show(id):
            return self.__organizador_control.show()

        # Criação: token -> corpo válido -> Control. O perfil é o único caso em que
        # não há AdminMiddleware aqui: quem exige administrador é o próprio Service.
        @self.__blueprint.route("/", methods=["POST"], strict_slashes=False)
        @self.__jwt_middleware.validate_token
        @self.__organizador_middleware.validate_body
        def store():
            return self.__organizador_control.store()

        # Edição: token -> id -> corpo. "Admin ou o próprio usuário" é checado no Service.
        @self.__blueprint.route("/<int:id>", methods=["PUT"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        @self.__organizador_middleware.validate_body
        def update(id):
            return self.__organizador_control.update()

        # Exclusão: o admin vem depois de token e id — primeiro sabemos quem é e se o id existe.
        @self.__blueprint.route("/<int:id>", methods=["DELETE"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        @self.__admin_middleware.validate_administrador
        def destroy(id):
            return self.__organizador_control.destroy()

        return self.__blueprint
