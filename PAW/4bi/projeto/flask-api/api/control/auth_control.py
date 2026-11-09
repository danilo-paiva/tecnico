# -*- coding: utf-8 -*-
"""
Controller de autenticação (camada Control do MVCS).

Quem chama: AuthRoteador, depois do middleware de corpo ou de token.
Devolve: a resposta no envelope (responder) — 200 no login/perfil e 201 no cadastro.
Não tem regra de negócio: quem valida e-mail, senha e perfil é o OrganizadorService.
"""
from flask import request

from api.service.organizador_service import OrganizadorService
from api.utils.resposta import responder


class AuthControl:
    """Controller de autenticação: login, cadastro público e perfil do token.

    Responsabilidade: ler o corpo da requisição, chamar o Service e montar a resposta.
    NÃO valida e-mail/senha, NÃO gera hash e NÃO decide perfil.
    """

    def __init__(self, organizador_service_dependency: OrganizadorService):
        """Recebe o OrganizadorService por injeção de dependência."""
        self.__organizador_service = organizador_service_dependency

    def login(self):
        """Lê { organizador: { email, senha } } do corpo e devolve os dados + o token (ou 400/401)."""
        # O corpo vem sempre dentro de um objeto "organizador" (mesmo contrato da API Node.js).
        dados = request.get_json().get("organizador")
        organizador, token = self.__organizador_service.login(
            dados.get("email"), dados.get("senha")
        )
        return responder(200, "Login realizado com sucesso", {
            "organizadores": [organizador],
            "token": token,
        })

    def registrar(self):
        """Cadastro público: cria a conta como "comum" e já devolve o token (201)."""
        # perfil="comum" fixo: o cliente não escolhe o perfil no cadastro público.
        organizador = self.__organizador_service.create_organizador(
            request.get_json().get("organizador"), perfil="comum"
        )
        # Faz login logo depois para o frontend receber o token e entrar direto.
        dados, token = self.__organizador_service.login(
            organizador["email"], request.get_json().get("organizador").get("senha")
        )
        return responder(201, "Conta criada com sucesso", {
            "organizadores": [dados],
            "token": token,
        })

    def perfil(self):
        """Devolve o organizador do token; o id sai do flask.g, não da URL (200/404)."""
        from flask import g

        # g.organizador foi preenchido pelo JwtMiddleware: é o usuário do token.
        organizador = self.__organizador_service.find_by_id(g.organizador.get("idOrganizador"))
        return responder(200, "Executado com sucesso", {"organizadores": [organizador]})
