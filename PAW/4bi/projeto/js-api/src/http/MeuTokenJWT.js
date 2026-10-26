const jwt = require("jsonwebtoken");
const crypto = require("crypto");
const config = require("../config/config");

/**
 * Geração e validação de tokens JWT (aula paw04x01 / paw03x01).
 * A mesma chave, emissor e audiência são usados na API Flask.
 */
module.exports = class MeuTokenJWT {
    #key;
    #alg;
    #type;
    #iss;
    #aud;
    #sub;
    #duracaoToken;
    #payload;

    constructor() {
        this.#key = config.jwt.chave;
        this.#alg = config.jwt.alg;
        this.#type = config.jwt.tipo;
        this.#iss = config.jwt.iss;
        this.#aud = config.jwt.aud;
        this.#sub = config.jwt.sub;
        this.#duracaoToken = config.jwt.duracaoSegundos;
        this.#payload = null;
    }

    /**
     * @param {object} claims { idOrganizador, nome, email, perfil }
     * @returns {string} token assinado
     */
    gerarToken(claims) {
        const agora = Math.floor(Date.now() / 1000);
        const payload = {
            iss: this.#iss,
            aud: this.#aud,
            sub: this.#sub,
            iat: agora,
            exp: agora + this.#duracaoToken,
            nbf: agora,
            jti: crypto.randomBytes(16).toString("hex"),
            idOrganizador: claims.idOrganizador,
            nome: claims.nome,
            email: claims.email,
            perfil: claims.perfil
        };

        return jwt.sign(payload, this.#key, {
            algorithm: this.#alg,
            header: { alg: this.#alg, typ: this.#type }
        });
    }

    /**
     * @param {string} stringToken header Authorization (com ou sem "Bearer ")
     * @returns {boolean}
     */
    validarToken(stringToken) {
        if (!stringToken || String(stringToken).trim() === "") {
            return false;
        }

        const token = String(stringToken).replace("Bearer ", "").trim();

        try {
            this.#payload = jwt.verify(token, this.#key, {
                algorithms: [this.#alg],
                audience: this.#aud,
                issuer: this.#iss
            });
            return true;
        } catch (erro) {
            this.#payload = null;
            return false;
        }
    }

    get payload() {
        return this.#payload;
    }
};
