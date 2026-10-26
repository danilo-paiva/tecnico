# -*- coding: utf-8 -*-
from datetime import datetime

from api.model.evento import Evento
from api.utils.error_response import ErrorResponse


class EventoService:
    """Service de Evento.

    Regras: organizador existente, data no futuro, título único por data
    e bloqueio de exclusão quando existem lotes vinculados.
    """

    def __init__(self, evento_dao_dependency, organizador_dao_dependency, lote_dao_dependency):
        self.__evento_dao = evento_dao_dependency
        self.__organizador_dao = organizador_dao_dependency
        self.__lote_dao = lote_dao_dependency

    def create_evento(self, dados: dict) -> dict:
        evento = Evento()
        evento.titulo = dados.get("titulo")
        evento.descricao = dados.get("descricao")
        evento.dataEvento = dados.get("dataEvento")
        evento.status = dados.get("status") or "planejado"
        evento.idOrganizador = dados.get("idOrganizador")

        self.__validar_regras(evento)

        id_evento = self.__evento_dao.create(evento)
        return self.__evento_dao.find_by_id(id_evento)

    def find_all(self, filtros: dict = None) -> list[dict]:
        return self.__evento_dao.find_all(filtros or {})

    def find_by_id(self, id_evento: int) -> dict:
        evento = self.__evento_dao.find_by_id(id_evento)
        if not evento:
            raise ErrorResponse(
                404,
                "Evento não encontrado",
                {"mensagem": f"Não existe evento com o id {id_evento}."},
            )
        return evento

    def update_evento(self, id_evento: int, dados: dict) -> dict:
        atual = self.__evento_dao.find_by_id(id_evento)
        if not atual:
            raise ErrorResponse(
                404,
                "Evento não encontrado",
                {"mensagem": f"Não existe evento com o id {id_evento}."},
            )

        evento = Evento()
        evento.idEvento = id_evento
        evento.titulo = dados.get("titulo", atual["titulo"])
        evento.descricao = dados.get("descricao", atual["descricao"])
        evento.dataEvento = dados.get("dataEvento", atual["dataEvento"])
        evento.status = dados.get("status", atual["status"])
        evento.idOrganizador = dados.get("idOrganizador", atual["idOrganizador"])

        self.__validar_regras(evento)

        if not self.__evento_dao.update(evento):
            raise ErrorResponse(500, "Falha ao atualizar evento")

        return self.__evento_dao.find_by_id(id_evento)

    def delete_evento(self, id_evento: int) -> bool:
        evento = self.__evento_dao.find_by_id(id_evento)
        if not evento:
            raise ErrorResponse(
                404,
                "Evento não encontrado",
                {"mensagem": f"Não existe evento com o id {id_evento}."},
            )

        total_lotes = self.__lote_dao.count_by_evento(id_evento)
        if total_lotes > 0:
            raise ErrorResponse(
                409,
                "Evento possui lotes vinculados",
                {
                    "mensagem": (
                        f"Exclua os {total_lotes} lote(s) deste evento antes de removê-lo."
                    )
                },
            )

        return self.__evento_dao.delete(id_evento)

    def __validar_regras(self, evento: Evento):
        organizador = self.__organizador_dao.find_by_id(evento.idOrganizador)
        if not organizador:
            raise ErrorResponse(
                400,
                "Organizador inválido",
                {
                    "campo": "idOrganizador",
                    "mensagem": f"Não existe organizador com o id {evento.idOrganizador}.",
                },
            )

        if evento.dataEvento < datetime.now():
            raise ErrorResponse(
                400,
                "Data inválida",
                {"campo": "dataEvento", "mensagem": "A data do evento não pode estar no passado."},
            )

        if self.__evento_dao.existe_titulo_data(
            evento.titulo, evento.dataEvento, evento.idEvento
        ):
            raise ErrorResponse(
                409,
                "Evento duplicado",
                {"mensagem": "Já existe um evento com este título nesta data/hora."},
            )
