const DomainError = require("../http/DomainError");

/**
 * Utilitários de validação usados pelos setters dos Models.
 * Cada função lança DomainError com uma mensagem pronta para o usuário.
 * Segue o padrão da aula: validação simples, com if e mensagem.
 */

const texto = (valor, campo, min, max, obrigatorio) => {
    if (min === undefined) min = 1;
    if (max === undefined) max = 255;
    if (obrigatorio === undefined) obrigatorio = true;

    if (valor === undefined || valor === null || valor === "") {
        if (!obrigatorio) return null;
        throw new DomainError(campo + " é obrigatório.");
    }
    if (typeof valor !== "string") {
        throw new DomainError(campo + " deve ser um texto.");
    }

    const limpo = valor.trim();

    if (limpo.length < min) {
        throw new DomainError(campo + " deve ter pelo menos " + min + " caracteres.");
    }
    if (limpo.length > max) {
        throw new DomainError(campo + " deve ter no máximo " + max + " caracteres.");
    }

    return limpo;
};

const inteiro = (valor, campo, min, max) => {
    if (min === undefined) min = 0;
    if (max === undefined) max = 999999999;

    if (valor === undefined || valor === null || valor === "") {
        throw new DomainError(campo + " é obrigatório.");
    }

    const numero = Number(valor);

    if (!Number.isInteger(numero)) {
        throw new DomainError(campo + " deve ser um número inteiro.");
    }
    if (numero < min) {
        throw new DomainError(campo + " deve ser maior ou igual a " + min + ".");
    }
    if (numero > max) {
        throw new DomainError(campo + " deve ser menor ou igual a " + max + ".");
    }

    return numero;
};

const decimal = (valor, campo, min, max) => {
    if (min === undefined) min = 0;
    if (max === undefined) max = 99999999;

    if (valor === undefined || valor === null || valor === "") {
        throw new DomainError(campo + " é obrigatório.");
    }

    const numero = Number(valor);

    if (isNaN(numero)) {
        throw new DomainError(campo + " deve ser um número.");
    }
    if (numero < min) {
        throw new DomainError(campo + " deve ser maior ou igual a " + min + ".");
    }
    if (numero > max) {
        throw new DomainError(campo + " deve ser menor ou igual a " + max + ".");
    }

    return Math.round(numero * 100) / 100;
};

const email = (valor) => {
    const limpo = texto(valor, "email", 5, 150).toLowerCase();

    if (limpo.indexOf("@") < 1 || limpo.indexOf(".") < 3 || limpo.indexOf(" ") !== -1) {
        throw new DomainError("email deve ser um endereço válido.");
    }

    return limpo;
};

const telefone = (valor) => {
    if (valor === undefined || valor === null || String(valor).trim() === "") {
        return null;
    }

    const limpo = String(valor).trim();

    if (limpo.length < 8 || limpo.length > 20) {
        throw new DomainError("telefone deve ter entre 8 e 20 caracteres.");
    }

    return limpo;
};

const dataHora = (valor, campo) => {
    if (!valor) {
        throw new DomainError(campo + " é obrigatório.");
    }

    const data = new Date(String(valor).replace(" ", "T"));

    if (isNaN(data.getTime())) {
        throw new DomainError(campo + " deve ser uma data/hora válida.");
    }

    return data;
};

/** Completa com zero à esquerda (equivale ao padStart da aula de front). */
const doisDigitos = (n) => {
    return n < 10 ? "0" + n : String(n);
};

/** Converte Date para o formato aceito pelo MySQL: YYYY-MM-DD HH:mm:ss */
const paraSql = (data) => {
    return data.getFullYear() + "-" + doisDigitos(data.getMonth() + 1) + "-" + doisDigitos(data.getDate()) +
        " " + doisDigitos(data.getHours()) + ":" + doisDigitos(data.getMinutes()) + ":" + doisDigitos(data.getSeconds());
};

module.exports = { texto, inteiro, decimal, email, telefone, dataHora, paraSql };
