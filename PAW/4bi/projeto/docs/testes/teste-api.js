/**
 * Teste automatizado do contrato das APIs (Node.js e Flask).
 *
 * Roda a mesma bateria de verificacoes na API escolhida por BASE:
 *   BASE=http://localhost:3000/api/v1 node docs/testes/teste-api.js   (Node.js)
 *   BASE=http://localhost:8080/api/v1 node docs/testes/teste-api.js   (Flask)
 *
 * Requer Node 18+ (fetch nativo) e o banco com o seed de docs/banco.sql.
 * O proprio script cria e remove os registros que usa.
 */
const BASE = process.env.BASE || "http://localhost:3000/api/v1";
let falhas = 0;

const req = async (metodo, caminho, corpo = null, token = null) => {
    const headers = { "Content-Type": "application/json" };
    if (token) headers.Authorization = `Bearer ${token}`;
    const r = await fetch(`${BASE}/${caminho}`, {
        method: metodo,
        headers,
        body: corpo ? JSON.stringify(corpo) : undefined
    });
    const texto = await r.text();
    let json = null;
    try { json = texto ? JSON.parse(texto) : null; } catch { json = texto; }
    return { status: r.status, json };
};

const checar = (nome, condicao, extra = "") => {
    if (condicao) {
        console.log(`  OK   ${nome}`);
    } else {
        falhas++;
        console.log(`  FALHA ${nome} ${extra}`);
    }
};

