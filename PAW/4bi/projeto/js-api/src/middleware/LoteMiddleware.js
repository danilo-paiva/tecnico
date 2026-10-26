const { responder } = require("../utils/resposta");

const CAMPOS_OBRIGATORIOS = {
    POST: ["nome", "quantidadeTotal", "idEvento"]
};

/**
 * Middleware de validação do corpo de Lote.
 */
module.exports = class LoteMiddleware {
    validarBody = (request, response, next) => {
        const dados = request.body?.lote;

        if (!dados || typeof dados !== "object" || Array.isArray(dados)) {
            return responder(response, 400, "Erro na validação de dados", {
                mensagem: "O campo 'lote' é obrigatório no corpo da requisição."
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
};
