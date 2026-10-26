const express = require("express");
const asyncHandler = require("../utils/asyncHandler");

/**
 * Roteador de Organizador: todas as rotas exigem JWT;
 * a exclusão exige também o perfil administrador.
 */
module.exports = class OrganizadorRoteador {
    #jwtMiddleware;
    #adminMiddleware;
    #parametroMiddleware;
    #organizadorMiddleware;
    #organizadorControle;

    constructor(jwtMiddleware, adminMiddleware, parametroMiddleware, organizadorMiddleware, organizadorControle) {
        this.#jwtMiddleware = jwtMiddleware;
        this.#adminMiddleware = adminMiddleware;
        this.#parametroMiddleware = parametroMiddleware;
        this.#organizadorMiddleware = organizadorMiddleware;
        this.#organizadorControle = organizadorControle;
    }

    criarRotas() {
        const router = express.Router();

        router.use(this.#jwtMiddleware.validarToken);

        router.get("/", asyncHandler(this.#organizadorControle.index));

        router.get(
            "/:id",
            this.#parametroMiddleware.validarId,
            asyncHandler(this.#organizadorControle.show)
        );

        router.post(
            "/",
            this.#organizadorMiddleware.validarBody,
            asyncHandler(this.#organizadorControle.store)
        );

        router.put(
            "/:id",
            this.#parametroMiddleware.validarId,
            this.#organizadorMiddleware.validarBody,
            asyncHandler(this.#organizadorControle.update)
        );

        router.delete(
            "/:id",
            this.#parametroMiddleware.validarId,
            this.#adminMiddleware.validarAdministrador,
            asyncHandler(this.#organizadorControle.destroy)
        );

        return router;
    }
};
