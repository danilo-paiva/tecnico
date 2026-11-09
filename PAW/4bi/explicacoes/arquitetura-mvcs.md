# Arquitetura MVCS nas duas APIs — três explicações

> **Material de apoio (fora da pasta do projeto).** Este documento e o
> `arquitetura-mvcs.html` ficam em `PAW/4bi/explicacoes/` de propósito: o que vai para o
> classroom é só `PAW/4bi/projeto/`. Os arquivos citados aqui estão em
> `PAW/4bi/projeto/js-api/` e `PAW/4bi/projeto/flask-api/` (os caminhos abaixo são relativos à
> pasta do projeto).

Documento de apoio à apresentação. O mesmo desenho aparece duas vezes: em **JavaScript
(Node.js + Express)** e em **Python (Flask)**. As três explicações de cada API são três
ângulos do mesmo assunto:

1. **Mapa das camadas** — o que existe, onde fica e quem pode falar com quem.
2. **O caminho de uma requisição** — o que acontece, arquivo por arquivo, do clique até o JSON.
3. **Onde mora cada decisão** — qual camada decide o quê e por que as regras não se misturam.

> **MVCS = MVC + Service.** O `Controller` continua sendo o intermediário entre a entrada
> (HTTP) e a saída (JSON), mas a **regra de negócio** sai dele e vai para o `Service`. O
> `Model` guarda os dados e a **regra de domínio** da própria entidade. O `DAO` isola o SQL.
> Middleware e Roteador são peças transversais/de despacho.

---

# API 1 — JavaScript (Node.js + Express) — `js-api/`

## Explicação 1 — Mapa das camadas

```
Requisição HTTP
   │
   ▼
Servidor.js ─────────► Roteador ──► Middleware ──► Controle ──► Serviço ──► DAO ──► Banco.js ──► MySQL
(express, CORS,           (URL +        (token,       (só          (regra      (SQL)    (conexão)
 estáticos, DI,            método)       perfil,       orquestra)    de
 erro central)                           corpo, id)                 negócio)
```

| Camada | Pasta | Papel | Não faz |
|---|---|---|---|
| **Roteador** | `src/roteador/*.js` | Casa URL + método com um Controle e diz **em que ordem** os middlewares rodam | Não valida nada, não decide nada |
| **Middleware** | `src/middleware/*.js` | Barreira antes do Controle: token JWT, perfil administrador, `:id` inteiro, corpo com o envelope `{recurso: {...}}` | Não salva, não consulta o banco para regra de negócio |
| **Controle** | `src/controle/*.js` | Lê `params`, `query` e `body`, chama **um** método do Serviço e monta a resposta no envelope `{ success, message, data }` | **Não tem regra de negócio** e não escreve SQL |
| **Serviço** | `src/servico/*.js` | Regra de negócio: duplicidade, vínculo, data no passado, permissão por perfil, valores padrão | Não conhece `request`/`response` |
| **Model** | `src/modelo/*.js` | Dados + **regra de domínio** nos setters (tipo, tamanho, faixa, `perfil`/`status` na lista) | Não sabe de banco nem de HTTP |
| **DAO** | `src/dao/*.js` | Só SQL com `?` (prepared statement) e o nome das colunas | Não valida, não decide |
| **Banco** | `src/database/Banco.js` | Conexão única com o MySQL (`mysql2`) e os métodos `consultar` / `executar` | Não conhece entidade |

A montagem fica em **`Servidor.js`** (injeção de dependência): ele cria `DAOs → Services →
Controles → Routers` e registra tudo nos prefixos `/api/v1/organizadores`, `/eventos`,
`/lotes` e `/auth`. Depois vêm o **servidor de arquivos estáticos** (o frontend é servido
pela própria API) e o **tratador de erro central**.

## Explicação 2 — O caminho de uma requisição

Exemplo real: o frontend tenta cadastrar um evento com título e data que já existem.

```
POST /api/v1/eventos   {"evento": {"titulo": "Expo Inovação", "dataEvento": "2027-05-05T09:00", "idOrganizador": 1}}
```

