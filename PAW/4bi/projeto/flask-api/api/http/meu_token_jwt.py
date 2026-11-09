# -*- coding: utf-8 -*-
"""
Geração e validação dos tokens JWT (camada HTTP/segurança).

Quem usa: OrganizadorService (gera no login) e JwtMiddleware (valida).
Devolve: uma string assinada em gerar_token; True/False em validar_token.
A mesma chave, emissor e audiência são usados na API Node.js,
portanto o token de uma API é aceito pela outra.
"""
import secrets
import time

import jwt

from api.config.config import Config


class MeuTokenJWT:
    """Assina e confere tokens JWT (aula de autenticação com JWT).

    Responsabilidade: montar o payload e validar a assinatura.
    NÃO consulta banco e NÃO decide permissão: isso é do Service/middleware.
    Depois de um validar_token bem-sucedido, os claims ficam em .payload.
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
        """Recebe os claims (idOrganizador, nome, email, perfil) e devolve o JWT assinado."""
        agora = int(time.time())
        payload = {
            # Claims padrão do JWT: quem emitiu, para quem, quando e por quanto tempo vale.
            "iss": self.__iss,
            "aud": self.__aud,
            "sub": self.__sub,
            # iat = emitido agora; exp = agora + 30 dias; nbf = já vale a partir de agora.
            "iat": agora,
            "exp": agora + self.__duracao_token,
            "nbf": agora,
            # jti = identificador único do token, evita dois tokens idênticos no mesmo segundo.
            "jti": secrets.token_hex(16),
            # Claims de aplicação: é daqui que o middleware tira o usuário logado.
            "idOrganizador": claims.get("idOrganizador"),
            "nome": claims.get("nome"),
            "email": claims.get("email"),
            "perfil": claims.get("perfil"),
        }

        # headers "typ": JWT só carimba o tipo do token; não entra na validação.
        return jwt.encode(
            payload,
            self.__key,
            algorithm=self.__alg,
            headers={"alg": self.__alg, "typ": self.__type},
        )

    def validar_token(self, string_token: str) -> bool:
        """Recebe o header Authorization (com ou sem "Bearer ") e devolve True se o token confere."""
        # Token ausente ou em branco não é erro de biblioteca: já responde False.
        if not string_token or not str(string_token).strip():
            return False

        # O middleware repassa o header inteiro; aqui o prefixo "Bearer " é descartado.
        token = str(string_token).replace("Bearer ", "").strip()

        try:
            # decode confere assinatura, exp, nbf, iss e aud de uma vez.
            self.__payload = jwt.decode(
                token,
                self.__key,
                algorithms=[self.__alg],
                audience=self.__aud,
                issuer=self.__iss,
            )
            return True
        # Token expirado, assinatura errada ou chave de outra API: cai aqui e vira 401 no middleware.
        except jwt.InvalidTokenError:
            self.__payload = None
            return False

    @property
    def payload(self):
        """Claims do último token válido, ou None — o middleware copia isso para flask.g."""
        return self.__payload
