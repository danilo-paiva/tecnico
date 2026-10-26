const mysql = require("mysql2/promise");
const config = require("../config/config");

/**
 * Camada de acesso ao MySQL (pool de conexões).
 * Singleton: o pool é criado uma única vez e reutilizado por todos os DAOs.
 */
module.exports = class Banco {
    static #pool = null;

    static getPool() {
        if (Banco.#pool === null) {
            Banco.#pool = mysql.createPool({
                host: config.database.host,
                port: config.database.port,
                user: config.database.user,
                password: config.database.password,
                database: config.database.database,
                waitForConnections: true,
                connectionLimit: config.database.connectionLimit,
                queueLimit: 0,
                dateStrings: true,
                charset: "utf8mb4"
            });
        }
        return Banco.#pool;
    }

    /** SELECT -> array de linhas */
    static async consultar(SQL, parametros = []) {
        const [linhas] = await Banco.getPool().execute(SQL, parametros);
        return linhas;
    }

    /** INSERT / UPDATE / DELETE -> { insertId, affectedRows } */
    static async executar(SQL, parametros = []) {
        const [resultado] = await Banco.getPool().execute(SQL, parametros);
        return resultado;
    }

    static async testarConexao() {
        const conexao = await Banco.getPool().getConnection();
        try {
            await conexao.query("SELECT 1");
        } finally {
            conexao.release();
        }
    }
};
