# -*- coding: utf-8 -*-
from flask import Blueprint

from api.control.evento_control import EventoControl
from api.middleware.evento_middleware import EventoMiddleware
from api.middleware.jwt_middleware import JwtMiddleware
from api.middleware.parametro_middleware import ParametroMiddleware


class EventoRoteador:
    """Roteador de Evento (todas as rotas exigem JWT)."""

    def __init__(
        self,
        jwt_middleware: JwtMiddleware,
        parametro_middleware: ParametroMiddleware,
        evento_middleware: EventoMiddleware,
        evento_control: EventoControl,
    ):
        self.__jwt_middleware = jwt_middleware
        self.__parametro_middleware = parametro_middleware
        self.__evento_middleware = evento_middleware
        self.__evento_control = evento_control
        self.__blueprint = Blueprint("evento", __name__)

    def create_routes(self):
        @self.__blueprint.route("/", methods=["GET"], strict_slashes=False)
        @self.__jwt_middleware.validate_token
        def index():
            return self.__evento_control.index()

        @self.__blueprint.route("/<int:id>", methods=["GET"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        def show(id):
            return self.__evento_control.show()

        @self.__blueprint.route("/", methods=["POST"], strict_slashes=False)
        @self.__jwt_middleware.validate_token
        @self.__evento_middleware.validate_body
        def store():
            return self.__evento_control.store()

        @self.__blueprint.route("/<int:id>", methods=["PUT"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        @self.__evento_middleware.validate_body
        def update(id):
            return self.__evento_control.update()

        @self.__blueprint.route("/<int:id>", methods=["DELETE"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        def destroy(id):
            return self.__evento_control.destroy()

        return self.__blueprint
