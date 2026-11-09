const mysql = require("mysql2/promise");
const config = require("../config/config");

/**
 * Camada de acesso ao MySQL (aula paw04x01 - conexão com o banco).
 * Singleton: a conexão é criada uma única vez e reutilizada por todos os DAOs
 * (projeto de aula, sem pool de conexões).
 * É o único arquivo que conhece o driver mysql2: os DAOs só montam o SQL.
 * Não tem regra de negócio nem mensagem para o usuário final — erro do driver
 * sobe para o Service e cai no tratador global.
 */
module.exports = class Banco {
    static #conexao = null;

    /** Devolve a conexão única, criando-a na primeira chamada (padrão Singleton). */
    static async getConexao() {
        if (Banco.#conexao === null) {
            Banco.#conexao = await mysql.createConnection({
                host: config.database.host,
                port: config.database.port,
                user: config.database.user,
                password: config.database.password,
                database: config.database.database,
                // dateStrings evita o driver converter DATETIME em Date/UTC: a data
                // sai como texto, exatamente como está gravada no banco.
                dateStrings: true
            });
        }
        return Banco.#conexao;
    }

    /**
     * Executa um SELECT.
     * @param {string} SQL comando com "?" no lugar dos valores
     * @param {Array} parametros valores que substituem os "?"
     * @returns {Promise<Array>} array de linhas (vazio quando não acha nada)
     */
    static async consultar(SQL, parametros = []) {
        // execute() prepara o comando e trata os "?": nada de concatenar valor no SQL.
        const conexao = await Banco.getConexao();
        const [linhas] = await conexao.execute(SQL, parametros);
        return linhas;
    }

    /**
     * Executa INSERT, UPDATE ou DELETE.
     * @returns {Promise<object>} { insertId, affectedRows } — insertId só no INSERT
     */
    static async executar(SQL, parametros = []) {
        const conexao = await Banco.getConexao();
        const [resultado] = await conexao.execute(SQL, parametros);
        return resultado;
    }

    /** Usado na subida da API e no GET /health: só falha se o banco não responder. */
    static async testarConexao() {
        await Banco.consultar("SELECT 1");
    }
};
