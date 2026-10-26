const express = require("express");
const asyncHandler = require("../utils/asyncHandler");

/**
 * Roteador de Evento (todas as rotas exigem JWT).
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

    criarRotas() {
        const router = express.Router();

        router.use(this.#jwtMiddleware.validarToken);

        router.get("/", asyncHandler(this.#eventoControle.index));

        router.get(
            "/:id",
            this.#parametroMiddleware.validarId,
            asyncHandler(this.#eventoControle.show)
        );

        router.post(
            "/",
            this.#eventoMiddleware.validarBody,
            asyncHandler(this.#eventoControle.store)
        );

        router.put(
            "/:id",
            this.#parametroMiddleware.validarId,
            this.#eventoMiddleware.validarBody,
            asyncHandler(this.#eventoControle.update)
        );

        router.delete(
            "/:id",
            this.#parametroMiddleware.validarId,
            asyncHandler(this.#eventoControle.destroy)
        );

        return router;
    }
};
