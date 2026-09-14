# Documentação da API — Gestão de Eventos e Ingressos (3º bi)

Base: `http://localhost:8080` — todas as respostas seguem
`{success, message, data}` e os erros `{success: false, message, error}`.

## Autenticação (JWT)

Tela de login: `GET /login.html` (usuário do seed: **ana@email.com** / **123456**).

```bash
# 1. Login (rota PUBLICA — não pede token)
curl -X POST http://localhost:8080/login -H "Content-Type: application/json" \
  -d '{"participante":{"email":"ana@email.com","senha":"123456"}}'
# → {"success":true,"message":"Login realizado com sucesso",
#    "data":{"participante":{"id_participante":1,"nome":"Ana Souza","email":"ana@email.com"},
#            "token":"eyJ0eXAiOi..."}}
# (atalho no mesmo formato: POST /participantes/login)

# 2. Guarde o token e use em TUDO (ficha item 2: qualquer recurso exige JWT)
TOKEN="eyJ0eXAiOi..."
curl http://localhost:8080/locais -H "Authorization: Bearer $TOKEN"
```

Sem token → `401 {"success":false,"message":"Acesso não autorizado",...}`.
Token inválido/expirado → `401`. Body de login inválido → `400`
(`{"participante": {"email", "senha"}}` é obrigatório).
Login com credencial errada → `401 {"success":false,"message":"Usuário ou senha inválidos"}`.
Perfil comum tentando escrita → `403 {"success":false,"message":"Acesso negado",...}`
(só `administrador` pode POST/PUT/PATCH/DELETE; GET vale para todo logado.
Exceção: `POST /compras` (comprar) vale para todo logado.)

## Locais (com `Authorization: Bearer $TOKEN` em todas)

```bash
curl http://localhost:8080/locais -H "Authorization: Bearer $TOKEN"
curl http://localhost:8080/locais/1 -H "Authorization: Bearer $TOKEN"
curl http://localhost:8080/locais/count -H "Authorization: Bearer $TOKEN"
curl -X POST http://localhost:8080/locais -H "Content-Type: application/json" -H "Authorization: Bearer $TOKEN" \
  -d '{"local":{"nome":"Ginasio Central","endereco":"Rua A, 100","capacidade":800}}'
curl -X PUT http://localhost:8080/locais/1 -H "Content-Type: application/json" -H "Authorization: Bearer $TOKEN" \
  -d '{"local":{"nome":"Ginasio Central","endereco":"Rua A, 101","capacidade":900}}'
curl -X DELETE http://localhost:8080/locais/1 -H "Authorization: Bearer $TOKEN"
```

## Eventos (`status`: planejado, confirmado, cancelado, realizado)

```bash
curl http://localhost:8080/eventos -H "Authorization: Bearer $TOKEN"
curl http://localhost:8080/eventos/1 -H "Authorization: Bearer $TOKEN"
curl http://localhost:8080/eventos/count -H "Authorization: Bearer $TOKEN"
curl -X POST http://localhost:8080/eventos -H "Content-Type: application/json" -H "Authorization: Bearer $TOKEN" \
  -d '{"evento":{"titulo":"Show de Rock","descricao":"Banda local","data_evento":"2026-12-10 20:00:00","status":"planejado","id_local":2}}'
curl -X PUT http://localhost:8080/eventos/1 -H "Content-Type: application/json" -H "Authorization: Bearer $TOKEN" \
  -d '{"evento":{"titulo":"Show de Rock","data_evento":"2026-12-10 20:00:00","status":"confirmado","id_local":2}}'
curl -X DELETE http://localhost:8080/eventos/1 -H "Authorization: Bearer $TOKEN"
```

## Ingressos (estoque cheio na criação; `quantidade_disponivel` é da API)

```bash
curl http://localhost:8080/ingressos -H "Authorization: Bearer $TOKEN"
curl http://localhost:8080/ingressos/1 -H "Authorization: Bearer $TOKEN"
curl http://localhost:8080/ingressos/count -H "Authorization: Bearer $TOKEN"
curl -X POST http://localhost:8080/ingressos -H "Content-Type: application/json" -H "Authorization: Bearer $TOKEN" \
  -d '{"ingresso":{"tipo":"Camarote","preco":200,"quantidade_total":50,"id_evento":1}}'
curl -X PUT http://localhost:8080/ingressos/1 -H "Content-Type: application/json" -H "Authorization: Bearer $TOKEN" \
  -d '{"ingresso":{"tipo":"Inteira","preco":90,"quantidade_total":200,"id_evento":1}}'
curl -X DELETE http://localhost:8080/ingressos/1 -H "Authorization: Bearer $TOKEN"
```

## Participantes (senha com hash, fora do JSON)

```bash
curl http://localhost:8080/participantes -H "Authorization: Bearer $TOKEN"
curl http://localhost:8080/participantes/1 -H "Authorization: Bearer $TOKEN"
curl http://localhost:8080/participantes/count -H "Authorization: Bearer $TOKEN"
curl -X POST http://localhost:8080/participantes -H "Content-Type: application/json" -H "Authorization: Bearer $TOKEN" \
  -d '{"participante":{"nome":"Diego","email":"diego@email.com","cpf":"444.555.666-77","telefone":"(47) 90000-0000","senha":"secreta123"}}'
curl -X PUT http://localhost:8080/participantes/1 -H "Content-Type: application/json" -H "Authorization: Bearer $TOKEN" \
  -d '{"participante":{"nome":"Diego","email":"diego@email.com","cpf":"444.555.666-77","senha":"nova123"}}'
curl -X DELETE http://localhost:8080/participantes/1 -H "Authorization: Bearer $TOKEN"
```

## Compras (`valor_total` calculado; baixa o estoque em transação)

```bash
curl http://localhost:8080/compras -H "Authorization: Bearer $TOKEN"
curl http://localhost:8080/compras/1 -H "Authorization: Bearer $TOKEN"
curl http://localhost:8080/compras/count -H "Authorization: Bearer $TOKEN"
curl -X POST http://localhost:8080/compras -H "Content-Type: application/json" -H "Authorization: Bearer $TOKEN" \
  -d '{"compra":{"id_participante":1,"id_ingresso":1,"quantidade":2}}'
curl -X PUT http://localhost:8080/compras/1 -H "Content-Type: application/json" -H "Authorization: Bearer $TOKEN" \
  -d '{"compra":{"id_participante":1,"id_ingresso":1,"quantidade":3}}'
curl -X DELETE http://localhost:8080/compras/1 -H "Authorization: Bearer $TOKEN"
```

## Códigos HTTP

| Código | Quando |
|---|---|
| 200 | busca, atualização, exclusão, contagem e login ok |
| 201 | cadastro ok |
| 400 | validação, duplicado, sem estoque, vínculo que impede excluir |
| 401 | sem token, token inválido/expirado, login errado |
| 403 | perfil comum tentando escrita (só admin; exceção: `POST /compras`) |
| 404 | id não existe / FK não existe |
| 500 | erro interno |
