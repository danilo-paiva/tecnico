const express = require("express");
const asyncHandler = require("../utils/asyncHandler");

/**
 * Roteador de Organizador: todas as rotas exigem JWT;
 * a exclusão exige também o perfil administrador.
 * Ordem em cada rota: token (router.use) → id da URL → corpo → Controle.
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

    /** Devolve o Router, que o Servidor registra no prefixo /organizadores. */
    criarRotas() {
        const router = express.Router();

        // router.use vale para TODAS as rotas abaixo: o token é a primeira barreira.
        router.use(this.#jwtMiddleware.validarToken);

        // Listagem liberada para qualquer usuário autenticado.
        router.get("/", asyncHandler(this.#organizadorControle.index));

        // Ordem: id inteiro → Controle; id inválido para aqui com 400.
        router.get(
            "/:id",
            this.#parametroMiddleware.validarId,
            asyncHandler(this.#organizadorControle.show)
        );

        // Ordem: corpo → Controle. Sem adminMiddleware: o Service também checa o perfil (403).
        router.post(
            "/",
            this.#organizadorMiddleware.validarBody,
            asyncHandler(this.#organizadorControle.store)
        );

        // Ordem: id → corpo → Controle. Corpo parcial: campo ausente mantém o valor atual.
        router.put(
            "/:id",
            this.#parametroMiddleware.validarId,
            this.#organizadorMiddleware.validarBody,
            asyncHandler(this.#organizadorControle.update)
        );

        // Ordem: id → administrador → Controle (o perfil é checado antes de qualquer SQL).
        router.delete(
            "/:id",
            this.#parametroMiddleware.validarId,
            this.#adminMiddleware.validarAdministrador,
            asyncHandler(this.#organizadorControle.destroy)
        );

        return router;
    }
};
