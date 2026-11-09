const Banco = require("../database/Banco");

// Consulta base: os AS já entregam a coluna em camelCase, igual ao JSON da API.
// DATE_FORMAT devolve a data em ISO (com "T"), sem o driver converter fuso.
const SELECT = `
    SELECT id_organizador AS idOrganizador,
           nome,
           email,
           telefone,
           perfil,
           DATE_FORMAT(created_at, '%Y-%m-%dT%H:%i:%s') AS createdAt
      FROM organizadores`;

/**
 * DAO (Data Access Object) de Organizador.
 * Isola todo o SQL da regra de negócio: o Service nunca escreve consulta.
 * Não valida dado (isso é do Model) e não decide se pode gravar (isso é do
 * Service). Se o banco mudar, só este arquivo muda.
 */
module.exports = class OrganizadorDAO {
    /** Lista com filtros opcionais de nome, email e perfil (o WHERE é montado conforme o que veio). */
    async findAll(filtros = {}) {
        const condicoes = [];
        const parametros = [];

        if (filtros.nome) {
            condicoes.push("nome LIKE ?");
            parametros.push(`%${filtros.nome}%`);
        }
        if (filtros.email) {
            condicoes.push("email LIKE ?");
            parametros.push(`%${filtros.email}%`);
        }
        if (filtros.perfil) {
            condicoes.push("perfil = ?");
            parametros.push(filtros.perfil);
        }

        // Cada filtro vira um "?": o valor nunca é concatenado dentro do SQL.
        const where = condicoes.length > 0 ? ` WHERE ${condicoes.join(" AND ")}` : "";
        return Banco.consultar(`${SELECT}${where} ORDER BY nome`, parametros);
    }

    /** Devolve uma linha ou null — o 404 é decidido no Service. */
    async findById(idOrganizador) {
        const linhas = await Banco.consultar(`${SELECT} WHERE id_organizador = ?`, [idOrganizador]);
        return linhas.length > 0 ? linhas[0] : null;
    }

    /** Devolve uma linha ou null (usado aqui para checar e-mail único). */
    async findByEmail(email) {
        const linhas = await Banco.consultar(`${SELECT} WHERE email = ?`, [email]);
        return linhas.length > 0 ? linhas[0] : null;
    }

    /** Retorna também o hash da senha — usado apenas no login. */
    async buscarCredenciais(email) {
        const linhas = await Banco.consultar(
            `SELECT id_organizador AS idOrganizador, nome, email, telefone, perfil, senha
               FROM organizadores WHERE email = ?`,
            [email]
        );
        return linhas.length > 0 ? linhas[0] : null;
    }

    /**
     * Diz se o e-mail já está em uso (o banco também tem UNIQUE, mas aqui a
     * resposta sai como 409 com mensagem clara).
     * @param {number|null} ignorarId no update, o próprio registro não conta como duplicado
     * @returns {Promise<boolean>}
     */
    async existeEmail(email, ignorarId = null) {
        const parametros = [email];
        let SQL = "SELECT COUNT(*) AS total FROM organizadores WHERE email = ?";
        if (ignorarId !== null) {
            SQL += " AND id_organizador <> ?";
            parametros.push(ignorarId);
        }
        const linhas = await Banco.consultar(SQL, parametros);
        return Number(linhas[0].total) > 0;
    }

    /** Insere com o hash da senha (nunca a senha pura) e devolve o id gerado. */
    async create(organizador, senhaHash) {
        const resultado = await Banco.executar(
            `INSERT INTO organizadores (nome, email, senha, telefone, perfil)
             VALUES (?, ?, ?, ?, ?)`,
            [
                organizador.nome,
                organizador.email,
                senhaHash,
                organizador.telefone,
                organizador.perfil
            ]
        );

        if (!resultado.insertId) {
            throw new Error("Falha ao inserir organizador.");
        }
        return resultado.insertId;
    }

    /**
     * Atualiza o registro; a senha só é trocada quando veio uma nova.
     * @param {string|null} senhaHash null mantém a senha que já está no banco
     * @returns {Promise<boolean>}
     */
    async update(organizador, senhaHash = null) {
        const campos = ["nome = ?", "email = ?", "telefone = ?", "perfil = ?"];
        const parametros = [
            organizador.nome,
            organizador.email,
            organizador.telefone,
            organizador.perfil
        ];

        if (senhaHash) {
            campos.push("senha = ?");
            parametros.push(senhaHash);
        }

        parametros.push(organizador.idOrganizador);

        await Banco.executar(
            `UPDATE organizadores SET ${campos.join(", ")} WHERE id_organizador = ?`,
            parametros
        );
        // O MySQL devolve affectedRows = 0 quando os valores enviados são iguais
        // aos já gravados; a existência do registro já foi validada no Service.
        return true;
    }

    /** Remove e devolve false quando o id não existia mais. */
    async delete(idOrganizador) {
        const resultado = await Banco.executar(
            "DELETE FROM organizadores WHERE id_organizador = ?",
            [idOrganizador]
        );
        return resultado.affectedRows > 0;
    }
};
