# Contrato das APIs — PAW 4º bimestre (gestão de eventos)

As duas APIs (JavaScript/Node.js na porta **3000** e Python/Flask na porta **8080**)
implementam **o mesmo contrato**. Tudo que está documentado aqui vale para as duas — o que
muda é só a linguagem de implementação. O frontend depende apenas deste contrato.

- Node.js: `http://localhost:3000/api/v1`
- Flask: `http://localhost:8080/api/v1`

## Convenções

### Envelope de resposta

Toda resposta (sucesso ou erro) tem o mesmo formato:

```json
{
  "success": true,
  "message": "Busca realizada com sucesso",
  "data": { "eventos": [ /* ... */ ] }
}
```

- `success` — `true` para status < 400, `false` caso contrário.
- `message` — resumo da operação/erro.
- `data` — conteúdo (listas sempre com **o nome da coleção**, ex.: `eventos`, `lotes`,
  `organizadores`; mesmo no GET de um item só, a coleção vem com 1 elemento).
- Erros de regra trazem `data.detalhes` (ou `data.mensagem`/`data.campo`) com o texto
  detalhado, pronto para exibir ao usuário.

### Autenticação

Todas as rotas exigem o token **exceto** `POST /auth/login`, `POST /auth/registrar` e
`GET /health`:

```
Authorization: Bearer <token>
```

- Token JWT **HS256**, validade de **30 dias**, claims: `idOrganizador`, `nome`, `email`,
  `perfil` (+ `iss`, `aud`, `sub`, `iat`, `exp`, `nbf`, `jti`).
- A chave é a mesma nas duas APIs: **o token emitido pela API Node vale na API Flask** e
  vice-versa. A chave pode ser trocada com a variável de ambiente `JWT_KEY`.

### Códigos de status usados

| Código | Quando |
|---|---|
| 200 | consulta/atualização/exclusão com sucesso |
| 201 | cadastro com sucesso |
| 400 | corpo ausente/ inválido, id de rota inválido, regra de negócio (data no passado, disponível > total, excluir a si mesmo) |
| 401 | sem token, token inválido/expirado, credenciais erradas |
| 403 | perfil `comum` tentando operação de administrador |
| 404 | recurso/rota inexistente |
| 409 | duplicidade (e-mail, evento, lote) ou exclusão bloqueada por vínculo |
| 500 | erro inesperado (ex.: banco fora) |

Exemplos reais de erro:

```json
{"success":false,"message":"Token inválido ou expirado","data":{"mensagem":"Envie o cabeçalho Authorization: Bearer <token>."}}

{"success":false,"message":"Evento duplicado","data":{"detalhes":{"mensagem":"Já existe um evento com este título nesta data/hora."}}}

{"success":false,"message":"Evento possui lotes vinculados","data":{"detalhes":{"mensagem":"Exclua os 2 lote(s) deste evento antes de removê-lo."}}}
```

### Corpos de requisição

POST/PUT usam **envelope com o nome do recurso**:

```json
{ "organizador": { ... } }
{ "evento": { ... } }
{ "lote": { ... } }
```

Sem o envelope a resposta é **400** (`"O campo 'evento' é obrigatório no corpo da requisição."`).
Os nomes dos campos seguem o padrão `camelCase` usado no frontend.
Datas/horas usam ISO-8601: `2027-03-15T08:00:00` (aceita `2027-03-15T08:00`).

Entidades e campos devolvidos:

| Entidade | Campos na resposta |
|---|---|
| organizador | `idOrganizador`, `nome`, `email`, `telefone`, `perfil`, `createdAt` — **a senha nunca é devolvida** |
| evento | `idEvento`, `titulo`, `descricao`, `dataEvento`, `status`, `idOrganizador`, `organizadorNome` (join), `createdAt` |
| lote | `idLote`, `nome`, `preco`, `quantidadeTotal`, `quantidadeDisponivel`, `idEvento`, `eventoTitulo` (join), `createdAt` |

---

