const express = require("express");
const asyncHandler = require("../utils/asyncHandler");

/**
 * Roteador de autenticação.
 * Rotas públicas: /login e /registrar. Rota protegida: /perfil.
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

    criarRotas() {
        const router = express.Router();

        router.post(
            "/login",
            this.#organizadorMiddleware.validarLoginBody,
            asyncHandler(this.#authControle.login)
        );

        router.post(
            "/registrar",
            this.#organizadorMiddleware.validarBody,
            asyncHandler(this.#authControle.registrar)
        );

        router.get(
            "/perfil",
            this.#jwtMiddleware.validarToken,
            asyncHandler(this.#authControle.perfil)
        );

        return router;
    }
};
