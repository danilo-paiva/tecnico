# Documentação da API — Gestão de Eventos e Ingressos

Base: `http://localhost:8080` — todas as respostas seguem
`{success, message, data}` e os erros `{success: false, message, error}`.

## Locais

```bash
curl http://localhost:8080/locais
curl http://localhost:8080/locais/1
curl http://localhost:8080/locais/count
curl -X POST http://localhost:8080/locais -H "Content-Type: application/json" \
  -d '{"local":{"nome":"Ginasio Central","endereco":"Rua A, 100","capacidade":800}}'
curl -X PUT http://localhost:8080/locais/1 -H "Content-Type: application/json" \
  -d '{"local":{"nome":"Ginasio Central","endereco":"Rua A, 101","capacidade":900}}'
curl -X DELETE http://localhost:8080/locais/1
```

## Eventos (`status`: planejado, confirmado, cancelado, realizado)

```bash
curl http://localhost:8080/eventos
curl http://localhost:8080/eventos/1
curl http://localhost:8080/eventos/count
curl -X POST http://localhost:8080/eventos -H "Content-Type: application/json" \
  -d '{"evento":{"titulo":"Show de Rock","descricao":"Banda local","data_evento":"2026-12-10 20:00:00","status":"planejado","id_local":2}}'
curl -X PUT http://localhost:8080/eventos/1 -H "Content-Type: application/json" \
  -d '{"evento":{"titulo":"Show de Rock","data_evento":"2026-12-10 20:00:00","status":"confirmado","id_local":2}}'
curl -X DELETE http://localhost:8080/eventos/1
```

## Ingressos (estoque cheio na criação; `quantidade_disponivel` é da API)

```bash
curl http://localhost:8080/ingressos
curl http://localhost:8080/ingressos/1
curl http://localhost:8080/ingressos/count
curl -X POST http://localhost:8080/ingressos -H "Content-Type: application/json" \
  -d '{"ingresso":{"tipo":"Camarote","preco":200,"quantidade_total":50,"id_evento":1}}'
curl -X PUT http://localhost:8080/ingressos/1 -H "Content-Type: application/json" \
  -d '{"ingresso":{"tipo":"Inteira","preco":90,"quantidade_total":200,"id_evento":1}}'
curl -X DELETE http://localhost:8080/ingressos/1
```

## Participantes (senha com hash, fora do JSON)

```bash
curl http://localhost:8080/participantes
curl http://localhost:8080/participantes/1
curl http://localhost:8080/participantes/count
curl -X POST http://localhost:8080/participantes -H "Content-Type: application/json" \
  -d '{"participante":{"nome":"Diego","email":"diego@email.com","cpf":"444.555.666-77","telefone":"(47) 90000-0000","senha":"secreta123"}}'
curl -X PUT http://localhost:8080/participantes/1 -H "Content-Type: application/json" \
  -d '{"participante":{"nome":"Diego","email":"diego@email.com","cpf":"444.555.666-77","senha":"nova123"}}'
curl -X DELETE http://localhost:8080/participantes/1
```

## Compras (`valor_total` calculado; baixa o estoque em transação)

```bash
curl http://localhost:8080/compras
curl http://localhost:8080/compras/1
curl http://localhost:8080/compras/count
curl -X POST http://localhost:8080/compras -H "Content-Type: application/json" \
  -d '{"compra":{"id_participante":1,"id_ingresso":1,"quantidade":2}}'
curl -X PUT http://localhost:8080/compras/1 -H "Content-Type: application/json" \
  -d '{"compra":{"id_participante":1,"id_ingresso":1,"quantidade":3}}'
curl -X DELETE http://localhost:8080/compras/1
```

## Códigos HTTP

| Código | Quando |
|---|---|
| 200 | busca, atualização, exclusão e contagem ok |
| 201 | cadastro ok |
| 400 | validação, duplicado, sem estoque, vínculo que impede excluir |
| 404 | id não existe / FK não existe |
| 500 | erro interno |
