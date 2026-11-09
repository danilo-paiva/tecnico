# Roteiro de testes — PAW 4º bimestre

Dois níveis de teste, ambos executados na entrega:

1. **Contrato das APIs** — script automatizado, roda igual nas duas linguagens.
2. **Frontend no navegador** — checklist manual (login, sessão, CRUD, filtros, troca de API,
   permissões).

Antes de testar: banco importado (`docs/banco.sql`), API Node na porta 3000 e API Flask na
porta 8080 rodando.

---

## 1. Teste automatizado das APIs

```bash
# API JavaScript (Node.js)
BASE=http://localhost:3000/api/v1 node docs/testes/teste-api.js

# API Flask
BASE=http://localhost:8080/api/v1 node docs/testes/teste-api.js
```

No **PowerShell**:

```powershell
$env:BASE='http://localhost:3000/api/v1'; node docs/testes/teste-api.js
$env:BASE='http://localhost:8080/api/v1'; node docs/testes/teste-api.js
```

Cada execução faz **33 verificações** e limpa tudo que criou (o banco volta ao seed).
Saída esperada nas duas:

```
TODOS OS TESTES PASSARAM
```

### O que é verificado

| # | Verificação | Esperado |
|---|---|---|
| 1 | GET sem token | 401 |
| 2 | Login com senha errada | 401 |
| 3 | Login admin (`ana@email.com`) | 200 + token |
| 4 | Login não devolve hash de senha | sem `$2b$` no corpo |
| 5 | GET organizadores | 3 |
| 6 | Filtro `perfil=administrador` | 1 |
| 7 | Filtro `nome=bruno` | 1 |
| 8 | Filtro eventos `status=confirmado` | 2 |
| 9 | Filtro lotes `precoMax=100` | todos ≤ 100 |
| 10 | Filtro lotes `idEvento=1` | só do evento 1 |
| 11 | Criar evento com data no passado | 400 |
| 12 | Criar evento válido | 201 |
| 13 | Criar evento repetido (título + data) | 409 |
| 14 | PUT evento (status) | 200 |
| 15 | Criar lote sem `quantidadeDisponivel` | 201 e disponível = total |
| 16 | Criar lote repetido no mesmo evento | 409 |
| 17 | Lote com `disponivel > total` | 400 |
| 18 | Excluir evento que tem lote | 409 |
| 19 | Excluir lote | 200 |
| 20 | Excluir evento | 200 |
| 21 | Excluir organizador com eventos | 409 |
| 22 | Login perfil comum (`bruno@email.com`) | 200 |
| 23 | Comum excluindo organizador | 403 |
| 24 | Comum criando organizador | 403 |
| 25 | Comum editando outro organizador | 403 |
| 26 | Comum editando o próprio cadastro | 200 |
| 27 | Admin criando organizador (perfil administrador) | 201 |
| 28 | Admin excluindo organizador sem eventos | 200 |
| 29 | Cadastro público (`/auth/registrar`) | 201 + token |
| 30 | Cadastro público força perfil `comum` | perfil = comum |
| 31 | `GET /auth/perfil` com token do cadastro | 200 |
| 32 | Limpeza do usuário criado | 200 |
| 33 | Rota inexistente | 404 no envelope JSON |

Resultado da última execução (26/10/2026): **33/33 OK nas duas APIs**.

---

## 2. Frontend no navegador

Abre-se `http://localhost:3000/login.html` (API Node) ou `http://localhost:8080/login.html`
(API Flask). O seletor **Node / Flask** troca a API em uso.

### 2.1 Login e sessão

- [x] Login `ana@email.com` / `123456` → entra no dashboard, mostra “Olá, Ana” e o perfil
      `administrador` no topo.
- [x] Senha errada → mensagem de erro, sem entrar.
- [x] Aba **Criar conta** → cadastro cria usuário com perfil **comum** e já entra.
- [x] Token guardado no `localStorage` (`pulse.token`) e enviado como `Bearer` em toda chamada.
- [x] Token inválido/expirado → volta para `login.html?sessao=expirada` com o aviso
      “Sua sessão expirou. Entre novamente.”
- [x] Botão **Sair** limpa a sessão e volta ao login.

### 2.2 Dashboard (`index.html`)

- [x] Contadores do seed: **4 eventos**, **7 lotes**, **1.234 ingressos disponíveis**.
- [x] Resumo por status, lotes gratuitos e próximos eventos com dados reais da API.
- [x] Indicador da API em uso (“Node.js + Express” / “Python + Flask”).

### 2.3 Organizadores (`organizadores.html`)

