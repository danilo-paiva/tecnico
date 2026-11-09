const express = require("express");
const asyncHandler = require("../utils/asyncHandler");

/**
 * Roteador de Lote (todas as rotas exigem JWT).
 * Ordem em cada rota: token (router.use) → id da URL → corpo → Controle.
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

    /** Devolve o Router, que o Servidor registra no prefixo /lotes. */
    criarRotas() {
        const router = express.Router();

        // router.use vale para todas as rotas abaixo: sem token, nada executa.
        router.use(this.#jwtMiddleware.validarToken);

        router.get("/", asyncHandler(this.#loteControle.index));

        // Ordem: id → Controle; id inválido para aqui com 400.
        router.get(
            "/:id",
            this.#parametroMiddleware.validarId,
            asyncHandler(this.#loteControle.show)
        );

        // Ordem: corpo → Controle (no POST exige nome, quantidadeTotal e idEvento).
        router.post(
            "/",
            this.#loteMiddleware.validarBody,
            asyncHandler(this.#loteControle.store)
        );

        // Ordem: id → corpo → Controle; no PUT o corpo é parcial.
        router.put(
            "/:id",
            this.#parametroMiddleware.validarId,
            this.#loteMiddleware.validarBody,
            asyncHandler(this.#loteControle.update)
        );

        // Ordem: id → Controle (o 404 de lote inexistente vem do Service).
        router.delete(
            "/:id",
            this.#parametroMiddleware.validarId,
            asyncHandler(this.#loteControle.destroy)
        );

        return router;
    }
};
