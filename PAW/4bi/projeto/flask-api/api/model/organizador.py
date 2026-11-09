# -*- coding: utf-8 -*-
"""
Model de Organizador (camada Model do MVCS).

Quem usa: OrganizadorService (monta o objeto e lê os dados já validados).
Devolve: os atributos do organizador; os setters levantam ValueError (vira 400).
"""
from api.utils.validacao import texto, inteiro, email, telefone


class Organizador:
    """Model da entidade Organizador (também é o usuário autenticado da API).

    Responsabilidade: guardar os dados e aplicar as regras de domínio do cadastro.
    O mesmo registro serve de usuário: o perfil daqui é o que o JWT carrega.
    NÃO conhece banco de dados, SQL nem HTTP — isso fica no DAO/Control.
    """

    # Perfis possíveis: administrador gerencia tudo; comum só o próprio cadastro.
    PERFIS = ("administrador", "comum")

    def __init__(self):
        """Cria um organizador vazio (perfil "comum"); os setters validam o restante."""
        self.__id_organizador = None
        self.__nome = None
        self.__email = None
        self.__senha = None
        self.__telefone = None
        # Todo cadastro nasce "comum"; só administrador pode criar outro perfil.
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
        # O e-mail é normalizado para minúsculas aqui; a unicidade é do Service.
        self.__email = email(valor)

    @property
    def senha(self):
        return self.__senha

    @senha.setter
    def senha(self, valor):
        # 72 é o limite do bcrypt em bytes; espaço não entra na senha (facilita login).
        limpa = texto(valor, "senha", 6, 72)
        if " " in limpa:
            raise ValueError("senha não pode conter espaços.")
        self.__senha = limpa

    @property
    def telefone(self):
        return self.__telefone

    @telefone.setter
    def telefone(self, valor):
        # telefone vazio vira None: é o único campo opcional do cadastro.
        self.__telefone = telefone(valor)

    @property
    def perfil(self):
        return self.__perfil

    @perfil.setter
    def perfil(self, valor):
        # perfil sempre em minúsculas e apenas entre os PERFIS conhecidos.
        limpo = str(valor or "").strip().lower()
        if limpo not in Organizador.PERFIS:
            raise ValueError(f"perfil deve ser {' ou '.join(Organizador.PERFIS)}.")
        self.__perfil = limpo