## Autenticação — `/auth`

### POST `/auth/login`

```json
{ "organizador": { "email": "ana@email.com", "senha": "123456" } }
```

- **200** → `{"organizadores":[{...}], "token":"eyJ..."}`
- **400** se faltar e-mail/senha · **401** `Credenciais inválidas` (e-mail ou senha errados)

### POST `/auth/registrar`

Cadastro público (não precisa de token). Campos obrigatórios: `nome`, `email`, `senha`.
O **perfil é sempre `comum`** (um `perfil: "administrador"` no corpo é ignorado).

- **201** → `{"organizadores":[{...}], "token":"eyJ..."}` (já entra logado)
- **400** validação · **409** `E-mail já cadastrado`

### GET `/auth/perfil`

Devolve os dados do dono do token (usado no login automático do frontend).

- **200** → `{"organizadores":[{...}]}` · **401** sem token válido

---

## Organizadores — `/organizadores`

| Método | Rota | Quem pode | Resposta |
|---|---|---|---|
| GET | `/organizadores?nome=&email=&perfil=` | qualquer logado | 200 |
| GET | `/organizadores/{id}` | qualquer logado | 200 · 404 |
| POST | `/organizadores` | **administrador** | 201 · 400 · 403 · 409 |
| PUT | `/organizadores/{id}` | **administrador** ou **o próprio** | 200 · 400 · 403 · 404 · 409 |
| DELETE | `/organizadores/{id}` | **administrador** (nunca a si mesmo) | 200 · 400 · 403 · 404 · 409 |

Filtros do GET: `nome` (parcial), `email` (parcial), `perfil` (`administrador`/`comum`).

Corpo (POST/PUT):

```json
{
  "organizador": {
    "nome": "Diego Alves",
    "email": "diego@email.com",
    "telefone": "(47) 90000-1111",
    "senha": "123456",
    "perfil": "comum"
  }
}
```

Regras:

- `nome` 3–120 · `email` válido e **único** · `senha` 6–72 (hash bcrypt; no PUT, omitir a
  senha mantém a atual) · `telefone` opcional até 20 · `perfil` ∈ {`administrador`,`comum`}.
- Perfil `comum` só edita **o próprio** cadastro (**403** `Você só pode editar o seu próprio cadastro.`);
  criar/excluir outro organizador exige administrador (**403**
  `Somente administradores podem cadastrar organizadores.` / `Esta operação exige o perfil administrador.`).
- Excluir a si mesmo → **400** · excluir organizador **com eventos** → **409**
  (`Exclua ou transfira os 1 evento(s) antes de remover este organizador.`).

---

## Eventos — `/eventos`

| Método | Rota | Quem pode | Resposta |
|---|---|---|---|
| GET | `/eventos?titulo=&status=&idOrganizador=&de=&ate=` | qualquer logado | 200 |
| GET | `/eventos/{id}` | qualquer logado | 200 · 404 |
| POST | `/eventos` | qualquer logado | 201 · 400 · 409 |
| PUT | `/eventos/{id}` | qualquer logado | 200 · 400 · 404 · 409 |
| DELETE | `/eventos/{id}` | qualquer logado | 200 · 404 · 409 |

Filtros do GET:

| Filtro | Efeito |
|---|---|
| `titulo` | trecho do título (`LIKE %...%`) |
| `status` | `planejado`, `confirmado`, `realizado`, `cancelado` |
| `idOrganizador` | eventos de um organizador |
| `de` / `ate` | intervalo de datas (AAAA-MM-DD, inclusivo no dia) |

Corpo (POST/PUT):

```json
{
  "evento": {
    "titulo": "Semana de Tecnologia 2027",
    "descricao": "Palestras e workshops de PAW, BdD e POOI",
    "dataEvento": "2027-03-15T08:00",
    "status": "confirmado",
    "idOrganizador": 1
  }
}
```

Regras:

