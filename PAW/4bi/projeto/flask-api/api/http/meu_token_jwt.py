# -*- coding: utf-8 -*-
import secrets
import time

import jwt

from api.config.config import Config


class MeuTokenJWT:
    """Geração e validação de tokens JWT (aula paw04x03).

    A mesma chave, emissor e audiência são usados na API Node.js,
    portanto o token de uma API é aceito pela outra.
    """

    def __init__(self):
        self.__key = Config.JWT_KEY
        self.__alg = Config.JWT_ALG
        self.__type = Config.JWT_TIPO
        self.__iss = Config.JWT_ISS
        self.__aud = Config.JWT_AUD
        self.__sub = Config.JWT_SUB
        self.__duracao_token = Config.JWT_DURACAO_SEGUNDOS
        self.__payload = None

    def gerar_token(self, claims: dict) -> str:
        agora = int(time.time())
        payload = {
            "iss": self.__iss,
            "aud": self.__aud,
            "sub": self.__sub,
            "iat": agora,
            "exp": agora + self.__duracao_token,
            "nbf": agora,
            "jti": secrets.token_hex(16),
            "idOrganizador": claims.get("idOrganizador"),
            "nome": claims.get("nome"),
            "email": claims.get("email"),
            "perfil": claims.get("perfil"),
        }

        return jwt.encode(
            payload,
            self.__key,
            algorithm=self.__alg,
            headers={"alg": self.__alg, "typ": self.__type},
        )

    def validar_token(self, string_token: str) -> bool:
        if not string_token or not str(string_token).strip():
            return False

        token = str(string_token).replace("Bearer ", "").strip()

        try:
            self.__payload = jwt.decode(
                token,
                self.__key,
                algorithms=[self.__alg],
                audience=self.__aud,
                issuer=self.__iss,
            )
            return True
        except jwt.InvalidTokenError:
            self.__payload = None
            return False

    @property
    def payload(self):
        return self.__payload
