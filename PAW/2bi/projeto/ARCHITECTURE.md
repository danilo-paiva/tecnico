# Arquitetura da API - Gestão de RH

Este projeto utiliza uma **Arquitetura em Camadas**, visando a separação de responsabilidades, facilidade de manutenção e escalabilidade. O fluxo de uma requisição segue o seguinte caminho:
`Rota` $\rightarrow$ `Middleware` $\rightarrow$ `Controller` $\rightarrow$ `Service` $\rightarrow$ `DAO` $\rightarrow$ `Banco de Dados`.

---

## 📂 Descrição das Camadas

### 1. Rotas (`src/api/routes`)
As rotas são a porta de entrada da aplicação. Sua única responsabilidade é **mapear as URLs** (endpoints) e os métodos HTTP (GET, POST, PUT, DELETE) para os métodos correspondentes nos Controllers.
- **Exemplo:** Define que um `GET /funcionarios` deve chamar o método `findAllController` do `FuncionarioController`.

### 2. Middlewares (`src/api/middlewares`)
Os middlewares atuam como interceptadores. Eles são executados **antes** que a requisição chegue ao Controller.
- **Responsabilidades:** Validação de formato de dados (JSON), verificação de permissões, validação de IDs nos parâmetros da URL e autenticação.
- **Objetivo:** Impedir que requisições inválidas cheguem às camadas de negócio, garantindo que o Controller receba apenas dados saneados.

### 3. Controllers (`src/api/controllers`)
O Controller é o "maestro" da requisição. Ele não deve conter lógica de negócio complexa.
- **Responsabilidades:**
    - Receber a requisição HTTP.
    - Extrair os dados do corpo (`body`) ou da URL (`args`).
    - Chamar o serviço correspondente para processar a operação.
    - Formatar e retornar a resposta JSON ao cliente (sucesso ou erro).

### 4. Services (`src/api/services`)
A camada de Serviço é onde reside a **Lógica de Negócio**.
- **Responsabilidades:**
    - Orquestrar o fluxo de dados entre os Controllers e os DAOs.
    - Aplicar regras de negócio (ex: "um funcionário não pode ter dois cargos ao mesmo tempo").
    - Tratar exceções de negócio e decidir o que deve ser retornado ao controlador.
    - Garantir a integridade das operações antes de persistir no banco.

### 5. DAOs - Data Access Objects (`src/api/dao`)
O DAO é a única camada que "fala" diretamente com o banco de dados via SQL.
- **Responsabilidades:**
    - Executar queries SQL (`SELECT`, `INSERT`, `UPDATE`, `DELETE`).
    - Mapear os resultados brutos do banco de dados (arrays do PDO) para objetos do tipo **Model**.
    - Isolar a complexidade do banco de dados do restante da aplicação.

### 6. Models (`src/api/models`)
Os modelos são classes que representam as entidades do domínio (ex: `Funcionario`, `Cargo`).
- **Responsabilidades:**
    - Definir a estrutura de dados de cada entidade.
    - **Validação de Dados:** Garantir que os atributos possuam valores válidos (ex: e-mail válido, senha com complexidade, nomes não vazios) através de seus métodos `set`.
    - Implementar `JsonSerializable` para controlar como o objeto é convertido para JSON na resposta da API.

### 7. Database (`src/api/database`)
Camada de infraestrutura responsável pela conexão.
- **Responsabilidades:**
    - Gerenciar a conexão com o MySQL utilizando PDO.
    - Implementar o padrão **Singleton** para evitar a abertura de múltiplas conexões desnecessárias com o servidor.

---

## 🔄 Fluxo de Dados (Exemplo: Cadastro de Funcionário)

1. **Rota:** Identifica `POST /funcionarios` e encaminha para o `FuncionarioController`.
2. **Middleware:** Valida se o JSON enviado possui todos os campos obrigatórios.
3. **Controller:** Extrai o JSON e cria um objeto `Funcionario`, chamando o `FuncionarioService->create()`.
4. **Service:** Verifica se o e-mail já existe no sistema e se o cargo informado é válido.
5. **DAO:** Recebe o objeto validado e executa o `INSERT INTO funcionarios...`.
6. **Model:** Durante a montagem do objeto, valida se a senha atende aos requisitos de segurança.
7. **Resposta:** O caminho é percorrido de volta, retornando um JSON de "Cadastro realizado com sucesso" para o usuário.