- [x] Lista 3 registros do seed (nome, e-mail, telefone, perfil, data).
- [x] Filtros: `nome`, `e-mail`, `perfil=comum` → 2.
- [x] Criar organizador → 201, aparece na lista, toast “Organizador cadastrado.”
- [x] E-mail repetido → 409, modal aberto com o motivo.
- [x] Editar outro organizador (admin) → 200.
- [x] Excluir organizador **com eventos** (Carla) → 409
      “Exclua ou transfira os 1 evento(s) antes de remover este organizador.”
- [x] Excluir organizador sem eventos → 200.
- [x] Perfil **comum**: botão “+ Novo organizador” oculto, campo `perfil` desabilitado,
      apenas a própria linha editável, botão excluir desabilitado e, mesmo forçando a chamada
      direta, a API responde **403**.

### 2.4 Eventos (`eventos.html`)

- [x] Lista 4 eventos com data, organizador (join), status e descrição.
- [x] Filtros: `título=startup` → 1 · `status=confirmado` → 2 · `organizador=Bruno` → 1 ·
      `de=2027-05-01` → 2 · **Limpar** volta para 4.
- [x] Criar evento pelo modal → 201, aparece na lista, toast “Evento cadastrado.”
- [x] Data no passado → modal permanece aberto com a mensagem da API (400).
- [x] Título + data repetidos → 409 “Já existe um evento com este título nesta data/hora.”
- [x] Editar evento (título/status) → 200 e lista atualizada.
- [x] Excluir evento **com lotes** → 409 “Exclua os 2 lote(s) deste evento antes de removê-lo.”
- [x] Excluir evento sem lotes → 200 e lista atualizada.
- [x] Atalho “Ver lotes deste evento” abre `lotes.html?idEvento=N` já filtrado.

### 2.5 Lotes (`lotes.html`)

- [x] Lista 7 lotes com evento, preço e barra de ocupação (`disponivel / total`).
- [x] Filtros: `nome`, `evento`, faixa de preço (100–200 → 2 lotes) e **Limpar**.
- [x] Criar lote sem informar disponível → 201 e `disponivel = total` (50 de 50).
- [x] `disponivel > total` → 400 “A quantidade disponível não pode passar da total.”
- [x] Nome repetido no mesmo evento → 409.
- [x] Editar preço/disponível → 200; ao reduzir o total, a barra de ocupação reflete a mudança.
- [x] Excluir lote → 200 e lista atualizada.

### 2.6 As duas APIs no mesmo frontend

- [x] Trocar **Node → Flask** na barra lateral: a página recarrega, mostra “Python + Flask” e
      os **mesmos dados** (mesmo banco).
- [x] O token emitido pela API Node continua válido na API Flask (mesma chave JWT) — nenhum
      novo login é pedido.
- [x] CRUD e filtros repetidos na API Flask com os mesmos resultados
      (criar/editar/excluir evento e lote, filtros por status e faixa de preço).
- [x] `GET /api/v1/eventos` nas duas APIs devolve os mesmos 4 eventos.
- [x] Permissões iguais nas duas: comum recebe **403** ao criar organizador e ao editar outro.

### 2.7 Layout

- [x] Desktop (1440×900): sidebar fixa, grids de 3 colunas, tabelas sem quebra — prints em
      `docs/01-login.png` a `docs/07-login-criar-conta.png`.
- [x] Mobile 390×844 (iPhone 12 Pro emulado): a sidebar vira **cabeçalho** com marca,
      seletor Node/Flask e navegação em **4 colunas** (ícone + rótulo); cards e filtros
      empilham em 1–2 colunas e a página não rola na horizontal
      (`scrollWidth = clientWidth = 390`). A tabela rola **dentro do próprio card**
      (`table-wrap` com `overflow-x: auto` + dica “Deslize a tabela…”) — prints em
      `docs/08-mobile-login.png` a `docs/12-mobile-eventos-tabela.png`.
- [x] Nenhum erro de console, de página ou de rede nas 4 telas (desktop e mobile).

---

## 3. Roteiro para o dia da apresentação (ordem sugerida)

1. Subir o banco, a API Node (3000) e a API Flask (8080).
2. Mostrar `docs/testes/teste-api.js` rodando **nas duas** APIs (`TODOS OS TESTES PASSARAM`).
3. Insomnia aberto com `docs/insomnia.json`: mostrar login, GET com filtro, POST, PUT e DELETE
   (itens 1 e 2 da ficha podem ser apresentados por aqui).
4. Navegador em `http://localhost:3000/login.html`: login, dashboard, CRUD com filtros nas três
   telas, troca para Flask e repetir uma operação (item 3).
5. Demonstrar erros: data no passado, título duplicado, excluir evento com lotes, comum tentando
   criar organizador.
