const express = require("express");
const path = require("path");

const config = require("./src/config/config");
const Banco = require("./src/database/Banco");
const MeuTokenJWT = require("./src/http/MeuTokenJWT");
const ErrorResponse = require("./src/http/ErrorResponse");
const DomainError = require("./src/http/DomainError");
const { responder } = require("./src/utils/resposta");

const OrganizadorDAO = require("./src/dao/OrganizadorDAO");
const EventoDAO = require("./src/dao/EventoDAO");
const LoteDAO = require("./src/dao/LoteDAO");

const OrganizadorService = require("./src/servico/OrganizadorService");
const EventoService = require("./src/servico/EventoService");
const LoteService = require("./src/servico/LoteService");

const AuthControle = require("./src/controle/AuthControle");
const OrganizadorControle = require("./src/controle/OrganizadorControle");
const EventoControle = require("./src/controle/EventoControle");
const LoteControle = require("./src/controle/LoteControle");

const JwtMiddleware = require("./src/middleware/JwtMiddleware");
const AdminMiddleware = require("./src/middleware/AdminMiddleware");
const ParametroMiddleware = require("./src/middleware/ParametroMiddleware");
const OrganizadorMiddleware = require("./src/middleware/OrganizadorMiddleware");
const EventoMiddleware = require("./src/middleware/EventoMiddleware");
const LoteMiddleware = require("./src/middleware/LoteMiddleware");

const AuthRoteador = require("./src/roteador/AuthRoteador");
const OrganizadorRoteador = require("./src/roteador/OrganizadorRoteador");
const EventoRoteador = require("./src/roteador/EventoRoteador");
const LoteRoteador = require("./src/roteador/LoteRoteador");

/**
 * Servidor da API JavaScript.
 * Camada de infraestrutura: é o ponto onde a injeção de dependência acontece.
 * Responsável por criar DAO → Service → Controle, injetar cada dependência no
 * construtor de quem precisa e registrar os roteadores no pipeline do Express.
 * Ordem de uma requisição: middleware global → roteador da entidade →
 * middleware da rota → Controle → Service → DAO → MySQL.
 * Não tem regra de negócio: quem decide 400/403/404/409 são os Services.
 */