(async () => {
    console.log(`\n== ${BASE} ==\n`);

    let r = await req("GET", "organizadores");
    checar("sem token -> 401", r.status === 401, `(${r.status})`);

    r = await req("POST", "auth/login", { organizador: { email: "ana@email.com", senha: "errada" } });
    checar("login com senha errada -> 401", r.status === 401, `(${r.status})`);

    r = await req("POST", "auth/login", { organizador: { email: "ana@email.com", senha: "123456" } });
    checar("login admin -> 200 + token", r.status === 200 && r.json?.data?.token, `(${r.status})`);
    const tokenAna = r.json?.data?.token;
    checar("login não devolve senha", !JSON.stringify(r.json).includes("$2b$"));

    r = await req("GET", "organizadores", null, tokenAna);
    checar("GET organizadores -> 3", r.status === 200 && r.json.data.organizadores.length === 3, JSON.stringify(r.json));

    r = await req("GET", "organizadores?perfil=administrador", null, tokenAna);
    checar("filtro perfil=administrador -> 1", r.json.data.organizadores.length === 1);

    r = await req("GET", "organizadores?nome=bruno", null, tokenAna);
    checar("filtro nome=bruno -> 1", r.json.data.organizadores.length === 1);

    r = await req("GET", "eventos?status=confirmado", null, tokenAna);
    checar("filtro eventos status=confirmado -> 2", r.json.data.eventos.length === 2, JSON.stringify(r.json.data.eventos?.length));

    r = await req("GET", "lotes?precoMax=100", null, tokenAna);
    checar("filtro lotes precoMax=100", r.json.data.lotes.every((l) => Number(l.preco) <= 100));

    r = await req("GET", "lotes?idEvento=1", null, tokenAna);
    checar("filtro lotes por evento", r.json.data.lotes.every((l) => l.idEvento === 1));

    r = await req("POST", "eventos", { evento: { titulo: "Teste data passada", dataEvento: "2020-01-01T10:00", idOrganizador: 1 } }, tokenAna);
    checar("evento no passado -> 400", r.status === 400, `(${r.status}) ${JSON.stringify(r.json)}`);

    r = await req("POST", "eventos", { evento: { titulo: "Evento de Teste", descricao: "criado pelo teste", dataEvento: "2027-11-11T20:00", idOrganizador: 1 } }, tokenAna);
    checar("criar evento -> 201", r.status === 201, `(${r.status}) ${JSON.stringify(r.json)}`);
    const idEvento = r.json?.data?.eventos?.[0]?.idEvento;

    r = await req("POST", "eventos", { evento: { titulo: "Evento de Teste", dataEvento: "2027-11-11T20:00", idOrganizador: 1 } }, tokenAna);
    checar("evento duplicado -> 409", r.status === 409, `(${r.status})`);

    r = await req("PUT", `eventos/${idEvento}`, { evento: { status: "confirmado" } }, tokenAna);
    checar("atualizar evento -> 200", r.status === 200 && r.json.data.eventos[0].status === "confirmado", `(${r.status}) ${JSON.stringify(r.json)}`);

    r = await req("POST", "lotes", { lote: { nome: "Lote teste", preco: 50, quantidadeTotal: 10, idEvento } }, tokenAna);
    checar("criar lote -> 201 (disponivel = total)", r.status === 201 && r.json.data.lotes[0].quantidadeDisponivel === 10, `(${r.status}) ${JSON.stringify(r.json)}`);
    const idLote = r.json?.data?.lotes?.[0]?.idLote;

    r = await req("POST", "lotes", { lote: { nome: "Lote teste", preco: 50, quantidadeTotal: 10, idEvento } }, tokenAna);
    checar("lote duplicado -> 409", r.status === 409, `(${r.status})`);

    r = await req("POST", "lotes", { lote: { nome: "Lote inválido", quantidadeTotal: 5, quantidadeDisponivel: 8, idEvento } }, tokenAna);
    checar("disponivel > total -> 400", r.status === 400, `(${r.status})`);

    r = await req("DELETE", `eventos/${idEvento}`, null, tokenAna);
    checar("excluir evento com lote -> 409", r.status === 409, `(${r.status})`);

    r = await req("DELETE", `lotes/${idLote}`, null, tokenAna);
    checar("excluir lote -> 200", r.status === 200, `(${r.status})`);

    r = await req("DELETE", `eventos/${idEvento}`, null, tokenAna);
    checar("excluir evento -> 200", r.status === 200, `(${r.status})`);

    r = await req("DELETE", "organizadores/3", null, tokenAna);
    checar("excluir organizador com eventos -> 409", r.status === 409, `(${r.status}) ${JSON.stringify(r.json)}`);

    r = await req("POST", "auth/login", { organizador: { email: "bruno@email.com", senha: "123456" } });
    const tokenBruno = r.json?.data?.token;
    checar("login comum -> 200", r.status === 200);

    r = await req("DELETE", "organizadores/3", null, tokenBruno);
    checar("comum excluindo -> 403", r.status === 403, `(${r.status})`);

    r = await req("POST", "organizadores", { organizador: { nome: "Novo", email: "novo@email.com", senha: "123456" } }, tokenBruno);
    checar("comum criando organizador -> 403", r.status === 403, `(${r.status})`);

    r = await req("PUT", "organizadores/3", { organizador: { nome: "Carla Alterada" } }, tokenBruno);
    checar("comum editando outro -> 403", r.status === 403, `(${r.status})`);

    r = await req("PUT", "organizadores/2", { organizador: { telefone: "(47) 90000-0000" } }, tokenBruno);
    checar("comum editando a si -> 200", r.status === 200, `(${r.status}) ${JSON.stringify(r.json)}`);

    r = await req("POST", "organizadores", { organizador: { nome: "Teste Admin", email: "teste.admin@email.com", senha: "123456", perfil: "administrador" } }, tokenAna);
    checar("admin cria organizador -> 201", r.status === 201 && r.json.data.organizadores[0].perfil === "administrador", `(${r.status}) ${JSON.stringify(r.json)}`);
    const idNovo = r.json?.data?.organizadores?.[0]?.idOrganizador;

    r = await req("DELETE", `organizadores/${idNovo}`, null, tokenAna);
    checar("admin exclui organizador sem eventos -> 200", r.status === 200, `(${r.status})`);

    const emailUnico = `auto.${Date.now()}@email.com`;
    r = await req("POST", "auth/registrar", { organizador: { nome: "Auto Teste", email: emailUnico, senha: "123456", telefone: "(47) 91234-5678" } });
    checar("registro público -> 201 + token", r.status === 201 && r.json?.data?.token, `(${r.status}) ${JSON.stringify(r.json)}`);
    const tokenAuto = r.json?.data?.token;
    checar("registro público força perfil comum", r.json?.data?.organizadores?.[0]?.perfil === "comum");

    r = await req("GET", "auth/perfil", null, tokenAuto);
    checar("perfil via token -> 200", r.status === 200 && r.json.data.organizadores[0].email === emailUnico, `(${r.status})`);

    r = await req("DELETE", `organizadores/${r.json?.data?.organizadores?.[0]?.idOrganizador}`, null, tokenAna);
    checar("limpeza do registro público", r.status === 200, `(${r.status})`);

    r = await req("GET", "rota-que-nao-existe", null, tokenAna);
    checar("rota inexistente -> 404 JSON", r.status === 404 && r.json?.success === false, `(${r.status})`);

    console.log(falhas === 0 ? "\nTODOS OS TESTES PASSARAM\n" : `\n${falhas} FALHA(S)\n`);
    process.exit(falhas === 0 ? 0 : 1);
})();
