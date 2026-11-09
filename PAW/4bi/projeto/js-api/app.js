const Servidor = require("./Servidor");

/**
 * Ponto de entrada da API JavaScript.
 * Fica fora das camadas do MVCS: aqui só existe o start da aplicação.
 * Quem monta injeção de dependência (DAO → Service → Controle) e as rotas é o
 * Servidor, então este arquivo não conhece nenhuma classe de negócio.
 * Execute com: npm start   (ou node app.js)
 */
const servidor = new Servidor();

// iniciar() testa o banco e sobe o Express na porta definida em src/config/config.js.
servidor.iniciar();
