# -*- coding: utf-8 -*-
"""
Roteador de Lote (camada Roteador do MVCS — Blueprint do Flask).

Quem chama: Server.__configurar_modulos(), via register_blueprint com /api/v1/lotes.
Devolve: o Blueprint com as cinco rotas de lote.
Não tem regra de negócio nem SQL: só liga rota -> middlewares -> Control.
"""
from flask import Blueprint

from api.control.lote_control import LoteControl
from api.middleware.jwt_middleware import JwtMiddleware
from api.middleware.lote_middleware import LoteMiddleware
from api.middleware.parametro_middleware import ParametroMiddleware


class LoteRoteador:
    """Roteador de Lote (todas as rotas exigem JWT).

    Ordem dos decorators (o de cima executa primeiro):
    token -> validação de corpo -> parâmetro -> Control.
    """

    def __init__(
        self,
        jwt_middleware: JwtMiddleware,
        parametro_middleware: ParametroMiddleware,
        lote_middleware: LoteMiddleware,
        lote_control: LoteControl,
    ):
        """Recebe os middlewares e o Control por injeção de dependência (não cria nada)."""
        self.__jwt_middleware = jwt_middleware
        self.__parametro_middleware = parametro_middleware
        self.__lote_middleware = lote_middleware
        self.__lote_control = lote_control
        self.__blueprint = Blueprint("lote", __name__)

    def create_routes(self):
        """Monta as cinco rotas no Blueprint e devolve o Blueprint para o register_blueprint."""
        # Listagem: só o token (qualquer organizador logado pode consultar).
        @self.__blueprint.route("/", methods=["GET"], strict_slashes=False)
        @self.__jwt_middleware.validate_token
        def index():
            return self.__lote_control.index()

        # Leitura de um registro: token -> id da rota -> Control.
        @self.__blueprint.route("/<int:id>", methods=["GET"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        def show(id):
            return self.__lote_control.show()

        # Criação: token -> corpo válido -> Control (padrões de preço/quantidade no Service).
        @self.__blueprint.route("/", methods=["POST"], strict_slashes=False)
        @self.__jwt_middleware.validate_token
        @self.__lote_middleware.validate_body
        def store():
            return self.__lote_control.store()

        # Edição: token -> id -> corpo (atualização parcial, campos ausentes preservados).
        @self.__blueprint.route("/<int:id>", methods=["PUT"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        @self.__lote_middleware.validate_body
        def update(id):
            return self.__lote_control.update()

        # Exclusão: token -> id -> Control (lote não é referenciado por ninguém).
        @self.__blueprint.route("/<int:id>", methods=["DELETE"])
        @self.__jwt_middleware.validate_token
        @self.__parametro_middleware.validate_id
        def destroy(id):
            return self.__lote_control.destroy()

        return self.__blueprint
