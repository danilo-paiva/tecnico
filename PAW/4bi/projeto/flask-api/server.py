# -*- coding: utf-8 -*-
"""
Servidor da API Flask (camada de inicialização / composição).

Quem chama: app.py, via Server().init() e Server().run().
O que faz: injeção de dependência entre as camadas (MVCS + DAO),
registro dos Blueprints, CORS, arquivos estáticos e tratamento global de erros.
Não tem regra de negócio nem SQL: só monta o que já existe nas outras pastas.
"""
import os
import traceback

from flask import Flask, jsonify, request, send_from_directory
from flask_cors import CORS
from mysql.connector import IntegrityError
from werkzeug.exceptions import HTTPException

from api.config.config import Config
from api.database.mysql_database import MysqlDatabase
from api.http.meu_token_jwt import MeuTokenJWT
from api.utils.error_response import ErrorResponse

from api.dao.organizador_dao import OrganizadorDAO
from api.dao.evento_dao import EventoDAO
from api.dao.lote_dao import LoteDAO

from api.service.organizador_service import OrganizadorService
from api.service.evento_service import EventoService
from api.service.lote_service import LoteService

from api.control.auth_control import AuthControl
from api.control.organizador_control import OrganizadorControl
from api.control.evento_control import EventoControl
from api.control.lote_control import LoteControl

from api.middleware.jwt_middleware import JwtMiddleware
from api.middleware.admin_middleware import AdminMiddleware
from api.middleware.parametro_middleware import ParametroMiddleware
from api.middleware.organizador_middleware import OrganizadorMiddleware
from api.middleware.evento_middleware import EventoMiddleware
from api.middleware.lote_middleware import LoteMiddleware

from api.router.auth_roteador import AuthRoteador
from api.router.organizador_roteador import OrganizadorRoteador
from api.router.evento_roteador import EventoRoteador
from api.router.lote_roteador import LoteRoteador


