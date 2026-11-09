/**
 * Configuração central da aplicação (aula paw04x01).
 * Valores fixos no código, como nos exemplos da aula.
 */
module.exports = {
    porta: 3000,
    api: {
        nome: "Pulse Eventos API (Node.js)",
        versao: "1.0.0",
        prefixo: "/api/v1"
    },
    database: {
        host: "127.0.0.1",
        port: 3306,
        user: "root",
        password: "",
        database: "paw4bi_eventos"
    },
    jwt: {
        // A mesma chave é usada na API Flask: assim o mesmo token vale nas duas APIs.
        chave: "x9S4q0v+V0IjvHkG20uAxaHx1ijj+q1HWjHKv+ohxp/oK+77qyXkVj/l4QYHHTF3",
        alg: "HS256",
        tipo: "JWT",
        iss: "http://localhost",
        aud: "http://localhost",
        sub: "acesso_sistema",
        duracaoSegundos: 60 * 60 * 24 * 30
    }
};
