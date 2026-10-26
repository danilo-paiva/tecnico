const DomainError = require("../http/DomainError");

/**
 * Utilidades de validação usadas pelos setters dos Models.
 * Cada função lança DomainError com uma mensagem pronta para o usuário.
 */

const texto = (valor, campo, { min = 1, max = 255, obrigatorio = true } = {}) => {
    if (valor === undefined || valor === null || valor === "") {
        if (!obrigatorio) return null;
        throw new DomainError(`${campo} é obrigatório.`);
    }
    if (typeof valor !== "string") {
        throw new DomainError(`${campo} deve ser um texto.`);
    }
    const limpo = valor.trim();
    if (limpo.length < min) {
        throw new DomainError(`${campo} deve ter pelo menos ${min} caracteres.`);
    }
    if (limpo.length > max) {
        throw new DomainError(`${campo} deve ter no máximo ${max} caracteres.`);
    }
    return limpo;
};

const inteiro = (valor, campo, { min = 0, max = Number.MAX_SAFE_INTEGER } = {}) => {
    if (valor === undefined || valor === null || valor === "") {
        throw new DomainError(`${campo} é obrigatório.`);
    }
    const numero = Number(valor);
    if (!Number.isInteger(numero)) {
        throw new DomainError(`${campo} deve ser um número inteiro.`);
    }
    if (numero < min) {
        throw new DomainError(`${campo} deve ser maior ou igual a ${min}.`);
    }
    if (numero > max) {
        throw new DomainError(`${campo} deve ser menor ou igual a ${max}.`);
    }
    return numero;
};

const decimal = (valor, campo, { min = 0, max = 99999999 } = {}) => {
    if (valor === undefined || valor === null || valor === "") {
        throw new DomainError(`${campo} é obrigatório.`);
    }
    const numero = Number(valor);
    if (!Number.isFinite(numero)) {
        throw new DomainError(`${campo} deve ser um número.`);
    }
    if (numero < min) {
        throw new DomainError(`${campo} deve ser maior ou igual a ${min}.`);
    }
    if (numero > max) {
        throw new DomainError(`${campo} deve ser menor ou igual a ${max}.`);
    }
    return Math.round(numero * 100) / 100;
};

const email = (valor) => {
    const limpo = texto(valor, "email", { min: 5, max: 150 }).toLowerCase();
    if (!/^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i.test(limpo)) {
        throw new DomainError("email deve ser um endereço válido.");
    }
    return limpo;
};

const telefone = (valor) => {
    if (valor === undefined || valor === null || String(valor).trim() === "") return null;
    const limpo = String(valor).trim();
    if (limpo.length < 8 || limpo.length > 20) {
        throw new DomainError("telefone deve ter entre 8 e 20 caracteres.");
    }
    return limpo;
};

const dataHora = (valor, campo) => {
    if (!valor) throw new DomainError(`${campo} é obrigatório.`);
    const data = new Date(String(valor).replace(" ", "T"));
    if (Number.isNaN(data.getTime())) {
        throw new DomainError(`${campo} deve ser uma data/hora válida.`);
    }
    return data;
};

/** Converte Date para o formato aceito pelo MySQL: YYYY-MM-DD HH:mm:ss */
const paraSql = (data) => {
    const p = (n) => String(n).padStart(2, "0");
    return `${data.getFullYear()}-${p(data.getMonth() + 1)}-${p(data.getDate())} ` +
        `${p(data.getHours())}:${p(data.getMinutes())}:${p(data.getSeconds())}`;
};

module.exports = { texto, inteiro, decimal, email, telefone, dataHora, paraSql };
