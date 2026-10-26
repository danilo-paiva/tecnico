# -*- coding: utf-8 -*-
from api.model.lote import Lote
from api.utils.error_response import ErrorResponse


class LoteService:
    """Service de Lote.

    Regras: evento existente, nome único dentro do evento,
    quantidade disponível nunca maior que a total.
    """

    def __init__(self, lote_dao_dependency, evento_dao_dependency):
        self.__lote_dao = lote_dao_dependency
        self.__evento_dao = evento_dao_dependency

    def create_lote(self, dados: dict) -> dict:
        lote = Lote()
        lote.nome = dados.get("nome")
        lote.preco = dados.get("preco") if dados.get("preco") is not None else 0
        lote.quantidadeTotal = dados.get("quantidadeTotal")
        lote.quantidadeDisponivel = (
            dados.get("quantidadeDisponivel")
            if dados.get("quantidadeDisponivel") is not None
            else dados.get("quantidadeTotal")
        )
        lote.idEvento = dados.get("idEvento")

        self.__validar_regras(lote)

        id_lote = self.__lote_dao.create(lote)
        return self.__lote_dao.find_by_id(id_lote)

    def find_all(self, filtros: dict = None) -> list[dict]:
        return self.__lote_dao.find_all(filtros or {})

    def find_by_id(self, id_lote: int) -> dict:
        lote = self.__lote_dao.find_by_id(id_lote)
        if not lote:
            raise ErrorResponse(
                404,
                "Lote não encontrado",
                {"mensagem": f"Não existe lote com o id {id_lote}."},
            )
        return lote

    def update_lote(self, id_lote: int, dados: dict) -> dict:
        atual = self.__lote_dao.find_by_id(id_lote)
        if not atual:
            raise ErrorResponse(
                404,
                "Lote não encontrado",
                {"mensagem": f"Não existe lote com o id {id_lote}."},
            )

        lote = Lote()
        lote.idLote = id_lote
        lote.nome = dados.get("nome", atual["nome"])
        lote.preco = dados.get("preco", atual["preco"])
        lote.quantidadeTotal = dados.get("quantidadeTotal", atual["quantidadeTotal"])
        lote.quantidadeDisponivel = dados.get(
            "quantidadeDisponivel", atual["quantidadeDisponivel"]
        )
        lote.idEvento = dados.get("idEvento", atual["idEvento"])

        self.__validar_regras(lote)

        if not self.__lote_dao.update(lote):
            raise ErrorResponse(500, "Falha ao atualizar lote")

        return self.__lote_dao.find_by_id(id_lote)

    def delete_lote(self, id_lote: int) -> bool:
        lote = self.__lote_dao.find_by_id(id_lote)
        if not lote:
            raise ErrorResponse(
                404,
                "Lote não encontrado",
                {"mensagem": f"Não existe lote com o id {id_lote}."},
            )
        return self.__lote_dao.delete(id_lote)

    def __validar_regras(self, lote: Lote):
        evento = self.__evento_dao.find_by_id(lote.idEvento)
        if not evento:
            raise ErrorResponse(
                400,
                "Evento inválido",
                {
                    "campo": "idEvento",
                    "mensagem": f"Não existe evento com o id {lote.idEvento}.",
                },
            )

        if self.__lote_dao.existe_nome_evento(lote.nome, lote.idEvento, lote.idLote):
            raise ErrorResponse(
                409,
                "Lote duplicado",
                {"mensagem": f'O evento já possui um lote chamado "{lote.nome}".'},
            )
