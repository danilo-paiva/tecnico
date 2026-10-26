# -*- coding: utf-8 -*-
from api.utils.validacao import texto, inteiro, email, telefone


class Organizador:
    """Model da entidade Organizador (também é o usuário autenticado da API).

    Responsabilidade: dados + regras de domínio da entidade.
    Não conhece banco de dados nem HTTP.
    """

    PERFIS = ("administrador", "comum")

    def __init__(self):
        self.__id_organizador = None
        self.__nome = None
        self.__email = None
        self.__senha = None
        self.__telefone = None
        self.__perfil = "comum"

    @property
    def idOrganizador(self):
        return self.__id_organizador

    @idOrganizador.setter
    def idOrganizador(self, valor):
        self.__id_organizador = inteiro(valor, "idOrganizador", 1)

    @property
    def nome(self):
        return self.__nome

    @nome.setter
    def nome(self, valor):
        self.__nome = texto(valor, "nome", 3, 120)

    @property
    def email(self):
        return self.__email

    @email.setter
    def email(self, valor):
        self.__email = email(valor)

    @property
    def senha(self):
        return self.__senha

    @senha.setter
    def senha(self, valor):
        limpa = texto(valor, "senha", 6, 72)
        if " " in limpa:
            raise ValueError("senha não pode conter espaços.")
        self.__senha = limpa

    @property
    def telefone(self):
        return self.__telefone

    @telefone.setter
    def telefone(self, valor):
        self.__telefone = telefone(valor)

    @property
    def perfil(self):
        return self.__perfil

    @perfil.setter
    def perfil(self, valor):
        limpo = str(valor or "").strip().lower()
        if limpo not in Organizador.PERFIS:
            raise ValueError(f"perfil deve ser {' ou '.join(Organizador.PERFIS)}.")
        self.__perfil = limpo