1. **`Servidor.js`** recebe o `express.json()` (corpo virou objeto), aplica CORS e deixa o
   `EventoRoteador` cuidar do caminho `/eventos`.
2. **`src/roteador/EventoRoteador.js`** — `router.use(validarToken)` roda para **todas** as
   rotas (sem token, para aqui com **401**); em seguida `POST /` chama
   `EventoMiddleware.validarBody` e só depois `EventoControle.store`.
3. **`src/middleware/EventoMiddleware.js`** — confere se veio o objeto `evento` e, no POST, se
   `titulo`, `dataEvento` e `idOrganizador` estão presentes. Faltou algo? **400**.
4. **`src/controle/EventoControle.js`** — monta o objeto de entrada e chama
   `eventoService.createEvento(request.body.evento)`. Não sabe que existe duplicidade.
5. **`src/servico/EventoService.js`** — cria o `Evento` (os setters validam título, data e
   status), verifica se o organizador existe (**400**), chama
   `eventoDAO.existeTituloData(...)` e, se já existir, lança
   `ErrorResponse(409, "Evento duplicado")`.
6. **`src/dao/EventoDAO.js`** — `existeTituloData` roda `SELECT COUNT(*) ... WHERE titulo = ?
   AND data_evento = ?` com o título e a data convertida por `paraSql()`.
7. **`src/database/Banco.js`** — executa a consulta na conexão aberta com o MySQL.
8. O `throw` sobe pelo Serviço → Controle → `asyncHandler` → **tratador de erro do
   `Servidor.js`**, que traduz `ErrorResponse` para
   `{ "success": false, "message": "Evento duplicado", "data": { "detalhes": {...} } }` com
   **409**. O frontend lê `data.detalhes.mensagem` e mostra no toast.

Se tudo estivesse certo, o caminho era o mesmo até o passo 5, e o passo 6 seria
`INSERT INTO eventos (...) VALUES (?, ?, ?, ?, ?)` — o Serviço devolve o `insertId` e o
Controle responde **201**.

## Explicação 3 — Onde mora cada decisão

| Pergunta | Quem responde | Exemplo no código |
|---|---|---|
| “O campo tem o tamanho certo?” | **Model** (domínio) | `Organizador.set nome` → `texto(valor, "nome", 3, 120)` |
| “Esse `status` existe?” | **Model** (domínio) | `Evento.set status` usa `Evento.STATUS` |
| “`disponivel` pode ser maior que o `total`?” | **Model** (domínio) | `Lote.set quantidadeDisponivel` |
| “Esse e-mail já está cadastrado?” | **Serviço** (negócio) | `OrganizadorService.createOrganizador` → `organizadorDAO.existeEmail` → 409 |
| “Esse organizador tem eventos?” | **Serviço** (negócio) | `OrganizadorService.deleteOrganizador` → `countByOrganizador` → 409 |
| “Esse evento já tem lotes?” | **Serviço** (negócio) | `EventoService.deleteEvento` → `loteDAO.countByEvento` → 409 |
| “Um perfil `comum` pode editar outro cadastro?” | **Serviço** (negócio), com o perfil vindo do middleware | `OrganizadorService.updateOrganizador(..., usuarioLogado)` |
| “A data está no passado?” | **Serviço** (negócio, usa `Date.now()`) | `EventoService.#validarRegras` |
| “Existe um `WHERE` para montar?” | **DAO** (persistência) | `EventoDAO.findAll` monta os filtros dinâmicos |
| “Qual status HTTP devolver?” | **Controle** (HTTP) chamando `responder(...)` | `EventoControle.store` → 201 |

Por que não juntar tudo? Porque cada camada pode ser trocada sem mexer nas outras: trocar
MySQL por outro banco mexe só no DAO; mudar a regra (“evento só pode ser criado por admin”)
mexe só no Serviço; mudar a resposta (JSON → outro formato) mexe só no Controle. E o mesmo
Serviço pode ser chamado por outro ponto do sistema sem passar por HTTP.

> **View do MVCS:** o “V” aqui é o frontend (`frontend/`), servido pela própria API. Ele é
> “burro” de propósito: só chama as rotas, guarda o token e mostra o que volta —
> nenhuma regra de negócio é decidida no navegador.

