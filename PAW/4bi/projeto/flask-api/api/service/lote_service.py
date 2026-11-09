# -*- coding: utf-8 -*-
"""
Service de Lote (camada Service do MVCS).

Quem usa: LoteControl.
Devolve: dicionários prontos para o envelope de resposta,
e levanta ErrorResponse com o status HTTP (400/404/409) quando a regra falha.
É aqui que ficam as regras de negócio do lote.
"""
from api.model.lote import Lote
from api.utils.error_response import ErrorResponse


class LoteService:
    """Service de Lote: regras do lote e os valores padrão do cadastro.

    Responsabilidade: evento existente, nome único dentro do evento e
    quantidade disponível nunca maior que a total.
    NÃO monta JSON, NÃO lê request e NÃO escreve SQL.
    """

    def __init__(self, lote_dao_dependency, evento_dao_dependency):
        """Recebe o DAO de lote e o de evento (para validar o vínculo) por injeção de dependência."""
        self.__lote_dao = lote_dao_dependency
        self.__evento_dao = evento_dao_dependency

    def create_lote(self, dados: dict) -> dict:
        """Recebe os dados do lote, aplica os padrões, valida as regras e devolve o lote gravado."""
        lote = Lote()
        lote.nome = dados.get("nome")
        # Preço é opcional: sem valor informado o lote é criado como gratuito (0).
        lote.preco = dados.get("preco") if dados.get("preco") is not None else 0
        lote.quantidadeTotal = dados.get("quantidadeTotal")
        # Padrão pedido pelo projeto: sem quantidadeDisponivel, ela começa igual à total.
        # "is not None" e não "or": zero é um valor válido e não pode cair no padrão.
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
        """Repassa os filtros ao DAO e devolve a lista de lotes."""
        return self.__lote_dao.find_all(filtros or {})

    def find_by_id(self, id_lote: int) -> dict:
        """Devolve o lote pelo id; levanta ErrorResponse 404 se não existir."""
        lote = self.__lote_dao.find_by_id(id_lote)
        if not lote:
            raise ErrorResponse(
                404,
                "Lote não encontrado",
                {"mensagem": f"Não existe lote com o id {id_lote}."},
            )
        return lote

    def update_lote(self, id_lote: int, dados: dict) -> dict:
        """Atualiza o lote; o que não vier no corpo mantém o valor atual. Levanta 404/400/409."""
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
        """Exclui o lote; devolve True ou levanta ErrorResponse 404."""
        lote = self.__lote_dao.find_by_id(id_lote)
        if not lote:
            raise ErrorResponse(
                404,
                "Lote não encontrado",
                {"mensagem": f"Não existe lote com o id {id_lote}."},
            )
        return self.__lote_dao.delete(id_lote)

    def __validar_regras(self, lote: Lote):
        """Confere as duas regras de negócio do lote; levanta ErrorResponse 400 ou 409."""
        # Regra 1: o lote precisa pertencer a um evento que exista (400).
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

        # Regra 2: nome único dentro do evento (409); idLote entra como ignorar_id na edição.
        if self.__lote_dao.existe_nome_evento(lote.nome, lote.idEvento, lote.idLote):
            raise ErrorResponse(
                409,
                "Lote duplicado",
                {"mensagem": f'O evento já possui um lote chamado "{lote.nome}".'},
            )
