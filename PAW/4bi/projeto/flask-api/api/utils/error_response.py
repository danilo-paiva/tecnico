# -*- coding: utf-8 -*-
"""
Erro de aplicação com status HTTP.

Usado por services, controls e middlewares para padronizar as respostas.
O tratador global do servidor converte esta exceção em JSON.
"""


class ErrorResponse(Exception):
    def __init__(self, http_code: int, message: str, details=None):
        super().__init__(message)
        self.__http_code = http_code
        self.__details = details

    @property
    def http_code(self) -> int:
        return self.__http_code

    @property
    def details(self):
        return self.__details

    def __str__(self) -> str:
        return str(self.args[0])
