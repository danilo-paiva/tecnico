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
 * Responsável pela injeção de dependência entre as camadas (MVCS + DAO)
 * e pelo pipeline de middlewares do Express.
 */
module.exports = class Servidor {
    #porta;
    #app;

    constructor() {
        this.#porta = config.porta;
        this.#app = express();

        this.#configurarMiddlewaresGlobais();
        this.#configurarRotas();
        this.#configurarTratamentoDeErros();
    }

    #configurarMiddlewaresGlobais() {
        this.#app.use(express.json());
        this.#app.use(this.#cors);

        // O frontend é servido pela própria API (mesma origem).
        this.#app.use(express.static(path.join(__dirname, "..", "frontend")));
    }

    #cors = (request, response, next) => {
        response.header("Access-Control-Allow-Origin", "*");
        response.header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
        response.header("Access-Control-Allow-Headers", "Content-Type, Authorization");
        if (request.method === "OPTIONS") {
            return response.sendStatus(204);
        }
        return next();
    };

    #configurarRotas() {
        const prefixo = config.api.prefixo;

        // ---------- Injeção de dependência ----------
        const organizadorDAO = new OrganizadorDAO();
        const eventoDAO = new EventoDAO();
        const loteDAO = new LoteDAO();

        const tokenJWT = new MeuTokenJWT();

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

    #configurarTratamentoDeErros() {
        // Rota de API não encontrada
        this.#app.use(`${config.api.prefixo}`, (request, response) => {
            return responder(response, 404, "Rota não encontrada", {
                mensagem: `${request.method} ${request.originalUrl} não existe nesta API.`
            });
        });

        // Tratador global de erros
        this.#app.use((erro, request, response, next) => {
            if (erro instanceof ErrorResponse) {
                return responder(response, erro.status, erro.message, {
                    detalhes: erro.detalhes
                });
            }

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

    async iniciar() {
        try {
            await Banco.testarConexao();
            console.log(`Banco de dados conectado: ${config.database.database}`);
        } catch (erro) {
            console.error("Atenção: não foi possível conectar ao MySQL:", erro.message);
            console.error("Confira se o MySQL está ligado e se docs/banco.sql foi importado.");
        }

        this.#app.listen(this.#porta, () => {
            console.log(`${config.api.nome} rodando em http://localhost:${this.#porta}/`);
            console.log(`Frontend: http://localhost:${this.#porta}/login.html`);
        });
    }
};
