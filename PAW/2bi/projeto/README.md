# Projeto 2º Bimestre — PAW — API de Gestão de Eventos e Ingressos

REST API em Slim 4 + MySQL com 5 tabelas relacionadas, CRUD completo,
regras de negócio e respostas JSON padronizadas.

## Requisitos

- PHP 8.1+ com extensão `pdo_mysql`
- MySQL/MariaDB (XAMPP)
- Composer

## Como rodar

1. Importe o banco (cria `eventos_db` com dados de exemplo):
   `mysql -u root < docs/banco.sql`
2. Instale as dependências:
   `composer install`
3. Suba o servidor:
   `composer start` (ou `php -S localhost:8080 -t public`)
4. Teste: `GET http://localhost:8080/locais`

## Endpoints (todos devolvem `{success, message, data}`)

| Recurso | Listar | Buscar | Criar | Atualizar | Excluir | Contar |
|---|---|---|---|---|---|---|
| locais | GET /locais | GET /locais/{id} | POST /locais | PUT/PATCH /locais/{id} | DELETE /locais/{id} | GET /locais/count |
| eventos | GET /eventos | GET /eventos/{id} | POST /eventos | PUT/PATCH /eventos/{id} | DELETE /eventos/{id} | GET /eventos/count |
| ingressos | GET /ingressos | GET /ingressos/{id} | POST /ingressos | PUT/PATCH /ingressos/{id} | DELETE /ingressos/{id} | GET /ingressos/count |
| participantes | GET /participantes | GET /participantes/{id} | POST /participantes | PUT/PATCH /participantes/{id} | DELETE /participantes/{id} | GET /participantes/count |
| compras | GET /compras | GET /compras/{id} | POST /compras | PUT/PATCH /compras/{id} | DELETE /compras/{id} | GET /compras/count |

Detalhes e exemplos em `API.md`. Coleção pronta em `docs/insomnia.json`
(Insomnia → Import).

## Banco (`docs/banco.sql`)

`locais (1) → eventos (N) → ingressos (N)` e
`participantes (1) → compras (N) ← ingressos (1)`:

- **locais**: id_local, nome (UNIQUE), endereco, capacidade
- **eventos**: id_evento, titulo, descricao, data_evento, status, id_local (FK)
- **ingressos**: id_ingresso, tipo, preco, quantidade_total,
  quantidade_disponivel, id_evento (FK, UNIQUE tipo+evento)
- **participantes**: id_participante, nome, email (UNIQUE), cpf (UNIQUE),
  telefone, senha (hash)
- **compras**: id_compra, data_compra, quantidade, valor_total,
  id_participante (FK), id_ingresso (FK)

## Regras de negócio (camada Service + DAO)

- Nome de local único; evento único por título+data; tipo de ingresso
  único por evento; email e CPF únicos por participante.
- Chave estrangeira precisa existir (local do evento, evento do ingresso,
  participante e ingresso da compra).
- Compra calcula `valor_total = preco × quantidade` e baixa o estoque em
  transação; sem estoque, a compra é recusada e nada é gravado.
- Alterar a quantidade da compra ajusta o estoque; excluir a compra
  devolve o estoque; compra não pode ser transferida de dono/ingresso.
- Não é permitido excluir registro com vínculo (local com eventos, evento
  com ingressos, ingresso/participante com compras) nem reduzir o total
  do ingresso abaixo do já vendido.
- Senha do participante é gravada com hash e nunca aparece nas respostas.

## Estrutura

```
public/index.php            entrada (guarda arquivos estáticos no php -S)
src/api/Routes/             URLs → Controller + Middlewares
src/api/Middlewares/        valida body (ValidateXBody) e id (ValidateXId)
src/api/Controllers/        HTTP: lê pedido, devolve JSON
src/api/Services/           regras de negócio
src/api/DAO/                SQL
src/api/Models/             entidades + validações + JSON
src/api/Database/           conexão PDO
src/api/Http/               erro padrão com status HTTP
src/api/Server/             middlewares globais, rotas e erros
docs/banco.sql              banco + dados de exemplo
docs/insomnia.json          coleção do Insomnia
API.md                      exemplos de requisição
```
