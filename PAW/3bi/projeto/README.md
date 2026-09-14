# Projeto 3º Bimestre — PAW — Gestão de Eventos com JWT + Frontend

Continuação do projeto do 2º bimestre (API REST Slim 4 + MySQL), acrescentando o que a
**ficha do 3º bimestre** pede:

| Item da ficha | Onde está | Valor |
|---|---|---|
| Interface visual de login funcional (HTML/JS + REST + JWT) | `public/login.html` + `POST /login` | 2,0 |
| Todo recurso da API exige o token JWT | `ValidateParticipanteToken` em todas as rotas (menos `/login`) | 2,0 |
| Interfaces de cadastro via API (POST nas entidades) | formulário no topo de cada página (`locais`, `eventos`, `ingressos`, `participantes`, `compras`) | 2,0 |
| Página(s) de listagem (GET de todas as entidades) | tabelas em cada página + `dashboard.html` com os contadores | 2,0 |
| Interface para excluir e atualizar as entidades | botões **Editar** (PUT) e **Excluir** (DELETE) em cada linha | 2,0 |

> O projeto do 2º bi tinha 5 entidades; o frontend cobre **as 5** (supera o mínimo de 3).

## Requisitos

- PHP 8.1+ com extensão `pdo_mysql`
- MySQL/MariaDB (XAMPP) — banco `eventos_db`
- Composer

## Como rodar

1. Importe o banco (recria `eventos_db` com dados de exemplo):
   `mysql -u root < docs/banco.sql`
2. Instale as dependências:
   `composer install`
3. Suba o servidor (API **e** frontend na mesma origem):
   `composer start` (ou `php -S localhost:8080 -t public`)
4. Abra no navegador: `http://localhost:8080/login.html`
5. Entre com o usuário do seed: **ana@email.com** / **123456** (administradora).
   Para ver o perfil comum: `bruno@email.com` / `123456`.

## Login + JWT (resumo do fluxo)

1. O navegador envia `POST /login` com `{"participante": {"email", "senha"}}`.
2. A API confere a senha com `password_verify` e devolve `{"participante", "token"}`.
3. O frontend guarda o token no `localStorage` (`js/auth.js`).
4. Toda chamada seguinte envia `Authorization: Bearer <token>` (`js/ApiService.js`).
5. Sem token, ou com token inválido/expirado, a API responde `401`.
6. O token dura 30 dias e carrega `{id_participante, nome, email, perfil}` (claims públicas).

## Perfis (aula paw03x01 — ValidateAdministrador)

| Perfil | Leitura (GET) | Escrita (POST/PUT/PATCH/DELETE) |
|---|---|---|
| `comum` | ✅ com token | ❌ `403`, exceto **comprar** (`POST /compras` ✅) |
| `administrador` | ✅ com token | ✅ com token |

Comprar ingresso (`POST /compras`) é a ação do usuário comum, então vale para
todo logado; editar/excluir compra continua só de admin. No frontend, o comum
vê o formulário de compra mas não os botões Editar/Excluir (a API barra com
403 de qualquer jeito).
Um admin promove/rebaixa pelo campo `perfil` em `participantes.html`.
Banco novo: `docs/banco.sql` já vem com a coluna; banco antigo: rode
`docs/migracao-admin.sql`.

Detalhes e exemplos `curl` em `API.md`. Coleção do Insomnia em
`docs/insomnia.json` (pasta **Auth (JWT)** para o login; demais pastas já
enviam `Authorization: Bearer {{ _.token }}` — cole o token na variável
`token` do ambiente).

## Testes

- PHPUnit (unitários, sem precisar do banco): `composer test`
- Cobertura: `MeuTokenJWT` (gerar/validar/perfil/expirado/adulterado), `Models`
  (validações, perfil e senha fora do JSON), middlewares (token + body do login +
  admin 403/401) e `ParticipanteService::loginService` (sucesso/401 com DAO simulado).
- Roteiro manual do frontend em `docs/roteiro-testes.md`.

## Estrutura (o que mudou em relação ao 2º bi)

```
public/login.html, dashboard.html, locais|eventos|ingressos|participantes|compras.html
public/css/style.css                design system do projeto anterior (git e1d71da^)
                                + camada de compatibilidade p/ o HTML atual
public/js/config.js                 URL base da API
public/js/ApiService.js             aula paw03x04: GET/POST/PUT/DELETE com Bearer
public/js/auth.js                   guarda token, protege paginas, trata 401
src/api/Http/MeuTokenJWT.php        aula paw03x01: gerar/validar JWT
src/api/Middlewares/Participante/ValidateParticipanteToken.php  barreira 401
src/api/Middlewares/Participante/ValidateParticipanteLoginBody.php  valida body do login
src/api/Middlewares/Participante/ValidateAdministrador.php  barreira 403 (aula paw03x01)
src/api/Routes/AuthRouter.php       POST /login e POST /participantes/login (publicas)
src/api/Routes/*Router.php          GET exige Bearer; escrita exige admin (403)
src/api/DAO/ParticipanteDAO.php     + verificarLogin() com password_verify
src/api/Services/ParticipanteService.php  + loginService() que gera o JWT
src/api/Controllers/ParticipanteController.php  + loginController()
tests/                              PHPUnit (38 testes)
docs/banco.sql                      seed com senha 123456 + perfil (ana admin)
docs/migracao-admin.sql             ALTER + UPDATE para banco já importado
docs/insomnia.json                  coleção atualizada (Auth + Bearer)
docs/roteiro-testes.md              passo a passo da apresentação
API.md                              endpoints + auth + exemplos
```
