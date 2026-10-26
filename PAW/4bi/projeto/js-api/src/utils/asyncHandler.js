/**
 * Envolve controllers assíncronos para que qualquer exceção/rejeição
 * chegue ao tratador global de erros do Express.
 */
const asyncHandler = (fn) => (request, response, next) =>
    Promise.resolve(fn(request, response, next)).catch(next);

module.exports = asyncHandler;