module.exports = class Servidor {
    #porta;
    #app;

    /** Lê a porta do config, cria o Express e monta as três etapas abaixo. */
    constructor() {
        this.#porta = config.porta;
        this.#app = express();

        this.#configurarMiddlewaresGlobais();
        this.#configurarRotas();
        this.#configurarTratamentoDeErros();
    }

    /** Middlewares globais: valem para todas as rotas e rodam antes dos roteadores. */
    #configurarMiddlewaresGlobais() {
        // Sem isso request.body chegaria undefined: é o que converte o JSON recebido.
        this.#app.use(express.json());
        // CORS antes das rotas: responde o preflight (OPTIONS) de qualquer origem.
        this.#app.use(this.#cors);

        // O frontend é servido pela própria API (mesma origem).
        this.#app.use(express.static(path.join(__dirname, "..", "frontend")));
    }

    /** Libera qualquer origem e encerra o preflight OPTIONS com 204 (sem corpo). */
    #cors = (request, response, next) => {
        response.header("Access-Control-Allow-Origin", "*");
        response.header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
        response.header("Access-Control-Allow-Headers", "Content-Type, Authorization");
        // O navegador manda OPTIONS antes de um PUT/DELETE real; 204 = "pode seguir".
        if (request.method === "OPTIONS") {
            return response.sendStatus(204);
        }
        return next();
    };

    /**
     * Cria DAOs, Services, Controles, middlewares e roteadores, uma única vez, na
     * subida da API, e registra cada roteador no seu prefixo.
     */
    #configurarRotas() {
        const prefixo = config.api.prefixo;

        // ---------- Injeção de dependência ----------
        // A ordem importa: o DAO não conhece ninguém, o Service recebe DAOs e o
        // Controle recebe o Service — cada camada só enxerga a de baixo (DI).
        const organizadorDAO = new OrganizadorDAO();
        const eventoDAO = new EventoDAO();
        const loteDAO = new LoteDAO();

        const tokenJWT = new MeuTokenJWT();

        // OrganizadorService também recebe EventoDAO: conta eventos antes de excluir;
        // e recebe o MeuTokenJWT: gera o token no login.
        const organizadorService = new OrganizadorService(organizadorDAO, eventoDAO, tokenJWT);
        const eventoService = new EventoService(eventoDAO, organizadorDAO, loteDAO);
        const loteService = new LoteService(loteDAO, eventoDAO);

        const authControle = new AuthControle(organizadorService);
        const organizadorControle = new OrganizadorControle(organizadorService);
        const eventoControle = new EventoControle(eventoService);
        const loteControle = new LoteControle(loteService);

        const jwtMiddleware = new JwtMiddleware();
        const adminMiddleware = new AdminMiddleware();
        const parametroMiddleware = new ParametroMiddleware();
        const organizadorMiddleware = new OrganizadorMiddleware();
        const eventoMiddleware = new EventoMiddleware();
        const loteMiddleware = new LoteMiddleware();

        // ---------- Rotas ----------
        // Rota simples, sem Service: serve para saber se a API e o banco respondem.
        this.#app.get(`${prefixo}/health`, async (request, response) => {
            try {
                await Banco.testarConexao();
                return responder(response, 200, "API online", {
                    api: config.api.nome,
                    versao: config.api.versao,
                    banco: config.database.database,
                    hora: new Date().toISOString()
                });
            } catch (erro) {
                return responder(response, 503, "API online, banco indisponível", {
                    api: config.api.nome,
                    erro: erro.message
                });
            }
        });
        // Cada roteador recebe os middlewares prontos e define a ordem interna deles.
        const authRoteador = new AuthRoteador(jwtMiddleware, organizadorMiddleware, authControle);
        this.#app.use(`${prefixo}/auth`, authRoteador.criarRotas());

        const organizadorRoteador = new OrganizadorRoteador(
            jwtMiddleware,
            adminMiddleware,
            parametroMiddleware,
            organizadorMiddleware,
            organizadorControle
        );
        this.#app.use(`${prefixo}/organizadores`, organizadorRoteador.criarRotas());

        const eventoRoteador = new EventoRoteador(
            jwtMiddleware,
            parametroMiddleware,
            eventoMiddleware,
            eventoControle
        );
        this.#app.use(`${prefixo}/eventos`, eventoRoteador.criarRotas());

        const loteRoteador = new LoteRoteador(
            jwtMiddleware,
            parametroMiddleware,
            loteMiddleware,
            loteControle
        );
        this.#app.use(`${prefixo}/lotes`, loteRoteador.criarRotas());
    }

    /**
     * Registra o 404 da API e o tratador global de erros — por isso este método
     * roda por último: no Express, a ordem de registro é a ordem de execução.
     */
    #configurarTratamentoDeErros() {
        // Rota de API não encontrada
        this.#app.use(`${config.api.prefixo}`, (request, response) => {
            return responder(response, 404, "Rota não encontrada", {
                mensagem: `${request.method} ${request.originalUrl} não existe nesta API.`
            });
        });

        // Tratador global de erros
        // O Express só reconhece middleware de erro se ele tiver os 4 parâmetros
        // (erro, request, response, next) — mesmo que next não seja usado aqui.
        this.#app.use((erro, request, response, next) => {
            // Erro esperado da aplicação: usa o status que o Service/Middleware escolheu.
            if (erro instanceof ErrorResponse) {
                return responder(response, erro.status, erro.message, {
                    detalhes: erro.detalhes
                });
            }

            // Erro de domínio (setter do Model): entrada inválida do usuário → 400.
            if (erro instanceof DomainError) {
                return responder(response, 400, "Erro de validação", {
                    mensagem: erro.message
                });
            }

            console.error("Erro não tratado:", erro);
            return responder(response, 500, "Erro interno do servidor", {
                mensagem: "Consulte o terminal da API para mais detalhes."
            });
        });
    }

    /**
     * Testa o banco só para avisar no terminal e depois sobe o Express na porta
     * configurada. A API continua de pé mesmo sem MySQL — nesse caso o /health
     * responde 503, o que facilita o diagnóstico durante a apresentação.
     */
    async iniciar() {
        try {
            await Banco.testarConexao();
            console.log(`Banco de dados conectado: ${config.database.database}`);
        } catch (erro) {
            console.error("Atenção: não foi possível conectar ao MySQL:", erro.message);
            console.error("Confira se o MySQL está ligado e se docs/banco.sql foi importado.");
        }
        // O listen fica fora do try: banco fora do ar não impede a API de subir.
        this.#app.listen(this.#porta, () => {
            console.log(`${config.api.nome} rodando em http://localhost:${this.#porta}/`);
            console.log(`Frontend: http://localhost:${this.#porta}/login.html`);
        });
    }
};
