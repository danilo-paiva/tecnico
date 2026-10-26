# -*- coding: utf-8 -*-
from api.utils.validacao import texto, inteiro, data_hora


class Evento:
    """Model da entidade Evento (N:1 com Organizador)."""

    STATUS = ("planejado", "confirmado", "realizado", "cancelado")

    def __init__(self):
        self.__id_evento = None
        self.__titulo = None
        self.__descricao = None
        self.__data_evento = None
        self.__status = "planejado"
        self.__id_organizador = None

    @property
    def idEvento(self):
        return self.__id_evento

    @idEvento.setter
    def idEvento(self, valor):
        self.__id_evento = inteiro(valor, "idEvento", 1)

    @property
    def titulo(self):
        return self.__titulo

    @titulo.setter
    def titulo(self, valor):
        self.__titulo = texto(valor, "titulo", 3, 150)

    @property
    def descricao(self):
        return self.__descricao

    @descricao.setter
    def descricao(self, valor):
        self.__descricao = texto(valor, "descricao", 1, 2000, obrigatorio=False)

    @property
    def dataEvento(self):
        return self.__data_evento

    @dataEvento.setter
    def dataEvento(self, valor):
        self.__data_evento = data_hora(valor, "dataEvento")

    @property
    def status(self):
        return self.__status

    @status.setter
    def status(self, valor):
        limpo = str(valor or "").strip().lower()
        if limpo not in Evento.STATUS:
            raise ValueError(f"status deve ser {', '.join(Evento.STATUS)}.")
        self.__status = limpo

    @property
    def idOrganizador(self):
        return self.__id_organizador

    @idOrganizador.setter
    def idOrganizador(self, valor):
        self.__id_organizador = inteiro(valor, "idOrganizador", 1)
