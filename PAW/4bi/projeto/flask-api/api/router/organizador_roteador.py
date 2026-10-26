# -*- coding: utf-8 -*-
from flask import Blueprint

from api.control.organizador_control import OrganizadorControl
from api.middleware.admin_middleware import AdminMiddleware
from api.middleware.jwt_middleware import JwtMiddleware
from api.middleware.organizador_middleware import OrganizadorMiddleware
from api.middleware.parametro_middleware import ParametroMiddleware


class OrganizadorRoteador:
    """Roteador de Organizador: JWT em todas as rotas; exclusão exige admin."""

    def __init__(
        self,
        jwt_middleware: JwtMiddleware,
        admin_middleware: AdminMiddleware,
        parametro_middleware: ParametroMiddleware,
        organizador_middleware: OrganizadorMiddleware,
        organizador_control: OrganizadorControl,
    ):
        self.__jwt_middleware = jwt_middleware
        self.__admin_middleware = admin_middleware
        self.__parametro_middleware = parametro_middleware
        self.__organizador_middleware = organizador_middleware
        self.__organizador_control = organizador_control
        self.__blueprint = Blueprint("organizador", __name__)

    def create_routes(self):
        @self.__blueprint.route("/", methods=["GET"], strict_slashes=False)
        @self.__jwt_middleware.validate_token
        def index():
            return self.__organizador_control.index()

        @self.__blueprint.route("/<int:id>", methods=["GET"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        def show(id):
            return self.__organizador_control.show()

        @self.__blueprint.route("/", methods=["POST"], strict_slashes=False)
        @self.__jwt_middleware.validate_token
        @self.__organizador_middleware.validate_body
        def store():
            return self.__organizador_control.store()

        @self.__blueprint.route("/<int:id>", methods=["PUT"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        @self.__organizador_middleware.validate_body
        def update(id):
            return self.__organizador_control.update()

        @self.__blueprint.route("/<int:id>", methods=["DELETE"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        @self.__admin_middleware.validate_administrador
        def destroy(id):
            return self.__organizador_control.destroy()

        return self.__blueprint
