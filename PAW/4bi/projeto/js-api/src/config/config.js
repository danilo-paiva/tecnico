/**
 * Configuração central da aplicação (aula paw04x01).
 * Valores fixos no código, como nos exemplos da aula.
 * Consumido por: Servidor.js (porta, prefixo e nome), Banco.js (MySQL) e
 * MeuTokenJWT.js (chave, algoritmo e validade do token).
 * Nenhuma outra camada lê process.env: tudo passa por aqui.
 */
module.exports = {
    // Porta HTTP do Express: a API e o frontend sobem no mesmo endereço.
    porta: 3000,
    api: {
        nome: "Pulse Eventos API (Node.js)",
        versao: "1.0.0",
        prefixo: "/api/v1"
        // Prefixo versionado: toda rota nasce dele (/api/v1/organizadores, ...).
    },
    database: {
        // O mesmo banco criado por docs/banco.sql.
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
        // HS256 = assinatura simétrica: quem tem a chave valida o token.
        tipo: "JWT",
        // iss/aud/sub são gravados no token e conferidos na validação: um token
        // emitido por outra aplicação (outro iss/aud) é recusado.
        iss: "http://localhost",
        aud: "http://localhost",
        sub: "acesso_sistema",
        // 60 * 60 * 24 * 30 = 30 dias em segundos (validade do token).
        duracaoSegundos: 60 * 60 * 24 * 30
    }
};
