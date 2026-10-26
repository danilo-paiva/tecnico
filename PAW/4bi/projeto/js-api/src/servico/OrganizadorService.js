const bcrypt = require("bcrypt");
const Organizador = require("../modelo/Organizador");
const ErrorResponse = require("../http/ErrorResponse");

/**
 * Service de Organizador.
 * Concentra as regras de negócio: e-mail único, hash da senha, login/JWT,
 * permissões de perfil e exclusão segura (sem eventos vinculados).
 */
module.exports = class OrganizadorService {
    #organizadorDAO;
    #eventoDAO;
    #tokenJWT;

    constructor(organizadorDAO, eventoDAO, tokenJWT) {
        this.#organizadorDAO = organizadorDAO;
        this.#eventoDAO = eventoDAO;
        this.#tokenJWT = tokenJWT;
    }

    async createOrganizador(dados = {}, opcoes = {}) {
        const organizador = new Organizador();
        organizador.nome = dados.nome;
        organizador.email = dados.email;
        organizador.senha = dados.senha;
        organizador.telefone = dados.telefone;
        organizador.perfil = opcoes.perfil || dados.perfil || "comum";

        if (await this.#organizadorDAO.existeEmail(organizador.email)) {
            throw new ErrorResponse(409, "E-mail já cadastrado", {
                campo: "email",
                mensagem: `O e-mail ${organizador.email} já está em uso.`
            });
        }

        const senhaHash = await bcrypt.hash(organizador.senha, 10);
        const idOrganizador = await this.#organizadorDAO.create(organizador, senhaHash);
        return this.#organizadorDAO.findById(idOrganizador);
    }

    /** Cadastro pela rota administrativa: apenas administrador define o perfil. */
    async createOrganizadorComoAdministrador(dados = {}, usuarioLogado) {
        if (usuarioLogado?.perfil !== "administrador") {
            throw new ErrorResponse(403, "Acesso negado", {
                mensagem: "Somente administradores podem cadastrar organizadores."
            });
        }
        return this.createOrganizador(dados, { perfil: dados.perfil || "comum" });
    }

    async findAll(filtros = {}) {
        return this.#organizadorDAO.findAll(filtros);
    }

    async findById(idOrganizador) {
        const organizador = await this.#organizadorDAO.findById(idOrganizador);
        if (!organizador) {
            throw new ErrorResponse(404, "Organizador não encontrado", {
                mensagem: `Não existe organizador com o id ${idOrganizador}.`
            });
        }
        return organizador;
    }

    async updateOrganizador(idOrganizador, dados = {}, usuarioLogado) {
        const ehAdministrador = usuarioLogado?.perfil === "administrador";
        if (!ehAdministrador && Number(usuarioLogado?.idOrganizador) !== Number(idOrganizador)) {
            throw new ErrorResponse(403, "Acesso negado", {
                mensagem: "Você só pode editar o seu próprio cadastro."
            });
        }

        const atual = await this.#organizadorDAO.findById(idOrganizador);
        if (!atual) {
            throw new ErrorResponse(404, "Organizador não encontrado", {
                mensagem: `Não existe organizador com o id ${idOrganizador}.`
            });
        }

        const organizador = new Organizador();
        organizador.idOrganizador = idOrganizador;
        organizador.nome = dados.nome ?? atual.nome;
        organizador.email = dados.email ?? atual.email;
        organizador.telefone = dados.telefone !== undefined ? dados.telefone : atual.telefone;

        // Somente administrador altera o perfil; usuário comum mantém o perfil atual.
        organizador.perfil = ehAdministrador
            ? (dados.perfil ?? atual.perfil)
            : atual.perfil;

        let senhaHash = null;
        if (dados.senha) {
            organizador.senha = dados.senha;
            senhaHash = await bcrypt.hash(organizador.senha, 10);
        }

        if (await this.#organizadorDAO.existeEmail(organizador.email, organizador.idOrganizador)) {
            throw new ErrorResponse(409, "E-mail já cadastrado", {
                campo: "email",
                mensagem: `O e-mail ${organizador.email} já está em uso.`
            });
        }

        const atualizou = await this.#organizadorDAO.update(organizador, senhaHash);
        if (!atualizou) {
            throw new ErrorResponse(500, "Falha ao atualizar organizador");
        }

        return this.#organizadorDAO.findById(organizador.idOrganizador);
    }

    async deleteOrganizador(idOrganizador, usuarioLogado) {
        if (usuarioLogado?.perfil !== "administrador") {
            throw new ErrorResponse(403, "Acesso negado", {
                mensagem: "Somente administradores podem excluir organizadores."
            });
        }

        if (Number(usuarioLogado.idOrganizador) === Number(idOrganizador)) {
            throw new ErrorResponse(400, "Operação não permitida", {
                mensagem: "Não é permitido excluir o próprio usuário logado."
            });
        }

        const organizador = await this.#organizadorDAO.findById(idOrganizador);
        if (!organizador) {
            throw new ErrorResponse(404, "Organizador não encontrado", {
                mensagem: `Não existe organizador com o id ${idOrganizador}.`
            });
        }

        const totalEventos = await this.#eventoDAO.countByOrganizador(idOrganizador);
        if (totalEventos > 0) {
            throw new ErrorResponse(409, "Organizador possui eventos vinculados", {
                mensagem: `Exclua ou transfira os ${totalEventos} evento(s) antes de remover este organizador.`
            });
        }

        return this.#organizadorDAO.delete(idOrganizador);
    }

    async login(email, senha) {
        if (!email || !senha) {
            throw new ErrorResponse(400, "Dados de login incompletos", {
                mensagem: "Informe e-mail e senha."
            });
        }

        const credenciais = await this.#organizadorDAO.buscarCredenciais(
            String(email).trim().toLowerCase()
        );

        if (!credenciais) {
            throw new ErrorResponse(401, "Credenciais inválidas", {
                mensagem: "E-mail ou senha incorretos."
            });
        }

        const conferiu = await bcrypt.compare(String(senha), credenciais.senha);
        if (!conferiu) {
            throw new ErrorResponse(401, "Credenciais inválidas", {
                mensagem: "E-mail ou senha incorretos."
            });
        }

        const { senha: _ignorada, ...organizador } = credenciais;
        const token = this.#tokenJWT.gerarToken(organizador);
        return { organizador, token };
    }
};
