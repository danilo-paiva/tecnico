const { responder } = require("../utils/resposta");

const CAMPOS_OBRIGATORIOS = {
    POST: ["nome", "email", "senha"]
};

/**
 * Middleware de validação do corpo de Organizador.
 */
module.exports = class OrganizadorMiddleware {
    validarBody = (request, response, next) => {
        const dados = request.body?.organizador;

        if (!dados || typeof dados !== "object" || Array.isArray(dados)) {
            return responder(response, 400, "Erro na validação de dados", {
                mensagem: "O campo 'organizador' é obrigatório no corpo da requisição."
            });
        }

        if (request.method === "POST") {
            for (const campo of CAMPOS_OBRIGATORIOS.POST) {
                if (dados[campo] === undefined || dados[campo] === null || dados[campo] === "") {
                    return responder(response, 400, "Erro na validação de dados", {
                        campo,
                        mensagem: `O campo '${campo}' é obrigatório.`
                    });
                }
            }
        }

        return next();
    };

    validarLoginBody = (request, response, next) => {
        const dados = request.body?.organizador;
        if (!dados || typeof dados !== "object") {
            return responder(response, 400, "Erro na validação de dados", {
                mensagem: "Envie o objeto 'organizador' com email e senha."
            });
        }
        for (const campo of ["email", "senha"]) {
            if (!dados[campo]) {
                return responder(response, 400, "Erro na validação de dados", {
                    campo,
                    mensagem: `O campo '${campo}' é obrigatório.`
                });
            }
        }
        return next();
    };
};
