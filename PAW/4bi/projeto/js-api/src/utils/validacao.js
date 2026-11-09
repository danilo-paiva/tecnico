const DomainError = require("../http/DomainError");

/**
 * Utilitários de validação usados pelos setters dos Models.
 * Camada de apoio do Model: cada função normaliza o valor recebido e lança
 * DomainError com uma mensagem pronta para o usuário (o tratador global
 * converte em 400).
 * Segue o padrão da aula: validação simples, com if e mensagem.
 * Não acessa banco nem HTTP, e não sabe de duplicidade — isso é regra de
 * negócio, resolvida no Service com consulta ao DAO.
 */

/**
 * Valida e limpa um campo de texto.
 * @param {string} valor dado recebido no JSON
 * @param {string} campo nome que aparece na mensagem de erro
 * @param {number} min tamanho mínimo (padrão 1) e max tamanho máximo (padrão 255)
 * @param {boolean} obrigatorio se false, valor vazio devolve null em vez de erro
 * @returns {string|null} o texto já sem espaços nas pontas
 */
const texto = (valor, campo, min, max, obrigatorio) => {
    if (min === undefined) min = 1;
    if (max === undefined) max = 255;
    if (obrigatorio === undefined) obrigatorio = true;

    // Sem valor: só é erro se o campo for obrigatório.
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

/**
 * Converte e valida um número inteiro (aceita "10" vindo de JSON ou query string).
 * @returns {number} o inteiro já convertido
 */
const inteiro = (valor, campo, min, max) => {
    if (min === undefined) min = 0;
    if (max === undefined) max = 999999999;

    if (valor === undefined || valor === null || valor === "") {
        throw new DomainError(campo + " é obrigatório.");
    }

    // Number() antes do teste: ids e quantidades podem chegar como texto.
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

/** Valida um decimal (preço) e arredonda para 2 casas. */
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

    // O banco é DECIMAL(10,2): 19.999 vira 20.00 já na entrada.
    return Math.round(numero * 100) / 100;
};

/** Valida o formato do e-mail e normaliza para minúsculo. */
const email = (valor) => {
    // Minúsculo sempre: a checagem de e-mail único é case-insensitive.
    const limpo = texto(valor, "email", 5, 150).toLowerCase();

    // Checagem simples, como na aula: tem "@" e "." em posição válida e sem espaço.
    if (limpo.indexOf("@") < 1 || limpo.indexOf(".") < 3 || limpo.indexOf(" ") !== -1) {
        throw new DomainError("email deve ser um endereço válido.");
    }

    return limpo;
};

/** Telefone é opcional: vazio vira null; se vier preenchido, confere o tamanho. */
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

/**
 * Converte texto de data em Date; aceita "2025-11-20 19:30:00" e ISO 8601.
 * @returns {Date} data já validada (se pode ser passado é o Service quem decide)
 */
const dataHora = (valor, campo) => {
    if (!valor) {
        throw new DomainError(campo + " é obrigatório.");
    }

    // O Date do JS entende ISO ("T" no meio), não o formato do MySQL (com espaço).
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

/**
 * Converte Date para o formato aceito pelo MySQL: YYYY-MM-DD HH:mm:ss
 * Não usa toISOString porque ele converte para UTC e mudaria a hora gravada.
 */
const paraSql = (data) => {
    return data.getFullYear() + "-" + doisDigitos(data.getMonth() + 1) + "-" + doisDigitos(data.getDate()) +
        " " + doisDigitos(data.getHours()) + ":" + doisDigitos(data.getMinutes()) + ":" + doisDigitos(data.getSeconds());
};

module.exports = { texto, inteiro, decimal, email, telefone, dataHora, paraSql };
