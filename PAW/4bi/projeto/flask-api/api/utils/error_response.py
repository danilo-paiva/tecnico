# -*- coding: utf-8 -*-
"""
Erro de aplicação com status HTTP (camada de utilidades).

Quem usa: Services, Controls e Middlewares, no lugar de devolver jsonify na mão.
Devolve: nada — é uma exceção. O tratador global do server.py
converte esta exceção no JSON { success: false, message, data }.
"""


class ErrorResponse(Exception):
    """Exceção que carrega o status HTTP e os detalhes do erro.

    Permite que o Service pare a execução no meio da regra de negócio
    sem conhecer Flask: quem traduz para JSON é o server.py.
    """
    def __init__(self, http_code: int, message: str, details=None):
        """Recebe o status (400, 403, 404, 409...), a mensagem e um dicionário opcional de detalhes."""
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
        """Devolve só a mensagem: é isso que o server.py coloca em "message" na resposta."""
        # args[0] é a mensagem passada ao super().__init__(message).
        return str(self.args[0])
