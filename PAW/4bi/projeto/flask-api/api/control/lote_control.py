# -*- coding: utf-8 -*-
"""
Controller de Lote (camada Control do MVCS).

Quem chama: LoteRoteador, depois dos middlewares de token/id/corpo.
Devolve: a resposta no envelope (responder) — 200 ou 201.
Não tem regra de negócio: evento existente e nome único são do Service.
"""
from flask import request

from api.service.lote_service import LoteService
from api.utils.resposta import responder


class LoteControl:
    """Controller de Lote: orquestra entrada/saída, sem regra de negócio.

    Responsabilidade: ler query string, id da rota e corpo, chamar o Service e responder.
    NÃO calcula padrões de quantidade e NÃO confere duplicidade.
    """

    def __init__(self, lote_service_dependency: LoteService):
        """Recebe o LoteService por injeção de dependência."""
        self.__lote_service = lote_service_dependency

    def index(self):
        """GET /lotes: lê os filtros da query string e devolve a lista (200)."""
        filtros = {}
        # Só os filtros conhecidos são lidos; preço vem como texto e o DAO converte.
        for campo in ("nome", "idEvento", "precoMin", "precoMax"):
            valor = request.args.get(campo)
            if valor:
                filtros[campo] = valor.strip()

        lotes = self.__lote_service.find_all(filtros)
        return responder(200, "Busca realizada com sucesso", {"lotes": lotes})

    def show(self):
        """GET /lotes/<id>: devolve um lote (200/404)."""
        # :id da rota — o ParametroMiddleware já garantiu que é inteiro maior que zero.
        id_lote = request.view_args.get("id")
        lote = self.__lote_service.find_by_id(id_lote)
        return responder(200, "Executado com sucesso", {"lotes": [lote]})

    def store(self):
        """POST /lotes: cria o lote e devolve o registro com o eventoTitulo (201/400/409)."""
        lote = self.__lote_service.create_lote(request.get_json().get("lote"))
        return responder(201, "Cadastro realizado com sucesso", {"lotes": [lote]})

    def update(self):
        """PUT /lotes/<id>: atualiza o lote; campo ausente mantém o valor atual (200/404/400/409)."""
        id_lote = request.view_args.get("id")
        lote = self.__lote_service.update_lote(id_lote, request.get_json().get("lote"))
        return responder(200, "Lote atualizado com sucesso", {"lotes": [lote]})

    def destroy(self):
        """DELETE /lotes/<id>: exclui o lote (200/404)."""
        id_lote = request.view_args.get("id")
        self.__lote_service.delete_lote(id_lote)
        return responder(200, "Lote excluído com sucesso")
