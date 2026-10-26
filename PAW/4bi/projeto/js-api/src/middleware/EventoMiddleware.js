const { responder } = require("../utils/resposta");

const CAMPOS_OBRIGATORIOS = {
    POST: ["titulo", "dataEvento", "idOrganizador"]
};

/**
 * Middleware de validação do corpo de Evento.
 */
module.exports = class EventoMiddleware {
    validarBody = (request, response, next) => {
        const dados = request.body?.evento;

        if (!dados || typeof dados !== "object" || Array.isArray(dados)) {
            return responder(response, 400, "Erro na validação de dados", {
                mensagem: "O campo 'evento' é obrigatório no corpo da requisição."
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
