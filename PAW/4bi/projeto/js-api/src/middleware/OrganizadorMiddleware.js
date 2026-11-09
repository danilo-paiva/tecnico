const { responder } = require("../utils/resposta");

const CAMPOS_OBRIGATORIOS_POST = ["nome", "email", "senha"];
const CAMPOS_OBRIGATORIOS_LOGIN = ["email", "senha"];

/**
 * Middleware de validação do corpo de Organizador.
 */
module.exports = class OrganizadorMiddleware {
    validarBody = (request, response, next) => {
        const dados = request.body && request.body.organizador;

        if (!dados || typeof dados !== "object") {
            return responder(response, 400, "Erro na validação de dados", {
                mensagem: "O campo 'organizador' é obrigatório no corpo da requisição."
            });
        }

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

    validarLoginBody = (request, response, next) => {
        const dados = request.body && request.body.organizador;

        if (!dados || typeof dados !== "object") {
            return responder(response, 400, "Erro na validação de dados", {
                mensagem: "Envie o objeto 'organizador' com email e senha."
            });
        }

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
