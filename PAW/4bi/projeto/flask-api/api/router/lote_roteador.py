# -*- coding: utf-8 -*-
from flask import Blueprint

from api.control.lote_control import LoteControl
from api.middleware.jwt_middleware import JwtMiddleware
from api.middleware.lote_middleware import LoteMiddleware
from api.middleware.parametro_middleware import ParametroMiddleware


class LoteRoteador:
    """Roteador de Lote (todas as rotas exigem JWT)."""

    def __init__(
        self,
        jwt_middleware: JwtMiddleware,
        parametro_middleware: ParametroMiddleware,
        lote_middleware: LoteMiddleware,
        lote_control: LoteControl,
    ):
        self.__jwt_middleware = jwt_middleware
        self.__parametro_middleware = parametro_middleware
        self.__lote_middleware = lote_middleware
        self.__lote_control = lote_control
        self.__blueprint = Blueprint("lote", __name__)

    def create_routes(self):
        @self.__blueprint.route("/", methods=["GET"], strict_slashes=False)
        @self.__jwt_middleware.validate_token
        def index():
            return self.__lote_control.index()

        @self.__blueprint.route("/<int:id>", methods=["GET"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        def show(id):
            return self.__lote_control.show()

        @self.__blueprint.route("/", methods=["POST"], strict_slashes=False)
        @self.__jwt_middleware.validate_token
        @self.__lote_middleware.validate_body
        def store():
            return self.__lote_control.store()

        @self.__blueprint.route("/<int:id>", methods=["PUT"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        @self.__lote_middleware.validate_body
        def update(id):
            return self.__lote_control.update()

        @self.__blueprint.route("/<int:id>", methods=["DELETE"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        def destroy(id):
            return self.__lote_control.destroy()

        return self.__blueprint
