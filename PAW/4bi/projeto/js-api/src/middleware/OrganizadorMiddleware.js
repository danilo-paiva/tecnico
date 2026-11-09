const { responder } = require("../utils/resposta");

// Campos que o POST exige; o PUT aceita corpo parcial (o Service completa o resto).
const CAMPOS_OBRIGATORIOS_POST = ["nome", "email", "senha"];
const CAMPOS_OBRIGATORIOS_LOGIN = ["email", "senha"];

/**
 * Middleware de validação do corpo de Organizador.
 * Confere a forma: existe o objeto "organizador" e, no POST, os campos
 * obrigatórios. Formato e tamanho de cada campo são validados nos setters do
 * Model (que respondem 400 pelo DomainError).
 */
module.exports = class OrganizadorMiddleware {
    /** Exige request.body.organizador; no POST também nome, email e senha. @returns 400 */
    validarBody = (request, response, next) => {
        const dados = request.body && request.body.organizador;

        if (!dados || typeof dados !== "object") {
            return responder(response, 400, "Erro na validação de dados", {
                mensagem: "O campo 'organizador' é obrigatório no corpo da requisição."
            });
        }

        // Só o POST exige todos os campos: no PUT o corpo é parcial.
        if (request.method === "POST") {
            for (let i = 0; i < CAMPOS_OBRIGATORIOS_POST.length; i++) {
                const campo = CAMPOS_OBRIGATORIOS_POST[i];
                if (dados[campo] === undefined || dados[campo] === null || dados[campo] === "") {
                    return responder(response, 400, "Erro na validação de dados", {
                        campo: campo,
                        mensagem: `O campo '${campo}' é obrigatório.`
                    });
                }
            }
        }

        return next();
    };

    /** Exige request.body.organizador com email e senha. @returns 400 */
    validarLoginBody = (request, response, next) => {
        const dados = request.body && request.body.organizador;

        if (!dados || typeof dados !== "object") {
            return responder(response, 400, "Erro na validação de dados", {
                mensagem: "Envie o objeto 'organizador' com email e senha."
            });
        }

        // Login não confere formato: quem valida e-mail e senha é o Service (401).
        for (let i = 0; i < CAMPOS_OBRIGATORIOS_LOGIN.length; i++) {
            const campo = CAMPOS_OBRIGATORIOS_LOGIN[i];
            if (!dados[campo]) {
                return responder(response, 400, "Erro na validação de dados", {
                    campo: campo,
                    mensagem: `O campo '${campo}' é obrigatório.`
                });
            }
        }

        return next();
    };
};
