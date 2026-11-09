# -*- coding: utf-8 -*-
"""
Service de Evento (camada Service do MVCS).

Quem usa: EventoControl.
Devolve: dicionários prontos para o envelope de resposta,
e levanta ErrorResponse com o status HTTP (400/404/409) quando a regra falha.
É aqui que ficam as regras de negócio do evento.
"""
from datetime import datetime

from api.model.evento import Evento
from api.utils.error_response import ErrorResponse


class EventoService:
    """Service de Evento: regras do evento e a proteção na exclusão.

    Responsabilidade: organizador existente, data não passada, título único
    por data/hora e bloqueio de exclusão quando existem lotes vinculados.
    NÃO monta JSON, NÃO lê request e NÃO escreve SQL.
    """

    def __init__(self, evento_dao_dependency, organizador_dao_dependency, lote_dao_dependency):
        """Recebe os três DAOs por injeção de dependência: evento, organizador e lote."""
        self.__evento_dao = evento_dao_dependency
        self.__organizador_dao = organizador_dao_dependency
        self.__lote_dao = lote_dao_dependency

    def create_evento(self, dados: dict) -> dict:
        """Recebe os dados do evento, valida as regras e devolve o evento gravado."""
        evento = Evento()
        evento.titulo = dados.get("titulo")
        evento.descricao = dados.get("descricao")
        evento.dataEvento = dados.get("dataEvento")
        # status é opcional no cadastro: sem valor, o evento nasce "planejado".
        evento.status = dados.get("status") or "planejado"
        evento.idOrganizador = dados.get("idOrganizador")

        self.__validar_regras(evento)

        id_evento = self.__evento_dao.create(evento)
        return self.__evento_dao.find_by_id(id_evento)

    def find_all(self, filtros: dict = None) -> list[dict]:
        """Repassa os filtros ao DAO e devolve a lista de eventos."""
        return self.__evento_dao.find_all(filtros or {})

    def find_by_id(self, id_evento: int) -> dict:
        """Devolve o evento pelo id; levanta ErrorResponse 404 se não existir."""
        evento = self.__evento_dao.find_by_id(id_evento)
        if not evento:
            raise ErrorResponse(
                404,
                "Evento não encontrado",
                {"mensagem": f"Não existe evento com o id {id_evento}."},
            )
        return evento

    def update_evento(self, id_evento: int, dados: dict) -> dict:
        """Atualiza o evento; o que não vier no corpo mantém o valor atual. Levanta 404/400/409."""
        atual = self.__evento_dao.find_by_id(id_evento)
        if not atual:
            raise ErrorResponse(
                404,
                "Evento não encontrado",
                {"mensagem": f"Não existe evento com o id {id_evento}."},
            )

        evento = Evento()
        evento.idEvento = id_evento
        # get(campo, atual[campo]) = atualização parcial: campo ausente continua com o valor gravado.
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
        """Exclui o evento; devolve True ou levanta 404 (não existe) ou 409 (tem lotes)."""
        evento = self.__evento_dao.find_by_id(id_evento)
        if not evento:
            raise ErrorResponse(
                404,
                "Evento não encontrado",
                {"mensagem": f"Não existe evento com o id {id_evento}."},
            )

        # Lote aponta para evento: apagar o evento antes deixaria lote órfão (409).
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
        """Confere as três regras de negócio do evento; levanta ErrorResponse 400 ou 409."""
        # Regra 1: o organizador informado precisa existir (400, é dado inválido).
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

        # Regra 2: não se agenda evento no passado (400).
        if evento.dataEvento < datetime.now():
            raise ErrorResponse(
                400,
                "Data inválida",
                {"campo": "dataEvento", "mensagem": "A data do evento não pode estar no passado."},
            )

        # Regra 3: título único na mesma data/hora (409). No create idEvento é None,
        # então a checagem não ignora nenhum registro; no update, ignora o próprio.
        if self.__evento_dao.existe_titulo_data(
            evento.titulo, evento.dataEvento, evento.idEvento
        ):
            raise ErrorResponse(
                409,
                "Evento duplicado",
                {"mensagem": "Já existe um evento com este título nesta data/hora."},
            )