---

# API 2 — Python (Flask) — `flask-api/`

## Explicação 1 — Mapa das camadas

```
Requisição HTTP
   │
   ▼
server.py ─────────► Router (Blueprint) ──► Middleware (decorator) ──► Control ──► Service ──► DAO ──► MySQL
(Flask, CORS,            (URL + método +        (token, perfil,           (só          (regra      (SQL)   (pool de
 estáticos, DI,           ordem dos              id, corpo)                orquestra)    de               conexões)
 errorhandler)            decorators)                                                   negócio)
```

| Camada | Pasta | Papel | Não faz |
|---|---|---|---|
| **Router** | `api/router/*.py` | `Blueprint` da entidade: cada rota declara os **decorators na ordem** (token → perfil → corpo → id) e chama o Control | Não valida, não decide |
| **Middleware** | `api/middleware/*.py` | `@wraps` + `*args/**kwargs`: valida o token (e guarda os claims em `flask.g`), exige `administrador`, checa `<int:id>` e o envelope `{recurso: {...}}` | Não salva nada |
| **Control** | `api/control/*.py` | `request.args` / `view_args` / `get_json()`, chama **um** método do Service e devolve `responder(...)` | **Não tem regra de negócio** |
| **Service** | `api/service/*.py` | Regra de negócio + orquestração das entidades | Não conhece `request` |
| **Model** | `api/model/*.py` | `@property`/`@setter` com regra de domínio; levanta `ValueError` (vira **400**) | Não sabe de banco |
| **DAO** | `api/dao/*.py` | SQL com `%s` dentro de `with get_connection() as conn:` + `commit()` | Não valida |
| **Database** | `api/database/mysql_database.py` | Pool de conexões (`MySQLConnectionPool`) singleton e `get_connection()` | Não conhece entidade |

A montagem fica em **`server.py`**: o `Server.__init__` cria o `Flask`, liga o CORS e os
arquivos estáticos; o `init()` conecta no banco (`MysqlDatabase`) e chama
`__configurar_middlewares()` e `__configurar_modulos()`. É esse segundo método que faz a
**injeção de dependência** dos três módulos: cria os DAOs, passa para os Services, os Services
para os Controls e monta cada Router, registrando os Blueprints em
`/api/v1/auth`, `/api/v1/organizadores`, `/api/v1/eventos` e `/api/v1/lotes`. No fim vem
`__configurar_tratamento_de_erros()`, com o `errorhandler(Exception)` que traduz
`ErrorResponse`, `ValueError`, `IntegrityError` e `HTTPException` para o envelope padrão.

## Explicação 2 — O caminho de uma requisição

Mesmo exemplo: cadastrar um evento com título e data repetidos.

```
POST /api/v1/eventos   {"evento": {"titulo": "Expo Inovação", "dataEvento": "2027-05-05T09:00", "idOrganizador": 1}}
```

1. **`server.py`** — `before_request` imprime o separador no terminal; o Flask resolve a rota
   pelo Blueprint `evento` no prefixo `/api/v1/eventos`.
2. **`api/router/evento_roteador.py`** — a função `store()` tem três decorators, que executam
   **de cima para baixo**: `@jwt.validate_token` → `@evento.validate_body` → corpo da função
   (`evento_control.store()`).
3. **`api/middleware/jwt_middleware.py`** — tira o `Bearer`, valida assinatura/expiração com
   `PyJWT` e guarda os claims em **`flask.g.organizador`**. Token ruim? **401**.
4. **`api/middleware/evento_middleware.py`** — exige `titulo`, `dataEvento` e `idOrganizador`
   dentro do objeto `evento` (`get_json(silent=True)`). Faltou algo? **400**.
5. **`api/control/evento_control.py`** — `store()` chama
   `self.__evento_service.create_evento(dados)` e devolve `responder(201, ...)`. Não conhece
   duplicidade.
