# -*- coding: utf-8 -*-
"""
Controller de Organizador (camada Control do MVCS).

Quem chama: OrganizadorRoteador, depois dos middlewares de token/id/corpo.
Devolve: a resposta no envelope (responder) — 200 ou 201.
Não tem regra de negócio: permissão, e-mail único e exclusão são do Service.
"""
from flask import request

from api.service.organizador_service import OrganizadorService
from api.utils.resposta import responder


class OrganizadorControl:
    """Controller de Organizador: orquestra entrada/saída, sem regra de negócio.

    Responsabilidade: ler query string, id da rota e corpo, chamar o Service e responder.
    NÃO decide perfil, NÃO confere permissão e NÃO monta SQL.
    """

    def __init__(self, organizador_service_dependency: OrganizadorService):
        """Recebe o OrganizadorService por injeção de dependência."""
        self.__organizador_service = organizador_service_dependency

    def index(self):
        """GET /organizadores: lê os filtros da query string e devolve a lista (200)."""
        filtros = {}
        # Só os filtros conhecidos são lidos: evita repassar query string arbitrária ao DAO.
        for campo in ("nome", "email", "perfil"):
            valor = request.args.get(campo)
            if valor:
                filtros[campo] = valor.strip()

        organizadores = self.__organizador_service.find_all(filtros)
        return responder(200, "Busca realizada com sucesso", {"organizadores": organizadores})

    def show(self):
        """GET /organizadores/<id>: devolve um organizador (200/404)."""
        # :id da rota — o ParametroMiddleware já garantiu que é inteiro maior que zero.
        id_organizador = request.view_args.get("id")
        organizador = self.__organizador_service.find_by_id(id_organizador)
        return responder(200, "Executado com sucesso", {"organizadores": [organizador]})

    def store(self):
        """POST /organizadores: cadastro feito por administrador, que pode escolher o perfil (201)."""
        from flask import g

        # g.organizador foi preenchido pelo JwtMiddleware: é quem está pedindo o cadastro.
        dados = request.get_json().get("organizador")
        organizador = self.__organizador_service.create_organizador_como_administrador(
            dados, g.organizador
        )
        return responder(201, "Cadastro realizado com sucesso", {"organizadores": [organizador]})

    def update(self):
        """PUT /organizadores/<id>: atualiza o cadastro; a permissão é do Service (200/403/404/409)."""
        from flask import g

        id_organizador = request.view_args.get("id")
        dados = request.get_json().get("organizador")
        # O g.organizador vai junto: o Service decide se este usuário pode editar este id.
        organizador = self.__organizador_service.update_organizador(
            id_organizador, dados, g.organizador
        )
        return responder(200, "Organizador atualizado com sucesso", {"organizadores": [organizador]})

    def destroy(self):
        """DELETE /organizadores/<id>: exclui o cadastro (200/400/403/404/409)."""
        from flask import g

        id_organizador = request.view_args.get("id")
        # Perfil administrador já foi exigido pelo AdminMiddleware no roteador;
        # "não excluir a si mesmo" e "sem eventos vinculados" são regras do Service.
        self.__organizador_service.delete_organizador(id_organizador, g.organizador)
        return responder(200, "Organizador excluído com sucesso")
