const { responder } = require("../utils/resposta");

// Campos que o POST exige; o PUT aceita corpo parcial (o Service completa o resto).
const CAMPOS_OBRIGATORIOS_POST = ["titulo", "dataEvento", "idOrganizador"];

/**
 * Middleware de validação do corpo de Evento.
 * Confere só a forma (objeto "evento" e campos obrigatórios do POST); as regras
 * de data e de título repetido ficam no EventoService.
 */
module.exports = class EventoMiddleware {
    /** Exige request.body.evento; no POST também titulo, dataEvento e idOrganizador. @returns 400 */
    validarBody = (request, response, next) => {
        const dados = request.body && request.body.evento;

        if (!dados || typeof dados !== "object") {
            return responder(response, 400, "Erro na validação de dados", {
                mensagem: "O campo 'evento' é obrigatório no corpo da requisição."
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
};
