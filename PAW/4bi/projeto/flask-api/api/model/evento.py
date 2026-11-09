# -*- coding: utf-8 -*-
"""
Model de Evento (camada Model do MVCS).

Quem usa: EventoService (monta o objeto e lê os dados já validados).
Devolve: os atributos do evento; os setters levantam ValueError (vira 400).
"""
from api.utils.validacao import texto, inteiro, data_hora


class Evento:
    """Model da entidade Evento (N:1 com Organizador).

    Responsabilidade: dados do evento + regras de domínio do próprio campo.
    NÃO verifica se o organizador existe nem duplicidade: isso é do Service.
    """

    # Situação do evento na agenda; fora destes quatro valores o setter recusa.
    STATUS = ("planejado", "confirmado", "realizado", "cancelado")

    def __init__(self):
        """Cria um evento vazio com status padrão "planejado" e sem dono definido."""
        self.__id_evento = None
        self.__titulo = None
        self.__descricao = None
        self.__data_evento = None
        # status padrão: evento recém-criado na agenda.
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
        # descrição é opcional: vazia vira None em vez de erro.
        self.__descricao = texto(valor, "descricao", 1, 2000, obrigatorio=False)

    @property
    def dataEvento(self):
        return self.__data_evento

    @dataEvento.setter
    def dataEvento(self, valor):
        # Aceita datetime ou texto em vários formatos e devolve sempre datetime.
        self.__data_evento = data_hora(valor, "dataEvento")

    @property
    def status(self):
        return self.__status

    @status.setter
    def status(self, valor):
        # Comparação contra a tupla STATUS: qualquer outro valor levanta ValueError.
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
