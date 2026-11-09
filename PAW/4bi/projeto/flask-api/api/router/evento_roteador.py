# -*- coding: utf-8 -*-
"""
Roteador de Evento (camada Roteador do MVCS — Blueprint do Flask).

Quem chama: Server.__configurar_modulos(), via register_blueprint com /api/v1/eventos.
Devolve: o Blueprint com as cinco rotas de evento.
Não tem regra de negócio nem SQL: só liga rota -> middlewares -> Control.
"""
from flask import Blueprint

from api.control.evento_control import EventoControl
from api.middleware.evento_middleware import EventoMiddleware
from api.middleware.jwt_middleware import JwtMiddleware
from api.middleware.parametro_middleware import ParametroMiddleware


class EventoRoteador:
    """Roteador de Evento (todas as rotas exigem JWT).

    Ordem dos decorators (o de cima executa primeiro):
    token -> validação de corpo -> parâmetro -> Control.
    """

    def __init__(
        self,
        jwt_middleware: JwtMiddleware,
        parametro_middleware: ParametroMiddleware,
        evento_middleware: EventoMiddleware,
        evento_control: EventoControl,
    ):
        """Recebe os middlewares e o Control por injeção de dependência (não cria nada)."""
        self.__jwt_middleware = jwt_middleware
        self.__parametro_middleware = parametro_middleware
        self.__evento_middleware = evento_middleware
        self.__evento_control = evento_control
        self.__blueprint = Blueprint("evento", __name__)

    def create_routes(self):
        """Monta as cinco rotas no Blueprint e devolve o Blueprint para o register_blueprint."""
        # Listagem: só o token (qualquer organizador logado pode consultar).
        @self.__blueprint.route("/", methods=["GET"], strict_slashes=False)
        @self.__jwt_middleware.validate_token
        def index():
            return self.__evento_control.index()

        # Leitura de um registro: token -> id da rota -> Control.
        @self.__blueprint.route("/<int:id>", methods=["GET"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        def show(id):
            return self.__evento_control.show()

        # Criação: token -> corpo válido -> Control (regras de data/título no Service).
        @self.__blueprint.route("/", methods=["POST"], strict_slashes=False)
        @self.__jwt_middleware.validate_token
        @self.__evento_middleware.validate_body
        def store():
            return self.__evento_control.store()

        # Edição: token -> id -> corpo (atualização parcial, campos ausentes preservados).
        @self.__blueprint.route("/<int:id>", methods=["PUT"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        @self.__evento_middleware.validate_body
        def update(id):
            return self.__evento_control.update()

        # Exclusão: token -> id -> Control; se houver lote vinculado o Service responde 409.
        @self.__blueprint.route("/<int:id>", methods=["DELETE"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        def destroy(id):
            return self.__evento_control.destroy()

        return self.__blueprint
