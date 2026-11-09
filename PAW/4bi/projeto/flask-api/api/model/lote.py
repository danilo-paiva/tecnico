# -*- coding: utf-8 -*-
"""
Model de Lote (camada Model do MVCS).

Quem usa: LoteService (monta o objeto e lê os dados já validados).
Devolve: os atributos do lote; os setters levantam ValueError (vira 400).
"""
from api.utils.validacao import texto, inteiro, decimal


class Lote:
    """Model da entidade Lote (N:1 com Evento).

    Responsabilidade: dados do lote + a regra quantidadeDisponivel <= quantidadeTotal.
    NÃO verifica se o evento existe nem nome repetido: isso é do Service.
    """

    def __init__(self):
        """Cria um lote vazio com preço 0.0 e quantidades indefinidas."""
        self.__id_lote = None
        self.__nome = None
        # Preço padrão é 0.0 (lote gratuito) até o Service informar outro.
        self.__preco = 0.0
        self.__quantidade_total = None
        self.__quantidade_disponivel = None
        self.__id_evento = None

    @property
    def idLote(self):
        return self.__id_lote

    @idLote.setter
    def idLote(self, valor):
        self.__id_lote = inteiro(valor, "idLote", 1)

    @property
    def nome(self):
        return self.__nome

    @nome.setter
    def nome(self, valor):
        self.__nome = texto(valor, "nome", 2, 80)

    @property
    def preco(self):
        return self.__preco

    @preco.setter
    def preco(self, valor):
        # decimal() já arredonda para 2 casas (centavos) e limita o valor.
        self.__preco = decimal(valor, "preco", 0, 999999.99)

    @property
    def quantidadeTotal(self):
        return self.__quantidade_total

    @quantidadeTotal.setter
    def quantidadeTotal(self, valor):
        self.__quantidade_total = inteiro(valor, "quantidadeTotal", 1, 1000000)

    @property
    def quantidadeDisponivel(self):
        return self.__quantidade_disponivel

    @quantidadeDisponivel.setter
    def quantidadeDisponivel(self, valor):
        # Regra de domínio: não dá para liberar mais ingressos do que o lote tem.
        # Se o total ainda não foi definido, a comparação é ignorada (é None).
        numero = inteiro(valor, "quantidadeDisponivel", 0, 1000000)
        if self.__quantidade_total is not None and numero > self.__quantidade_total:
            raise ValueError("quantidadeDisponivel não pode ser maior que quantidadeTotal.")
        self.__quantidade_disponivel = numero

    @property
    def idEvento(self):
        return self.__id_evento

    @idEvento.setter
    def idEvento(self, valor):
        self.__id_evento = inteiro(valor, "idEvento", 1)
