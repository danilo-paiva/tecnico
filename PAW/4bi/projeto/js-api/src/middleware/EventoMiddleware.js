const { responder } = require("../utils/resposta");

const CAMPOS_OBRIGATORIOS_POST = ["titulo", "dataEvento", "idOrganizador"];

/**
 * Middleware de validação do corpo de Evento.
 */
module.exports = class EventoMiddleware {
    validarBody = (request, response, next) => {
        const dados = request.body && request.body.evento;

        if (!dados || typeof dados !== "object") {
            return responder(response, 400, "Erro na validação de dados", {
                mensagem: "O campo 'evento' é obrigatório no corpo da requisição."
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
};
