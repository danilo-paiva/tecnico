const bcrypt = require("bcrypt");
const Organizador = require("../modelo/Organizador");
const ErrorResponse = require("../http/ErrorResponse");

/**
 * Service de Organizador.
 * Camada de regra de negócio: decide o que depende do banco (e-mail único,
 * organizador com eventos) e do usuário logado (perfil).
 * Fluxo: monta o Model (os setters validam o domínio), confere as regras, chama
 * o DAO e devolve o registro já salvo.
 * Não escreve SQL (DAO) nem monta resposta HTTP (Controle); usa ErrorResponse
 * só para carregar o status do erro (403, 404, 409...).
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

    /**
     * Cria um organizador com a senha já convertida em hash.
     * @param {object} dados nome, email, senha, telefone, perfil
     * @param {object} opcoes perfil forçado por quem chamou (cadastro público)
     * @returns {Promise<object>} organizador criado (vindo do banco, sem a senha)
     * @throws ErrorResponse 409 quando o e-mail já existe
     */
    async createOrganizador(dados = {}, opcoes = {}) {
        const organizador = new Organizador();
        organizador.nome = dados.nome;
        organizador.email = dados.email;
        organizador.senha = dados.senha;
        organizador.telefone = dados.telefone;
        // opcoes.perfil ganha do corpo: é assim que a rota pública força "comum".
        organizador.perfil = opcoes.perfil || dados.perfil || "comum";

        // O banco tem UNIQUE no e-mail, mas checar antes responde 409 com mensagem clara.
        if (await this.#organizadorDAO.existeEmail(organizador.email)) {
            throw new ErrorResponse(409, "E-mail já cadastrado", {
                campo: "email",
                mensagem: `O e-mail ${organizador.email} já está em uso.`
            });
        }

        // A senha nunca é gravada em texto puro: bcrypt com custo 10 gera o hash.
        const senhaHash = await bcrypt.hash(organizador.senha, 10);
        const idOrganizador = await this.#organizadorDAO.create(organizador, senhaHash);
        return this.#organizadorDAO.findById(idOrganizador);
    }

    /**
     * Cadastro pela rota administrativa: apenas administrador define o perfil.
     * @throws ErrorResponse 403 quando quem chamou não é administrador
     */
    async createOrganizadorComoAdministrador(dados = {}, usuarioLogado) {
        if (usuarioLogado && usuarioLogado.perfil !== "administrador") {
            throw new ErrorResponse(403, "Acesso negado", {
                mensagem: "Somente administradores podem cadastrar organizadores."
            });
        }
        return this.createOrganizador(dados, { perfil: dados.perfil || "comum" });
    }

    /** Lista com os filtros recebidos (aqui não há regra: listagem é consulta direta). */
    async findAll(filtros = {}) {
        return this.#organizadorDAO.findAll(filtros);
    }

    /** Busca por id. @throws ErrorResponse 404 quando não existe */
    async findById(idOrganizador) {
        const organizador = await this.#organizadorDAO.findById(idOrganizador);
        if (!organizador) {
            throw new ErrorResponse(404, "Organizador não encontrado", {
                mensagem: `Não existe organizador com o id ${idOrganizador}.`
            });
        }
        return organizador;
    }

    /**
     * Atualiza um organizador: o comum só edita o próprio cadastro, o
     * administrador edita qualquer um.
     * @throws ErrorResponse 403 (cadastro de outro), 404 (não existe), 409 (e-mail em uso)
     */
    async updateOrganizador(idOrganizador, dados = {}, usuarioLogado) {
        // Permissão: administrador edita todos; comum edita só o id que está no token.
        const ehAdministrador = usuarioLogado && usuarioLogado.perfil === "administrador";
        if (!ehAdministrador && Number(usuarioLogado && usuarioLogado.idOrganizador) !== Number(idOrganizador)) {
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
        // Campo não enviado mantém o valor atual — por isso cada linha testa undefined/null.
        organizador.nome = (dados.nome !== undefined && dados.nome !== null ? dados.nome : atual.nome);
        organizador.email = (dados.email !== undefined && dados.email !== null ? dados.email : atual.email);
        organizador.telefone = dados.telefone !== undefined ? dados.telefone : atual.telefone;

        // Somente administrador altera o perfil; usuário comum mantém o perfil atual.
        organizador.perfil = ehAdministrador
            ? (dados.perfil !== undefined && dados.perfil !== null ? dados.perfil : atual.perfil)
            : atual.perfil;

        let senhaHash = null;
        if (dados.senha) {
            organizador.senha = dados.senha;
            senhaHash = await bcrypt.hash(organizador.senha, 10);
        }

        // Ignora o próprio id: repetir o mesmo e-mail no update não é duplicidade.
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

    /**
     * Exclui um organizador (somente administrador).
     * @throws ErrorResponse 403 (não é administrador), 400 (o próprio usuário),
     *                       404 (não existe), 409 (tem eventos vinculados)
     */
    async deleteOrganizador(idOrganizador, usuarioLogado) {
        if (usuarioLogado && usuarioLogado.perfil !== "administrador") {
            throw new ErrorResponse(403, "Acesso negado", {
                mensagem: "Somente administradores podem excluir organizadores."
            });
        }

        // Ninguém exclui a si mesmo: evita ficar sem o usuário que está logado.
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

        // A FK do banco já barraria (ON DELETE NO ACTION), mas aqui a resposta é 409.
        const totalEventos = await this.#eventoDAO.countByOrganizador(idOrganizador);
        if (totalEventos > 0) {
            throw new ErrorResponse(409, "Organizador possui eventos vinculados", {
                mensagem: `Exclua ou transfira os ${totalEventos} evento(s) antes de remover este organizador.`
            });
        }

        return this.#organizadorDAO.delete(idOrganizador);
    }

    /**
     * Autentica e devolve o organizador + token JWT.
     * @throws ErrorResponse 400 (faltou e-mail ou senha), 401 (credenciais inválidas)
     */
    async login(email, senha) {
        if (!email || !senha) {
            throw new ErrorResponse(400, "Dados de login incompletos", {
                mensagem: "Informe e-mail e senha."
            });
        }

        // Normaliza o e-mail igual ao Model (trim + minúsculo) para casar com o banco.
        const credenciais = await this.#organizadorDAO.buscarCredenciais(
            String(email).trim().toLowerCase()
        );

        // Mesma mensagem para e-mail inexistente e senha errada: não revela qual falhou.
        if (!credenciais) {
            throw new ErrorResponse(401, "Credenciais inválidas", {
                mensagem: "E-mail ou senha incorretos."
            });
        }

        // bcrypt.compare compara a senha digitada com o hash gravado no banco.
        const conferiu = await bcrypt.compare(String(senha), credenciais.senha);
        if (!conferiu) {
            throw new ErrorResponse(401, "Credenciais inválidas", {
                mensagem: "E-mail ou senha incorretos."
            });
        }

        // Claims do token: o que as próximas requisições saberão sem consultar o banco.
        const organizador = {
            idOrganizador: credenciais.idOrganizador,
            nome: credenciais.nome,
            email: credenciais.email,
            telefone: credenciais.telefone,
            perfil: credenciais.perfil
        };
        const token = this.#tokenJWT.gerarToken(organizador);
        return { organizador: organizador, token: token };
    }
};
