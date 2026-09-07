# PAW 3º BIM — Gestão de Eventos & Ingressos (REST API + JWT + Front HTML/JS)

> **Base nova do 2º BIM** criada em `PAW/3bi/projeto` (não copiada do `PAW/2bi/projeto` RH). Domínio **Eventos** escolhido para originalidade. **Projeto 3º BIM completo**: API protegida por JWT + interfaces visuais HTML/JS funcionais.

## 1. Ficha de Avaliação
- **Arquivo:** `3BIM_PAW_PROJETO.docx` (na raiz do projeto, 37 KB, baixado de helioesperidiao.com)
- **Impressão:** ficha deve ser impressa e apresentada no dia da avaliação (conforme observação da ficha).
- **Entrega código:** todo o código fonte enviado no Classroom um dia antes (pasta `PAW/3bi/projeto`).

### Requisitos da Ficha (10 pontos) — Status
| Pontos | Requisito | Implementação |
|---|---|---|
| 2,0 | Interface visual para **login** funcional com HTML/JS + REST API + **JWT** | `public/login.html` → `POST /auth/login` → salva `token` em `localStorage` → redireciona para `dashboard.html`. Token JWT HS256 com expiração 2h. |
| 2,0 | **Todo recurso da API exige token JWT** | `AuthMiddleware` obrigatório para `/locais`, `/eventos`, `/participantes`, `/ingressos`, `/compras`. Sem `Authorization: Bearer <token>` retorna `401`. Frontend injeta token via `apiFetch`. |
| 2,0 | Interfaces visuais de **cadastro** consumindo API (POST nas 3+ entidades) | `locais.html`, `eventos.html`, `participantes.html`, `ingressos.html`, `compras.html` — cada uma com formulário que faz `POST` com JWT. Excede: 5 entidades. |
| 2,0 | Página para **listar** (GET todas as entidades) | Mesmas páginas acima: tabela carregada via `GET /locais` etc com token. `dashboard.html` também tem prévia com JWT. |
| 2,0 | Interface para **excluir e atualizar** as 3 entidades | Mesmas páginas: cada linha da tabela tem **Editar** (abre modal → `PUT /entidade/{id}`) e **Excluir** (`DELETE /entidade/{id}`) — todos com JWT. |

## 2. Domínio e Banco (5 tabelas relacionadas)

**eventos_db** (`docs/banco.sql`):
```sql
locais (id_local PK, nome UNIQUE, endereco, capacidade)
eventos (id_evento PK, titulo, descricao, data_evento DATETIME, status ENUM('planejado','confirmado','cancelado','realizado'), id_local FK → locais)
participantes (id_participante PK, nome, email UNIQUE, cpf UNIQUE, telefone, senha VARCHAR(255) hasheada)
ingressos (id_ingresso PK, tipo VARCHAR(80), preco DECIMAL, quantidade_total, quantidade_disponivel, id_evento FK → eventos, UNIQUE(tipo+id_evento))
compras (id_compra PK, data_compra DATETIME, quantidade, valor_total DECIMAL, id_participante FK, id_ingresso FK)
```
- Dados de exemplo: 3 locais, 3 eventos, 3 participantes (senha `Senha@123`), 5 ingressos, 3 compras.
- Charset `utf8mb4`, relacionamentos com `ON DELETE CASCADE/RESTRICT`.

**Contas de teste (senha `Senha@123`):**
- `ana@email.com` / `Senha@123` (id 1)
- `bruno@email.com` / `Senha@123` (id 2)
- `carla@email.com` / `Senha@123` (id 3)

## 3. Arquitetura

