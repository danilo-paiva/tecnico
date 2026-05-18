# 📡 Documentação da API

Documentação completa dos endpoints disponíveis na API de Gestão de RH.

---

## 🌐 URL Base

```
http://localhost:8080
```

---

## 📦 Cargos

### 1. Listar Todos os Cargos

**Endpoint:** `GET /cargos`

**Resposta (200 OK):**
```json
{
  "success": true,
  "message": "Busca realizada com sucesso",
  "data": {
    "cargos": [
      {
        "idCargo": 1,
        "nomeCargo": "Desenvolvedor"
      },
      {
        "idCargo": 2,
        "nomeCargo": "Gerente"
      }
    ]
  }
}
```

**Exemplo cURL:**
```bash
curl -X GET http://localhost:8080/cargos
```

### 2. Buscar Cargo por ID

**Endpoint:** `GET /cargos/{idCargo}`

**Parâmetros de URL:**
- `idCargo` (int obrigatório)

**Resposta (200 OK):**
```json
{
  "success": true,
  "message": "Executado com sucesso",
  "data": {
    "cargos": {
      "idCargo": 1,
      "nomeCargo": "Desenvolvedor"
    }
  }
}
```

**Exemplo cURL:**
```bash
curl -X GET http://localhost:8080/cargos/1
```

### 3. Criar Cargo

**Endpoint:** `POST /cargos`

**Cabeçalhos:**
- `Content-Type: application/json`

**Corpo da Requisição:**
```json
{
  "cargo": {
    "nomeCargo": "Desenvolvedor Sênior"
  }
}
```

**Resposta (201 Created):**
```json
{
  "success": true,
  "message": "Cadastro realizado com sucesso"
}
```

**Exemplo cURL:**
```bash
curl -X POST http://localhost:8080/cargos \
  -H "Content-Type: application/json" \
  -d '{"cargo":{"nomeCargo":"Desenvolvedor Sênior"}}'
```

### 4. Atualizar Cargo

**Endpoint:** `PUT /cargos/{idCargo}`

**Cabeçalhos:**
- `Content-Type: application/json`

**Corpo da Requisição:**
```json
{
  "cargo": {
    "nomeCargo": "Desenvolvedor Pleno"
  }
}
```

**Exemplo cURL:**
```bash
curl -X PUT http://localhost:8080/cargos/1 \
  -H "Content-Type: application/json" \
  -d '{"cargo":{"nomeCargo":"Desenvolvedor Pleno"}}'
```

### 5. Deletar Cargo

**Endpoint:** `DELETE /cargos/{idCargo}`

**Exemplo cURL:**
```bash
curl -X DELETE http://localhost:8080/cargos/1
```

### 6. Contar Cargos

**Endpoint:** `GET /cargos/count`

**Exemplo cURL:**
```bash
curl -X GET http://localhost:8080/cargos/count
```

---

## 👤 Funcionários

### 1. Listar Todos os Funcionários

**Endpoint:** `GET /funcionarios`

**Resposta (200 OK):**
```json
{
  "success": true,
  "message": "Busca realizada com sucesso",
  "data": {
    "funcionarios": []
  }
}
```

**Exemplo cURL:**
```bash
curl -X GET http://localhost:8080/funcionarios
```

### 2. Buscar Funcionário por ID

**Endpoint:** `GET /funcionarios/{idFuncionario}`

**Parâmetros de URL:**
- `idFuncionario` (int obrigatório)

**Exemplo cURL:**
```bash
curl -X GET http://localhost:8080/funcionarios/1
```

### 3. Criar Funcionário

**Endpoint:** `POST /funcionarios`

**Cabeçalhos:**
- `Content-Type: application/json`

**Corpo da Requisição:**
```json
{
  "funcionario": {
    "nomeFuncionario": "João Silva",
    "email": "joao@email.com",
    "senha": "123456",
    "recebeValeTransporte": 1,
    "cargo": {
      "idCargo": 1
    }
  }
}
```

**Exemplo cURL:**
```bash
curl -X POST http://localhost:8080/funcionarios \
  -H "Content-Type: application/json" \
  -d '{
    "funcionario": {
      "nomeFuncionario": "João Silva",
      "email": "joao@email.com",
      "senha": "123456",
      "recebeValeTransporte": 1,
      "cargo": {"idCargo": 1}
    }
  }'
```

### 4. Atualizar Funcionário

**Endpoint:** `PUT /funcionarios/{idFuncionario}`

**Cabeçalhos:**
- `Content-Type: application/json`

**Corpo da Requisição:**
```json
{
  "funcionario": {
    "nomeFuncionario": "João Atualizado",
    "email": "novo@email.com",
    "senha": "123456",
    "recebeValeTransporte": 0,
    "cargo": {
      "idCargo": 2
    }
  }
}
```

**Exemplo cURL:**
```bash
curl -X PUT http://localhost:8080/funcionarios/1 \
  -H "Content-Type: application/json" \
  -d '{
    "funcionario": {
      "nomeFuncionario": "João Atualizado",
      "email": "novo@email.com",
      "senha": "123456",
      "recebeValeTransporte": 0,
      "cargo": {"idCargo": 2}
    }
  }'
```

### 5. Deletar Funcionário

**Endpoint:** `DELETE /funcionarios/{idFuncionario}`

**Exemplo cURL:**
```bash
curl -X DELETE http://localhost:8080/funcionarios/1
```

### 6. Contar Funcionários

**Endpoint:** `GET /funcionarios/count`

**Exemplo cURL:**
```bash
curl -X GET http://localhost:8080/funcionarios/count
```

---

## 🔄 Códigos HTTP

| Código | Significado                  |
|--------|------------------------------|
| 200    | Requisição executada com sucesso |
| 201    | Registro criado com sucesso  |
| 204    | Exclusão realizada com sucesso |
| 400    | Erro de validação            |
| 404    | Registro não encontrado      |
| 500    | Erro interno do servidor     |

---

## 📊 Estrutura de Resposta

### Sucesso
```json
{
  "success": true,
  "message": "Descrição do resultado",
  "data": {}
}
```

### Erro
```json
{
  "success": false,
  "message": "Descrição do erro",
  "error": {}
}
```

---

## 🧪 Testando no Postman

1. **Criar Collection**
2. **Definir variável:**
   - `base_url = http://localhost:8080`
3. **Importar endpoints**
4. **Testar operações CRUD**

---

**Última atualização:** 2 de Maio de 2026