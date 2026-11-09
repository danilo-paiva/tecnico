const jwt = require("jsonwebtoken");
const crypto = require("crypto");
const config = require("../config/config");

/**
 * Geração e validação de tokens JWT (aula paw04x01 / paw03x01).
 * Infraestrutura HTTP: o OrganizadorService usa no login para emitir o token e
 * o JwtMiddleware usa para validar o cabeçalho Authorization das requisições.
 * A mesma chave, emissor e audiência são usados na API Flask: por isso o token
 * emitido aqui também vale lá (e vice-versa).
 * Não acessa banco, não guarda senha e não decide permissões — quem autoriza
 * por perfil é o AdminMiddleware.
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
     * Monta as claims e assina o token com HS256.
     * @param {object} claims { idOrganizador, nome, email, perfil }
     * @returns {string} token assinado, enviado no Authorization das próximas chamadas
     */
    gerarToken(claims) {
        const agora = Math.floor(Date.now() / 1000);
        // iat/nbf = emitido e válido a partir de agora; exp = agora + validade do config.
        // jti é só um identificador único do token (dois logins nunca geram o mesmo).
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
     * Valida a assinatura e as claims fixas (iss, aud, exp) do token recebido.
     * @param {string} stringToken header Authorization (com ou sem "Bearer ")
     * @returns {boolean} true se o token vale; false em qualquer falha
     *          (o 401 é montado por quem chamou, no JwtMiddleware)
     */
    validarToken(stringToken) {
        // Sem cabeçalho não há token: devolve false e o middleware responde 401.
        if (!stringToken || String(stringToken).trim() === "") {
            return false;
        }

        // Aceita tanto o header cru quanto o formato "Bearer <token>".
        const token = String(stringToken).replace("Bearer ", "").trim();

        try {
            // verify confere assinatura e exp; algorithms fixo impede que alguém
            // troque o algoritmo do token (ex.: alg "none") para falsificar acesso.
            this.#payload = jwt.verify(token, this.#key, {
                algorithms: [this.#alg],
                audience: this.#aud,
                issuer: this.#iss
            });
            return true;
        } catch (erro) {
            this.#payload = null;
            // Token expirado, assinatura errada ou aud/iss diferente: tudo vira false.
            return false;
        }
    }

    /** Claims do último token validado (lido pelo JwtMiddleware). */
    get payload() {
        return this.#payload;
    }
};