```
Routes → AuthMiddleware (JWT) → ValidateBody/Id → Controllers → Services → DAOs → Banco
```
- **Routes** (`src/api/Routes/`) mapeiam REST
- **Middlewares** validam JSON e IDs; `AuthMiddleware` valida JWT (exceto `/`, `/auth/login`, `/auth/register`, `*.html/css/js`)
- **Controllers** formatam JSON `{success,message,data}` com status 200/201/401/404/409
- **Services** regras: nome único, FK existe, tipo único por evento, estoque com transação (`CompraService` decrementa `quantidade_disponivel` com `WHERE quantidade_disponivel >= qtd`)
- **DAOs** PDO prepared statements, mapeiam via `Model::fromArray`
- **Models** validam nos setters
- **Server** (`src/api/Server/Server.php`) configura Slim 4, CORS, `addBodyParsing`, `addRouting`, `addErrorMiddleware`

**Config JWT** (`src/api/Config/JwtConfig.php`):
- `SECRET = evento_secret_3bi_2026...`, `ALGO = HS256`, `EXPIRATION = 7200s`, `ISSUER = eventos-api-3bi`

## 4. Como Rodar

### 4.1 Banco
```bash
# XAMPP MariaDB deve estar rodando
C:/xampp/mysql/bin/mysql.exe -u root --default-character-set=utf8mb4 -e "CREATE DATABASE IF NOT EXISTS eventos_db CHARACTER SET utf8mb4;"
C:/xampp/mysql/bin/mysql.exe -u root --default-character-set=utf8mb4 eventos_db < docs/banco.sql
```

### 4.2 Dependências
```bash
cd PAW/3bi/projeto
composer install
# já inclui slim/slim 4.15, slim/psr7, php-di/php-di, firebase/php-jwt 7.1
composer dump-autoload -o
```

### 4.3 Servidor
```bash
php -S 127.0.0.1:8001 -t public public/index.php
# ou
./run  # php -S localhost:8000 -t public public/index.php
```
- API base: `http://127.0.0.1:8001`
- Front: `http://127.0.0.1:8001/login.html` → `dashboard.html` → `locais.html` etc

> `public/index.php` tem guard para `php -S` servir estáticos diretamente (`return false` se arquivo existe).

## 5. API — Autenticação JWT

### Login
```bash
curl -X POST http://127.0.0.1:8001/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"ana@email.com","senha":"Senha@123"}'
# resposta: {success:true, data:{token:"eyJ...", expiresIn:7200, usuario:{idParticipante, nome, email}}}
```

### Uso do Token
Todos os recursos exigem header:
```
Authorization: Bearer <token>
```
```bash
TOKEN=$(curl -s -X POST http://127.0.0.1:8001/auth/login -H "Content-Type: application/json" -d '{"email":"ana@email.com","senha":"Senha@123"}' | php -r '$j=json_decode(file_get_contents("php://stdin"),true); echo $j["data"]["token"];')
curl -H "Authorization: Bearer $TOKEN" http://127.0.0.1:8001/locais
# sem token → 401 {success:false, message:"Token JWT não fornecido..."}
```

### Outros endpoints auth
- `POST /auth/register` — cria participante sem token: `{"nome":"...","email":"...","cpf":"...","telefone":"...","senha":"..."}`
- `GET /auth/me` — valida token e retorna payload (`Authorization: Bearer`)

## 6. API — Endpoints REST (todos com JWT exceto auth)

**Locais**
- `GET /locais` | `GET /locais/count` | `GET /locais/{id}` | `GET /locais/nome/{nome}`
- `POST /locais`  `{ "local": {"nome":"...","endereco":"...","capacidade":500} }`
- `PUT /locais/{id}` | `DELETE /locais/{id}`

**Eventos**
- `GET /eventos` | `GET /eventos/count` | `GET /eventos/{id}` | `GET /eventos/local/{idLocal}`
- `POST /eventos` `{ "evento": {"titulo":"...","descricao":"...","dataEvento":"2026-12-01 19:00:00","status":"planejado","idLocal":1} }`
- `PUT /eventos/{id}` | `DELETE /eventos/{id}`

**Participantes**
- `GET /participantes` | `GET /participantes/{id}`
- `POST /participantes` (com JWT) ou `POST /auth/register` (público)
- `PUT /participantes/{id}` | `DELETE /participantes/{id}`