class Server:
    """Monta a aplicação Flask inteira e a mantém pronta para atender requisições.

    NÃO valida dados, NÃO decide permissão e NÃO conhece tabela:
    só liga os objetos (DAO -> Service -> Control -> Roteador).
    """

    def __init__(self, porta: int = None):
        """Guarda a porta (padrão: Config.PORTA) e cria o Flask com o frontend estático."""
        self.__porta = porta or Config.PORTA

        # O frontend estático fica em PAW/4bi/projeto/frontend, ao lado de flask-api.
        base = os.path.dirname(os.path.abspath(__file__))
        self.__frontend = os.path.abspath(os.path.join(base, "..", "frontend"))

        # static_folder + static_url_path="" fazem o próprio Flask servir login.html,
        # css e js: o mesmo processo responde a API e as páginas.
        self.__app = Flask(
            __name__,
            static_folder=self.__frontend,
            static_url_path="",
        )
        # CORS liberado porque o frontend é aberto como arquivo ou em outra origem.
        CORS(self.__app, resources={r"/*": {"origins": "*"}})

        self.__db = None
        self.__organizador_service = None
        self.__evento_service = None
        self.__lote_service = None

    # ------------------------------------------------------------------
    # Inicialização
    # ------------------------------------------------------------------
    def init(self):
        """Executa a montagem na ordem certa: banco, middlewares, módulos, erros e rotas."""
        # O banco vem primeiro porque os DAOs recebem a instância pronta.
        self.__conectar_banco()
        self.__configurar_middlewares()
        self.__configurar_modulos()
        self.__configurar_tratamento_de_erros()
        self.__configurar_rotas_gerais()

    def __conectar_banco(self):
        """Cria o MysqlDatabase e apenas testa a conexão; não derruba a API se falhar."""
        self.__db = MysqlDatabase()
        try:
            self.__db.testar_conexao()
            print(f"Banco de dados conectado: {Config.DB_DATABASE}")
        except Exception as erro:
            # A falha é só avisada: a API sobe e /api/v1/health devolve 503.
            print(f"[ATENÇÃO] Não foi possível conectar ao MySQL: {erro}")
            print("Confira se o MySQL está ligado e se docs/banco.sql foi importado.")

    def __configurar_middlewares(self):
        """Instancia os middlewares (decorators) usados pelos roteadores."""
        self.__jwt_middleware = JwtMiddleware()
        self.__admin_middleware = AdminMiddleware()
        self.__parametro_middleware = ParametroMiddleware()
        self.__organizador_middleware = OrganizadorMiddleware()
        self.__evento_middleware = EventoMiddleware()
        self.__lote_middleware = LoteMiddleware()

        # Middleware de log: roda em TODAS as requisições, antes de qualquer rota.
        @self.__app.before_request
        def log_requisicao():
            print("-" * 70)
            print(f"{request.method} {request.path}")

    def __configurar_modulos(self):
        # Injeção de dependência, de baixo para cima: DAO -> Service -> Control -> Roteador.
        # Nenhuma camada cria a sua dependência; todas recebem pelo construtor.
        organizador_dao = OrganizadorDAO(self.__db)
        evento_dao = EventoDAO(self.__db)
        lote_dao = LoteDAO(self.__db)

        # Um único MeuTokenJWT para o servidor todo: gera e valida com a mesma chave.
        token_jwt = MeuTokenJWT()

        # Cada Service recebe só os DAOs de que precisa; o de organizador também
        # recebe o token porque o login devolve o JWT.
        self.__organizador_service = OrganizadorService(organizador_dao, evento_dao, token_jwt)
        self.__evento_service = EventoService(evento_dao, organizador_dao, lote_dao)
        self.__lote_service = LoteService(lote_dao, evento_dao)

        # Control só orquestra: recebe a requisição, chama o Service e devolve a resposta.
        auth_control = AuthControl(self.__organizador_service)
        organizador_control = OrganizadorControl(self.__organizador_service)
        evento_control = EventoControl(self.__evento_service)
        lote_control = LoteControl(self.__lote_service)

        # Prefixo comum: /api/v1/... (mesmo contrato da API Node.js).
        prefixo = Config.API_PREFIXO

        # Roteador de auth: /login e /registrar são públicos; /perfil usa JWT.
        auth_roteador = AuthRoteador(
            self.__jwt_middleware, self.__organizador_middleware, auth_control
        )
        # Cada Blueprint é registrado com o seu prefixo de recurso.
        self.__app.register_blueprint(auth_roteador.create_routes(), url_prefix=f"{prefixo}/auth")

        organizador_roteador = OrganizadorRoteador(
            self.__jwt_middleware,
            self.__admin_middleware,
            self.__parametro_middleware,
            self.__organizador_middleware,
            organizador_control,
        )
        self.__app.register_blueprint(
            organizador_roteador.create_routes(), url_prefix=f"{prefixo}/organizadores"
        )

        evento_roteador = EventoRoteador(
            self.__jwt_middleware,
            self.__parametro_middleware,
            self.__evento_middleware,
            evento_control,
        )
        self.__app.register_blueprint(
            evento_roteador.create_routes(), url_prefix=f"{prefixo}/eventos"
        )

        lote_roteador = LoteRoteador(
            self.__jwt_middleware,
            self.__parametro_middleware,
            self.__lote_middleware,
            lote_control,
        )
        self.__app.register_blueprint(
            lote_roteador.create_routes(), url_prefix=f"{prefixo}/lotes"
        )

    def __configurar_rotas_gerais(self):
        """Registra as rotas fora dos recursos: "/" (frontend) e "/api/v1/health"."""
        # A raiz entrega o login.html/index.html do frontend estático.
        @self.__app.route("/")
        def raiz():
            return send_from_directory(self.__frontend, "index.html")

        # Diagnóstico: responde 200 com o banco no ar e 503 quando o MySQL está fora.
        @self.__app.route(f"{Config.API_PREFIXO}/health")
        def health():
            try:
                self.__db.testar_conexao()
                return jsonify({
                    "success": True,
                    "message": "API online",
                    "data": {
                        "api": Config.API_NOME,
                        "versao": Config.API_VERSAO,
                        "banco": Config.DB_DATABASE,
                    },
                }), 200
            # 503 = serviço indisponível: a API está de pé, mas sem banco.
            except Exception as erro:
                return jsonify({
                    "success": False,
                    "message": "API online, banco indisponível",
                    "data": {"api": Config.API_NOME, "erro": str(erro)},
                }), 503

    # ------------------------------------------------------------------
    # Tratamento de erros
    # ------------------------------------------------------------------
    def __configurar_tratamento_de_erros(self):
        """Um tratador global: converte qualquer exceção no envelope {success, message, data}."""
        @self.__app.errorhandler(Exception)
        def tratar_erro(error):
            # Ordem dos isinstance importa: do erro mais específico para o mais genérico.
            # ErrorResponse já traz o status HTTP escolhido pela camada de negócio.
            if isinstance(error, ErrorResponse):
                return jsonify({
                    "success": False,
                    "message": str(error),
                    "data": {"detalhes": error.details},
                }), error.http_code

            # ValueError vem dos setters do Model: dado quebrou a regra de domínio -> 400.
            if isinstance(error, ValueError):
                return jsonify({
                    "success": False,
                    "message": "Erro de validação",
                    "data": {"mensagem": str(error)},
                }), 400

            # IntegrityError = UNIQUE/FK do MySQL (e-mail repetido, vínculo existente) -> 409.
            if isinstance(error, IntegrityError):
                return jsonify({
                    "success": False,
                    "message": "Registro duplicado ou em uso",
                    "data": {"mensagem": "Verifique os dados e os vínculos entre registros."},
                }), 409

            if isinstance(error, HTTPException):
                # 404/405 seguem a mesma resposta da API Node.js (rota inexistente).
                if error.code in (404, 405):
                    return jsonify({
                        "success": False,
                        "message": "Rota não encontrada",
                        "data": {
                            "mensagem": f"{request.method} {request.path} não existe nesta API."
                        }, 
                    }), 404

                return jsonify({
                    "success": False,
                    "message": error.description or "Erro na requisição",
                    "data": {"status": error.code},
                }), error.code

            # Qualquer outra exceção não prevista: loga o traceback e responde 500 sem detalhes.
            print(traceback.format_exc())
            return jsonify({
                "success": False,
                "message": "Erro interno do servidor",
                "data": {"mensagem": "Consulte o terminal da API para mais detalhes."},
            }), 500

    def run(self):
        """Sobe o Flask em 0.0.0.0 e bloqueia o terminal até Ctrl+C."""
        print(f"{Config.API_NOME} rodando em http://localhost:{self.__porta}/")
        print(f"Frontend: http://localhost:{self.__porta}/login.html")
        # debug=False e use_reloader=False: sem reload automático, o MySQL não é reconectado
        # a cada salvamento e o pool continua um só.
        self.__app.run(host="0.0.0.0", port=self.__porta, debug=False, use_reloader=False)
