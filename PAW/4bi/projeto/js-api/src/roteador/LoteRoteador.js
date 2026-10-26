const express = require("express");
const asyncHandler = require("../utils/asyncHandler");

/**
 * Roteador de Lote (todas as rotas exigem JWT).
 */
module.exports = class LoteRoteador {
    #jwtMiddleware;
    #parametroMiddleware;
    #loteMiddleware;
    #loteControle;

    constructor(jwtMiddleware, parametroMiddleware, loteMiddleware, loteControle) {
        this.#jwtMiddleware = jwtMiddleware;
        this.#parametroMiddleware = parametroMiddleware;
        this.#loteMiddleware = loteMiddleware;
        this.#loteControle = loteControle;
    }

    criarRotas() {
        const router = express.Router();

        router.use(this.#jwtMiddleware.validarToken);

        router.get("/", asyncHandler(this.#loteControle.index));

        router.get(
            "/:id",
            this.#parametroMiddleware.validarId,
            asyncHandler(this.#loteControle.show)
        );

        router.post(
            "/",
            this.#loteMiddleware.validarBody,
            asyncHandler(this.#loteControle.store)
        );

        router.put(
            "/:id",
            this.#parametroMiddleware.validarId,
            this.#loteMiddleware.validarBody,
            asyncHandler(this.#loteControle.update)
        );

        router.delete(
            "/:id",
            this.#parametroMiddleware.validarId,
            asyncHandler(this.#loteControle.destroy)
        );

        return router;
    }
};
