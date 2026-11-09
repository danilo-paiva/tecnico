const express = require("express");
const asyncHandler = require("../utils/asyncHandler");

/**
 * Roteador de autenticação.
 * Rotas públicas: /login e /registrar. Rota protegida: /perfil.
 * O roteador só liga rota → middleware → Controle. Aqui a ordem é a ordem de
 * execução: o middleware de corpo valida antes e o Controle só roda se passou.
 */
module.exports = class AuthRoteador {
    #jwtMiddleware;
    #organizadorMiddleware;
    #authControle;

    constructor(jwtMiddleware, organizadorMiddleware, authControle) {
        this.#jwtMiddleware = jwtMiddleware;
        this.#organizadorMiddleware = organizadorMiddleware;
        this.#authControle = authControle;
    }

    /** Monta as três rotas e devolve o Router, que o Servidor registra em /auth. */
    criarRotas() {
        const router = express.Router();

        // Público: corpo com email e senha → Controle (400 vem do middleware).
        router.post(
            "/login",
            this.#organizadorMiddleware.validarLoginBody,
            asyncHandler(this.#authControle.login)
        );

        // Público: mesmo validador de corpo; no POST ele exige nome, email e senha.
        router.post(
            "/registrar",
            this.#organizadorMiddleware.validarBody,
            asyncHandler(this.#authControle.registrar)
        );

        // Protegido: sem token válido o JwtMiddleware responde 401 e o Controle não roda.
        router.get(
            "/perfil",
            this.#jwtMiddleware.validarToken,
            asyncHandler(this.#authControle.perfil)
        );

        return router;
    }
};
