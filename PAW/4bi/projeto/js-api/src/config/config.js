/**
 * Configuração central da aplicação.
 * Os valores podem ser sobrescritos por variáveis de ambiente (sem alterar o código).
 */
module.exports = {
    porta: Number(process.env.PORT || 3000),
    api: {
        nome: "Pulse Eventos API (Node.js)",
        versao: "1.0.0",
        prefixo: "/api/v1"
    },
    database: {
        host: process.env.DB_HOST || "127.0.0.1",
        port: Number(process.env.DB_PORT || 3306),
        user: process.env.DB_USER || "root",
        password: process.env.DB_PASSWORD || "",
        database: process.env.DB_DATABASE || "paw4bi_eventos",
        connectionLimit: 10
    },
    jwt: {
        // A mesma chave é usada na API Flask: assim o mesmo token vale nas duas APIs.
        chave: process.env.JWT_KEY || "x9S4q0v+V0IjvHkG20uAxaHx1ijj+q1HWjHKv+ohxp/oK+77qyXkVj/l4QYHHTF3",
        alg: "HS256",
        tipo: "JWT",
        iss: "http://localhost",
        aud: "http://localhost",
        sub: "acesso_sistema",
        duracaoSegundos: 60 * 60 * 24 * 30
    }
};
