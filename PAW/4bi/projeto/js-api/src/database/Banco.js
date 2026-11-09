const mysql = require("mysql2/promise");
const config = require("../config/config");

/**
 * Camada de acesso ao MySQL (aula paw04x01 - conexão com o banco).
 * Singleton: a conexão é criada uma única vez e reutilizada por todos os DAOs.
 */
module.exports = class Banco {
    static #conexao = null;

    static async getConexao() {
        if (Banco.#conexao === null) {
            Banco.#conexao = await mysql.createConnection({
                host: config.database.host,
                port: config.database.port,
                user: config.database.user,
                password: config.database.password,
                database: config.database.database,
                dateStrings: true
            });
        }
        return Banco.#conexao;
    }

    /** SELECT -> array de linhas */
    static async consultar(SQL, parametros = []) {
        const conexao = await Banco.getConexao();
        const [linhas] = await conexao.execute(SQL, parametros);
        return linhas;
    }

    /** INSERT / UPDATE / DELETE -> { insertId, affectedRows } */
    static async executar(SQL, parametros = []) {
        const conexao = await Banco.getConexao();
        const [resultado] = await conexao.execute(SQL, parametros);
        return resultado;
    }

    static async testarConexao() {
        await Banco.consultar("SELECT 1");
    }
};