**Ingressos**
- `GET /ingressos` | `GET /ingressos/evento/{idEvento}`
- `POST /ingressos` `{ "ingresso": {"tipo":"vip","preco":150,"quantidadeTotal":100,"quantidadeDisponivel":100,"idEvento":1} }`
- `PUT /ingressos/{id}` | `DELETE /ingressos/{id}`

**Compras**
- `GET /compras` | `GET /compras/participante/{id}` | `GET /compras/ingresso/{id}`
- `POST /compras` `{ "compra": {"quantidade":2,"idParticipante":1,"idIngresso":1} }` (valor_total calculado)
- `PUT /compras/{id}` | `DELETE /compras/{id}` (devolve estoque)

Ver `endpoints_e_testes.txt` para exemplos completos com JWT e Insomnia.

## 7. Front-End (HTML/JS puro, sem frameworks)

Estrutura `public/`:
```
login.html       — form email/senha → POST /auth/login → localStorage token → dashboard
register.html    — form nome/email/cpf/telefone/senha → POST /auth/register
dashboard.html   — protegido (checkAuth), mostra usuário, cards para cada entidade com links
locais.html      — CRUD completo Locais (form + tabela + editar modal + excluir)
eventos.html     — CRUD Eventos (select de locais)
participantes.html — CRUD Participantes (cpf/phone mask)
ingressos.html   — CRUD Ingressos (select eventos)
compras.html     — CRUD Compras (select participantes/ingressos, cálculo valor)
css/style.css    — design system (≈33 KB, responsivo)
js/api.js        — apiFetch com JWT, helpers por entidade, Api.get alias
js/auth.js       — login, register, checkAuth, logout, displayUser, Auth alias
index.html       — redirect inteligente para login ou dashboard
```

**Tecnologias:** Vanilla JS, `fetch`, `localStorage` (`token` + `usuario`), `Authorization: Bearer`, tratamento 401 → redirect login, alerts, modals, máscaras.

**Fluxo JWT no front:**
1. `login.html` → `Auth.login(email,senha)` → `POST /auth/login` → `localStorage.setItem('token', token)` → `dashboard.html`
2. `js/api.js:apiFetch` injeta `Authorization: Bearer <token>` automaticamente
3. `js/auth.js:checkAuth` nas páginas protegidas verifica token e `GET /auth/me`; se 401 → `login.html`
4. Todas as operações de cadastro/listagem/atualização/exclusão usam `Api.createLocal` etc que chamam `apiFetch` com token

## 8. Demonstração (5 minutos)

1. **Mostrar ficha impressa** `3BIM_PAW_PROJETO.docx` preenchida.
2. **Login:** abrir `http://127.0.0.1:8001/login.html`, logar `ana@email.com / Senha@123`, mostrar token salvo em `localStorage` e redirecionamento.
3. **Listar:** em `locais.html` mostrar tabela carregada via `GET /locais` com header JWT (abrir DevTools Network).
4. **Cadastrar:** preencher formulário “Novo Local” → `POST /locais` com JWT → aparece na tabela.
5. **Atualizar/Excluir:** clicar **Editar** (modal → `PUT`) e **Excluir** (`DELETE`) — tudo com JWT.
6. **Sem token:** `curl http://127.0.0.1:8001/locais` → 401, provando proteção.

## 9. Diferença do 2º BIM
| 2º BIM (`PAW/2bi/projeto`) | 3º BIM (`PAW/3bi/projeto` este) |
|---|---|
| Domínio RH | Domínio Eventos (original) |
| Sem JWT, sem front | Com JWT + front completo |
| Código antigo | Código reescrito do zero |

## 10. Entrega
- Código em `PAW/3bi/projeto` (este diretório) — enviar ZIP ou Git para Classroom um dia antes
- Ficha impressa no dia
- Banco populado (`docs/banco.sql` já importado)

---
**Rodar agora:** `php -S 127.0.0.1:8001 -t public public/index.php` → `http://127.0.0.1:8001/login.html`