6. **`api/service/evento_service.py`** — monta o `Evento` (setters validam), chama
   `__validar_regras`: organizador existe (**400**), data no passado (**400**) e
   `evento_dao.existe_titulo_data(...)` → **409** `Evento duplicado`.
7. **`api/dao/evento_dao.py`** — `SELECT COUNT(*) ... WHERE titulo = %s AND data_evento = %s`
   com os parâmetros em `tuple()`.
8. **`api/database/mysql_database.py`** — pega uma conexão do pool e executa.
9. A exceção sobe até o **`errorhandler` do `server.py`**, que devolve o mesmo envelope da API
   Node (`{success: false, message, data}`) com o status do `ErrorResponse`.

Caminho feliz: até o passo 6 igual; o DAO faz `INSERT INTO eventos (...) VALUES (%s, ...)`,
`cursor.lastrowid` volta como `id_evento` e o Control responde **201**.

## Explicação 3 — Onde mora cada decisão

| Pergunta | Quem responde | Exemplo no código |
|---|---|---|
| “`nome` tem pelo menos 3 caracteres?” | **Model** (domínio) | `api/model/organizador.py` → `@nome.setter` → `texto(...)` |
| “`perfil` é `administrador` ou `comum`?” | **Model** (domínio) | `Organizador.PERFIS` no setter |
| “`quantidade_disponivel` cabe no total?” | **Model** (domínio) | `api/model/lote.py` → `@quantidadeDisponivel.setter` |
| “E-mail já cadastrado?” | **Service** | `organizador_service.create_organizador` → `organizador_dao.existe_email` → 409 |
| “Organizador tem eventos?” | **Service** | `delete_organizador` → `count_by_organizador` → 409 |
| “Evento tem lotes?” | **Service** | `evento_service.delete_evento` → `lote_dao.count_by_evento` → 409 |
| “Perfil `comum` pode mexer neste id?” | **Service** (recebe `g.organizador` do Control) | `organizador_service.update_organizador(id, dados, usuario_logado)` |
| “Data no passado?” | **Service** | `evento_service.__validar_regras` |
| “Como o SQL é montado?” | **DAO** | `evento_dao.find_all` monta os filtros |
| “Qual status HTTP?” | **Control** (`responder(...)`) | `evento_control.store` → 201 |
| “Rota inexistente / método errado?” | **`errorhandler` + rotas** | 404/405 no envelope padrão |

O detalhe que muda em relação ao Node é **como o usuário logado chega ao Serviço**: o
middleware de JWT coloca os claims em **`flask.g`**, o Control lê `g.organizador` e passa
como argumento. O Serviço continua sem conhecer HTTP — recebe um `dict` de claims.

---

# As duas lado a lado

| Peça | Node.js | Flask |
|---|---|---|
| Entrada / servidor | `Servidor.js` (`express`, `express.json`, `express.static`) | `server.py` (`Flask`, `before_request`, estáticos) |
| Despacho | `src/roteador/*.js` (`express.Router`) | `api/router/*.py` (`Blueprint` + decorators) |
| Barreira | `src/middleware/*.js` (função `(req, res, next)`) | `api/middleware/*.py` (decorator com `@wraps`) |
| Orquestração | `src/controle/*.js` (`index/show/store/update/destroy`) | `api/control/*.py` (`index/show/store/update/destroy`) |
| Regra de negócio | `src/servico/*.js` (`createEvento`, `findAll`, …) | `api/service/*.py` (`create_evento`, `find_all`, …) |
| Domínio | `src/modelo/*.js` (get/set + `DomainError`) | `api/model/*.py` (`@property` + `ValueError`) |
| SQL | `src/dao/*.js` (`?`) | `api/dao/*.py` (`%s`) |
| Erro central | tratador de 4 argumentos no `Servidor.js` | `@app.errorhandler(Exception)` no `server.py` |
| Resposta | `responder(res, status, msg, data)` | `responder(status, msg, data)` |
| Usuário logado | `request.organizador` (setado no `JwtMiddleware`) | `flask.g.organizador` |

Mesma arquitetura, mesmas rotas, mesmos status e mesmas mensagens: é por isso que o
**mesmo frontend** funciona nas duas.
