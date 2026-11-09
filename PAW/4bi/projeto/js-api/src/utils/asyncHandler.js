/**
 * Envolve controllers assíncronos para que qualquer exceção/rejeição
 * chegue ao tratador global de erros do Express.
 * Por quê: sem isso, um await que falha dentro de um controller async não é
 * capturado pelo Express e a requisição fica sem resposta (o cliente trava).
 * Uso: asyncHandler(this.#controle.store) — em todos os roteadores.
 */
// Promise.resolve(...).catch(next) desvia o erro para o next() → tratador global.
const asyncHandler = (fn) => (request, response, next) =>
    Promise.resolve(fn(request, response, next)).catch(next);

module.exports = asyncHandler;
