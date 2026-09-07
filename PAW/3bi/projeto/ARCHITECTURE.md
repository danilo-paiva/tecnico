# Arquitetura — Gestão de Eventos & Ingressos

Arquitetura em **Camadas** (mesma filosofia do projeto RH do 2º bi, porém código 100% reescrito):

`Rota` → `Middleware` → `Controller` → `Service` → `DAO` → `Banco`

## Camadas

### 1. Routes (`src/api/Routes`)
Mapeiam URLs e métodos HTTP para Controllers. Ex: `GET /locais` → `LocalController::findAll`.

### 2. Middlewares (`src/api/Middlewares`)
Interceptam antes do Controller. Validam formato do JSON, presença de campos obrigatórios, IDs numéricos. Lançam `ErrorResponse(400)` se inválido, impedindo que dados sujos cheguem ao Controller. Normalizam `parsedBody` para payload plano (`{"local":{...}}` → `{...}`).

### 3. Controllers (`src/api/Controllers`)
Maestros da requisição. Extraem `body`/`args`, chamam Service, formatam JSON padronizado `{success,message,data}` com status HTTP correto (200, 201, 404, 409, 500). Não contêm regra de negócio.

### 4. Services (`src/api/Services`)
Onde mora a **regra de negócio**. Orquestram DAOs, verificam duplicidades (nome, email, CPF, tipo), validam FKs existem, calculam `valor_total`, controlam estoque com **transação PDO** (CompraService). Lançam `ErrorResponse` com código apropriado.

### 5. DAOs (`src/api/Dao`)
Únicos a falar SQL. Executam `SELECT/INSERT/UPDATE/DELETE` com prepared statements, mapeiam rows para Models via `Model::fromArray`. Isolam SQL do resto.

### 6. Models (`src/api/Models`)
Representam entidades. Validam atributos nos setters (`InvalidArgumentException`), implementam `JsonSerializable` + `toArray()`/`fromArray()`. Ex: `Local::setCapacidade()` valida >0 e ≤1M.

### 7. Database (`src/api/Database/MysqlDatabase`)
Singleton PDO. Recebe config por construtor (host, user, password, database, port). DSN `mysql:host=...;port=...;dbname=...;charset=utf8mb4` com `ERRMODE_EXCEPTION`.

### 8. Server (`src/api/Server/Server`)
Configura Slim 4: `addBodyParsingMiddleware`, CORS, `addRoutingMiddleware`, registra todas as rotas, `addErrorMiddleware` que converte `ErrorResponse` e `HttpNotFoundException` em JSON.

## Fluxo exemplo: POST /compras
1. **Rota** identifica `POST /compras` → `CompraController::create` + `ValidateCompraBody`.
2. **Middleware** valida que `compra.quantidade`, `idParticipante`, `idIngresso` existem e são >0.
3. **Controller** extrai payload plano, chama `CompraService::create`.
4. **Service** verifica participante e ingresso existem (404), verifica estoque (409), calcula `valor_total`, abre transação, decrementa `quantidade_disponivel` com `WHERE quantidade_disponivel >= :qtd`, insere compra, commit.
5. **DAO** executa `INSERT INTO compras ...` e `UPDATE ingressos SET quantidade_disponivel = quantidade_disponivel - :qtd ...`.
6. **Resposta** volta com JSON `201` e compra criada.

## Diferenças para o projeto RH
- Domínio trocado (RH → Eventos) para garantir originalidade.
- Código reescrito do zero, nomes e mensagens diferentes, validações ajustadas (ex: ingresso tipo livre, evento status ampliado).
- Mesma organização garante familiaridade para avaliação, mas sem cópia.

## Pronto para 3º BIM (JWT implementado)
JWT implementado no padrão da Aula 1: `firebase/php-jwt` + `Config/JwtConfig`
(SECRET, HS256, iss/aud/sub, expiração) + `Http/MeuTokenJWT` (`gerarToken`,
`validateToken`, `getPayload`) + `Middlewares/AuthMiddleware` global
(equivale ao `ValidateFuncionarioToken`) + `Middlewares/ValidateParticipanteToken`
e `ValidateAdministrador` por rota (ordem da aula: Body → Administrador → Token).
Front usa `js/api.js` (`apiFetch` com Bearer) e `js/ApiService.js` (classe ES6 da Aula 4).
