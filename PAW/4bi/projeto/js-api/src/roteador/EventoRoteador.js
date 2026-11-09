const express = require("express");
const asyncHandler = require("../utils/asyncHandler");

/**
 * Roteador de Evento (todas as rotas exigem JWT).
 * Ordem em cada rota: token (router.use) → id da URL → corpo → Controle.
 */
module.exports = class EventoRoteador {
    #jwtMiddleware;
    #parametroMiddleware;
    #eventoMiddleware;
    #eventoControle;

    constructor(jwtMiddleware, parametroMiddleware, eventoMiddleware, eventoControle) {
        this.#jwtMiddleware = jwtMiddleware;
        this.#parametroMiddleware = parametroMiddleware;
        this.#eventoMiddleware = eventoMiddleware;
        this.#eventoControle = eventoControle;
    }

    /** Devolve o Router, que o Servidor registra no prefixo /eventos. */
    criarRotas() {
        const router = express.Router();

        // router.use vale para todas as rotas abaixo: sem token, nada executa.
        router.use(this.#jwtMiddleware.validarToken);

        router.get("/", asyncHandler(this.#eventoControle.index));

        // Ordem: id → Controle; id inválido para aqui com 400.
        router.get(
            "/:id",
            this.#parametroMiddleware.validarId,
            asyncHandler(this.#eventoControle.show)
        );

        // Ordem: corpo → Controle (no POST exige titulo, dataEvento e idOrganizador).
        router.post(
            "/",
            this.#eventoMiddleware.validarBody,
            asyncHandler(this.#eventoControle.store)
        );

        // Ordem: id → corpo → Controle; no PUT o corpo é parcial.
        router.put(
            "/:id",
            this.#parametroMiddleware.validarId,
            this.#eventoMiddleware.validarBody,
            asyncHandler(this.#eventoControle.update)
        );

        // Ordem: id → Controle (o 409 de "tem lotes" vem do Service).
        router.delete(
            "/:id",
            this.#parametroMiddleware.validarId,
            asyncHandler(this.#eventoControle.destroy)
        );

        return router;
    }
};