- `titulo` 3–150 e **único na mesma data/hora** → **409** `Evento duplicado`.
- `dataEvento` **não pode estar no passado** → **400** `A data do evento não pode estar no passado.`
- `status` fora da lista → **400** `status deve ser planejado, confirmado, realizado, cancelado.`
- `idOrganizador` inexistente → **400** `Organizador inválido`.
- Excluir evento **com lotes** → **409** `Exclua os N lote(s) deste evento antes de removê-lo.`
- `descricao` é opcional (até 2000 caracteres).

---

## Lotes — `/lotes`

| Método | Rota | Quem pode | Resposta |
|---|---|---|---|
| GET | `/lotes?nome=&idEvento=&precoMin=&precoMax=` | qualquer logado | 200 |
| GET | `/lotes/{id}` | qualquer logado | 200 · 404 |
| POST | `/lotes` | qualquer logado | 201 · 400 · 409 |
| PUT | `/lotes/{id}` | qualquer logado | 200 · 400 · 404 · 409 |
| DELETE | `/lotes/{id}` | qualquer logado | 200 · 404 |

Filtros do GET: `nome` (parcial), `idEvento`, `precoMin`, `precoMax`.

Corpo (POST/PUT):

```json
{
  "lote": {
    "nome": "1º lote",
    "preco": 80.00,
    "quantidadeTotal": 200,
    "quantidadeDisponivel": 200,
    "idEvento": 1
  }
}
```

Regras:

- `nome` 2–80 e **único dentro do evento** → **409** `O evento já possui um lote chamado "1º lote".`
- `preco` 0–999999.99 · `quantidadeTotal` 1–1.000.000.
- `quantidadeDisponivel` é opcional no POST: omitida, assume o valor de `quantidadeTotal`;
  **nunca pode ser maior que o total** → **400** `quantidadeDisponivel não pode ser maior que quantidadeTotal.`
- `idEvento` inexistente → **400** `Evento inválido`.

---

## Saúde da API — `/health`

Sem token, útil para saber se a API e o banco estão de pé:

```bash
curl http://localhost:3000/api/v1/health
```

- **200** → `{"success":true,"message":"API online","data":{"api":"Pulse Eventos API (Node.js)","versao":"1.0.0","banco":"paw4bi_eventos","hora":"..."}}`
- **503** → API no ar, banco indisponível

---

## Exemplos rápidos (curl)

```bash
# 1) login (guarde o token)
TOKEN=$(curl -s -X POST http://localhost:3000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"organizador":{"email":"ana@email.com","senha":"123456"}}' \
  | python -c "import sys,json;print(json.load(sys.stdin)['data']['token'])")

# 2) listar com filtro
curl -s "http://localhost:3000/api/v1/eventos?status=confirmado" -H "Authorization: Bearer $TOKEN"

# 3) criar evento
curl -s -X POST http://localhost:3000/api/v1/eventos \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"evento":{"titulo":"Workshop de Testes","dataEvento":"2027-09-10T19:30","status":"planejado","idOrganizador":1}}'

# 4) atualizar lote
curl -s -X PUT http://localhost:3000/api/v1/lotes/1 \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"lote":{"nome":"1º lote","preco":95.00,"quantidadeTotal":200,"quantidadeDisponivel":180,"idEvento":1}}'

# 5) excluir lote
curl -s -X DELETE http://localhost:3000/api/v1/lotes/1 -H "Authorization: Bearer $TOKEN"
```

Trocando `3000` por `8080` os mesmos comandos funcionam na API Flask — inclusive com o token
obtido na outra API.

## CORS e arquivos estáticos

- CORS liberado (`Access-Control-Allow-Origin: *`) para permitir que o frontend seja aberto de
  qualquer origem.
- As duas APIs também **servem o frontend** (`POST /` estático): `http://localhost:3000/login.html`
  e `http://localhost:8080/login.html`.
- Rotas `/api/v1/*` inexistentes respondem **404** no envelope padrão
  (`GET /api/v1/naoexiste não existe nesta API.`) — em qualquer método, nas duas APIs.
