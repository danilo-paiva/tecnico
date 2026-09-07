# Aulas de Banco de Dados — 3º Bimestre


---

# Stored Procedure

## Exemplo de Stored Procedure em MySQL

### 1. O que é uma Stored Procedure?

Uma _Stored Procedure_, ou procedimento armazenado, é um bloco de comandos SQL salvo dentro do banco de dados. Ela pode ser executada sempre que necessário, facilitando a reutilização de código, a organização das consultas e a segurança do sistema.

As Stored Procedures podem ser criadas **com parâmetros** ou **sem parâmetros**.

### 2. Tabela de Exemplo

Antes de criar as Stored Procedures, considere a seguinte tabela chamada `alunos`:

```sql
CREATE TABLE alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    idade INT,
    curso VARCHAR(100)
);
```

Inserindo alguns dados:

```sql
INSERT INTO alunos (nome, idade, curso) VALUES
('Ana', 18, 'Informática'),
('Carlos', 20, 'Administração'),
('Mariana', 19, 'Informática');
```

### 3. Stored Procedure sem Parâmetros

A procedure abaixo lista todos os alunos cadastrados na tabela.

```sql
DELIMITER $$

CREATE PROCEDURE listar_alunos()
BEGIN
    SELECT * FROM alunos;
END $$

DELIMITER ;
```

Para executar a procedure:

```sql
CALL listar_alunos();
```

#### 3.1. Explicação

A procedure `listar_alunos()` não recebe nenhum parâmetro. Quando ela é chamada com o comando `CALL`, executa o comando `SELECT * FROM alunos`, retornando todos os registros da tabela.

### 4. Stored Procedure com Parâmetro

A procedure abaixo recebe o nome de um curso como parâmetro e lista apenas os alunos desse curso.

```sql
DELIMITER $$

CREATE PROCEDURE listar_alunos_por_curso (
    IN curso_pesquisado VARCHAR(100)
)
BEGIN
    SELECT *
    FROM alunos
    WHERE curso = curso_pesquisado;
END $$

DELIMITER ;
```

Para executar a procedure:

```sql
CALL listar_alunos_por_curso('Informática');
```

#### 4.1. Explicação

A procedure `listar_alunos_por_curso()` possui um parâmetro de entrada chamado `curso_pesquisado`. Esse parâmetro recebe o nome de um curso e é usado dentro do comando `WHERE` para filtrar os alunos.

Por exemplo, ao executar:

```sql
CALL listar_alunos_por_curso('Informática');
```

o banco retorna somente os alunos que pertencem ao curso de Informática.

### 5. Resumo

- A Stored Procedure sem parâmetro executa sempre a mesma ação.
- A Stored Procedure com parâmetro permite enviar valores para personalizar a consulta.
- O comando `CALL` é usado para executar uma Stored Procedure.
- O comando `DELIMITER` é usado para alterar temporariamente o finalizador de comandos, permitindo criar blocos com `BEGIN` e `END`.




---

# Trigger, View e Stored Procedure

Trigger, View e Stored Procedure 

Automatizando tarefas e organizando consultas no MySQL 

BANCO DE DADOS MYSQL AVANÇADO 



## Situação-Problema 

Uma loja de informática possui um sistema com clientes, produtos, pedidos e itens vendidos. O banco de dados precisa resolver desafios reais do dia a dia: 

- 📊 Relatórios sem repetição 

Mostrar relatórios de vendas sem repetir consultas enormes a cada acesso. 

- 📋 Histórico de pedidos 

Consultar rapidamente todos os pedidos de cada cliente. 

- 🚫 Controle de estoque 

Impedir vendas quando não houver estoque disponível e diminuir automaticamente após a venda. 

- 🔍 Auditoria 

Registrar automaticamente todas as alterações feitas no estoque. 

- 💡 Como automatizar essas tarefas diretamente no banco de dados? A resposta está em **Trigger, View e Stored Procedure** . 



## Banco de Dados LojaTech 

Todas as aulas utilizam um banco de dados chamado **loja_tech** , composto por cinco tabelas interligadas: 



Os relacionamentos seguem o padrão: um cliente realiza vários pedidos; cada pedido possui vários itens; cada item aponta para um produto; e toda alteração de estoque é registrada em auditoria. 

CONFIGURAÇÃO INICIAL 

## Criação do Banco de Dados 

Código MySQL 

CREATE DATABASE loja_tech; 

USE loja_tech; 

Execute esses dois comandos antes de qualquer outro. Eles preparam o ambiente de trabalho. 

O que cada comando faz? 

1 CREATE DATABASE 

Cria o banco de dados chamado **loja_tech** no servidor MySQL. 

- 2 USE 

Seleciona o banco recém-criado. Todos os próximos comandos serão executados dentro dele. 

CONFIGURAÇÃO INICIAL 

## Criação das Tabelas 

As cinco tabelas abaixo formam a base do sistema LojaTech. Observe as chaves primárias ( **PRIMARY KEY** ), estrangeiras ( **FOREIGN KEY** ) e restrições de integridade: 

CREATE TABLE clientes ( id_cliente INT AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(100) NOT NULL, email VARCHAR(100) UNIQUE, cidade VARCHAR(80) ); 

CREATE TABLE produtos ( id_produto INT AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(100) NOT NULL, categoria VARCHAR(50), preco DECIMAL(10,2) NOT NULL, estoque INT NOT NULL DEFAULT 0 

); 

CREATE TABLE pedidos ( id_pedido INT AUTO_INCREMENT PRIMARY KEY, id_cliente INT NOT NULL, data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP, status VARCHAR(30) DEFAULT 'ABERTO', CONSTRAINT fk_pedido_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) 

); 

CREATE TABLE itens_pedido ( id_item INT AUTO_INCREMENT PRIMARY KEY, id_pedido INT NOT NULL, id_produto INT NOT NULL, quantidade INT NOT NULL, preco_unitario DECIMAL(10,2) NOT NULL, CONSTRAINT fk_item_pedido FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido), CONSTRAINT fk_item_produto FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ); 

CREATE TABLE auditoria_estoque ( id_auditoria INT AUTO_INCREMENT PRIMARY KEY, id_produto INT NOT NULL, estoque_anterior INT, estoque_novo INT, data_alteracao DATETIME DEFAULT CURRENT_TIMESTAMP, usuario VARCHAR(100), CONSTRAINT fk_auditoria_produto FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ); 

CONFIGURAÇÃO INICIAL 

## Inserindo Dados de Exemplo 

- INSERT INTO clientes (nome, email, cidade) VALUES ('Ana Souza',    'ana@email.com',   'São Paulo'), ('Bruno Lima',   'bruno@email.com', 'São José dos Campos'), 

- ('Carla Mendes', 'carla@email.com', 'Taubaté'); 

- INSERT INTO produtos (nome, categoria, preco, estoque) VALUES ('Notebook Gamer',       'Informática', 4500.00, 10), 

- ('Mouse Sem Fio',        'Periféricos',   89.90, 30), 

- ('Teclado Mecânico',     'Periféricos',  249.90, 20), 

- ('Monitor 24 Polegadas', 'Informática',  899.90, 15); 

INSERT INTO pedidos (id_cliente, status) VALUES (1, 'FINALIZADO'), (2, 'ABERTO'); 

INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario) VALUES (1, 1, 1, 4500.00), (1, 2, 2,   89.90), (2, 3, 1,  249.90); 

Esses dados serão utilizados em todos os exemplos de **View** , **Stored Procedure** e **Trigger** ao longo da aula. 



PARTE 1 

O que é uma View? Uma janela para seus dados 

PARTE 1 — VIEW 

## Conceito de View 

#### Definição 

Uma **View** é uma tabela virtual criada a partir de uma consulta SQL. Ela não armazena dados próprios — apresenta dados organizados que estão em outras tabelas. 

#### Analogia do cotidiano 

Pense em uma janela: ela não cria uma nova paisagem, apenas mostra uma parte organizada da paisagem que já existe. 

#### Quando usar? 

- Simplificar consultas complexas 

Evite repetir JOINs longos toda vez. 

#### Ocultar colunas sensíveis 

Exponha apenas o necessário para cada usuário. 

Criar relatórios padronizados 

Uma View vira o padrão oficial da empresa. 

Facilitar o acesso 

Usuários sem domínio de SQL consultam dados com facilidade. 

PARTE 1 — VIEW 

## Criando uma View 

A View abaixo reúne dados de quatro tabelas em uma única consulta organizada, calculando o subtotal de cada item vendido: 

CREATE VIEW vw_detalhes_vendas AS SELECT 

p.id_pedido, 

p.data_pedido, 

c.nome              AS cliente, 

pr.nome             AS produto, 

ip.quantidade, 

ip.preco_unitario, ip.quantidade * ip.preco_unitario AS subtotal, 

p.status 

FROM pedidos p 

INNER JOIN clientes c    ON p.id_cliente  = c.id_cliente 

INNER JOIN itens_pedido ip ON p.id_pedido = ip.id_pedido 

INNER JOIN produtos pr   ON ip.id_produto = pr.id_produto; 

4 tabelas 

subtotal 

Nomes claros 

Reunidas em uma View Campo calculado automaticamente 

Aliases facilitam relatórios 

PARTE 1 — VIEW 

## Consultando uma View 

#### Consultas possíveis 

-- Todos os dados SELECT * FROM vw_detalhes_vendas; -- Apenas pedidos finalizados SELECT * FROM vw_detalhes_vendas WHERE status = 'FINALIZADO'; -- Total comprado por cliente SELECT cliente, SUM(subtotal) AS total_comprado FROM vw_detalhes_vendas GROUP BY cliente; 

#### Resultado simulado 

|**Pedido**|**Cliente**|**Produto**|**Qtd**|**Subtotal**|
|---|---|---|---|---|
|1|Ana|Noteboo|1|R$|
||Souza|k Gamer||4.500,00|
|1|Ana|Mouse|2|R$|
||Souza|Sem Fio||179,80|
|2|Bruno|Teclado|1|R$|
||Lima|Mecânic<br>o||249,90|



Perceba que a View é consultada exatamente como uma tabela comum, usando **SELECT** , **WHERE** e **GROUP BY** . 

PARTE 1 — VIEW 

## Alterando e Excluindo uma View 

#### Criar ou substituir 

#### Excluindo a View 

CREATE OR REPLACE VIEW vw_produtos_disponiveis AS SELECT id_produto, nome, categoria, preco, estoque FROM produtos WHERE estoque > 0; 

-- Consultando SELECT * FROM vw_produtos_disponiveis; 

DROP VIEW vw_produtos_disponiveis; 

Excluir uma View **não exclui** as tabelas nem os dados originais. Apenas a definição da consulta é removida. 

#### Resumo dos comandos 

CREATE VIEW 

CREATE OR REPLACE 

Cria uma nova View 

Atualiza uma View existente 

#### DROP VIEW 

Remove a View do banco 

PARTE 2 



# O que é uma Stored Procedure? Uma receita pronta no banco 

PARTE 2 — STORED PROCEDURE 

## Conceito de Stored Procedure 

#### Definição 

#### O que uma Procedure pode fazer? 

Uma **Stored Procedure** (procedimento armazenado) é um conjunto de comandos SQL salvo diretamente no banco de dados, pronto para ser reutilizado. 

01 

Receber parâmetros 

#### Analogia do cotidiano 

Entrada (IN), saída (OUT) ou ambos (INOUT). 

Como uma receita pronta: você informa os ingredientes (parâmetros), executa a receita e recebe o resultado — sem precisar reescrever tudo. 

02 

Executar múltiplos comandos 

SELECT, INSERT, UPDATE e DELETE em sequência. 

03 

Usar condições e cálculos 

IF, ELSE, loops e operações matemáticas. 

##### 04 

Centralizar regras de negócio 

A lógica fica no banco, acessível a qualquer aplicação. 

PARTE 2 — STORED PROCEDURE 

## Procedure com Parâmetro de Entrada (IN) 

Esta procedure recebe o código de um cliente e retorna todos os pedidos dele com o total de cada um: 

DELIMITER // CREATE PROCEDURE sp_pedidos_cliente( IN p_id_cliente INT ) BEGIN SELECT p.id_pedido, p.data_pedido, p.status, SUM(ip.quantidade * ip.preco_unitario) AS total_pedido FROM pedidos p INNER JOIN itens_pedido ip ON p.id_pedido = ip.id_pedido WHERE p.id_cliente = p_id_cliente GROUP BY p.id_pedido, p.data_pedido, p.status; END // DELIMITER ; 

#### Executando 

#### Pontos importantes 

CALL sp_pedidos_cliente(1); 

**IN** : parâmetro de entrada — recebe o código do cliente **BEGIN / END** : delimitam o bloco de comandos **DELIMITER** : permite usar ; dentro da procedure sem encerrá-la antes do tempo 

PARTE 2 — STORED PROCEDURE 

## Procedure para Reajuste de Preços 

Procedure com dois parâmetros de entrada, validação condicional e tratamento de erro com **SIGNAL** : 

DELIMITER // CREATE PROCEDURE sp_reajustar_precos( IN p_categoria  VARCHAR(50), IN p_percentual DECIMAL(5,2) ) BEGIN IF p_percentual <= 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O percentual deve ser maior que zero'; ELSE UPDATE produtos SET preco = preco + (preco * p_percentual / 100) WHERE categoria = p_categoria; END IF; END // DELIMITER ; 

#### Executando um reajuste de 10% 

CALL sp_reajustar_precos('Periféricos', 10); -- Verificando o resultado SELECT nome, categoria, preco FROM produtos WHERE categoria = 'Periféricos'; 

#### Estruturas utilizadas 

#### IF / ELSE 

Executa lógica condicional dentro da procedure 

#### SIGNAL 

Lança um erro personalizado quando a regra é violada 

PARTE 2 — STORED PROCEDURE 

## Procedure com Parâmetro de Saída (OUT) 

#### Código da procedure 

#### Como funciona o OUT? 

DELIMITER // 

### 1 Parâmetro OUT 

CREATE PROCEDURE sp_total_clientes( OUT p_quantidade INT ) 

Diferente de **IN** , o **OUT** devolve um valor para quem chamou a procedure — ideal para retornar resultados calculados. 

BEGIN 

SELECT COUNT(*) INTO p_quantidade FROM clientes; 

### 2 Cláusula INTO 

Armazena o resultado do SELECT diretamente na variável de saída p_quantidade. 

END // 

DELIMITER ; 

### 3 Variável de sessão @total 

#### Executando e consultando 

No MySQL, variáveis iniciadas com @ são variáveis de sessão — persistem durante toda a conexão. 

CALL sp_total_clientes(@total); 

SELECT @total AS quantidade_clientes; 

PARTE 3 

O que é uma Trigger? Um sensor automático no banco 



PARTE 3 — TRIGGER 

## Conceito de Trigger 

#### Definição 

Uma **Trigger** (gatilho) é um comando executado **automaticamente** quando um evento ocorre em uma tabela: INSERT, UPDATE ou DELETE. 

#### Analogia do cotidiano 

Como um sensor de presença: quando alguém entra no ambiente, uma ação é disparada automaticamente — sem nenhuma intervenção manual. 

#### Eventos e momentos 

**Momento BEFORE** 

**AFTER** 

**Descrição** 

Executa antes do evento — ideal para validações Executa depois do evento — ideal para reações 

#### Exemplos de uso 

Atualizar estoque automaticamente 

Criar registros de auditoria 

Impedir dados inválidos 

Calcular valores automaticamente 

###### PARTE 3 — TRIGGER 

## Estrutura Básica de uma Trigger 

#### Sintaxe 

#### Disponibilidade de OLD e NEW 

DELIMITER // 

CREATE TRIGGER nome_da_trigger BEFORE INSERT ON nome_da_tabela 

FOR EACH ROW 

BEGIN 

-- comandos executados automaticamente 

END // 

|**Evento**|**OLD**|**NEW**|
|---|---|---|
|INSERT|❌Não disponível|✅Disponível|
|UPDATE|✅Disponível|✅Disponível|
|DELETE|✅Disponível|❌Não disponível|



DELIMITER ; 

**OLD** acessa os valores anteriores; **NEW** acessa os novos valores que serão inseridos ou atualizados. 

**FOR EACH ROW** : a Trigger executa para cada linha afetada pelo evento — não apenas uma vez por comando. 

PARTE 3 — TRIGGER 

## Trigger: Impedir Venda sem Estoque 

Esta Trigger é executada **antes** de inserir um item no pedido. Ela valida a quantidade e verifica se o estoque é suficiente: 

DELIMITER // CREATE TRIGGER trg_verificar_estoque BEFORE INSERT ON itens_pedido FOR EACH ROW BEGIN DECLARE v_estoque_atual INT; 

SELECT estoque INTO v_estoque_atual FROM produtos WHERE id_produto = NEW.id_produto; IF NEW.quantidade <= 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A quantidade deve ser maior que zero'; END IF; IF v_estoque_atual < NEW.quantidade THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Estoque insuficiente para realizar a venda'; END IF; END // DELIMITER ; 01 02 Disparada BEFORE INSERT Consulta o estoque Executa antes do item ser salvo na tabela. Busca o estoque atual usando NEW.id_produto. 03 04 Valida a quantidade Verifica disponibilidade Rejeita valores menores ou iguais a zero. Impede a operação se o estoque for insuficiente. 

PARTE 3 — TRIGGER 

## Trigger: Diminuir o Estoque Automaticamente 

#### Código da Trigger 

#### Testando na prática 

DELIMITER // CREATE TRIGGER trg_diminuir_estoque AFTER INSERT ON itens_pedido FOR EACH ROW BEGIN 

UPDATE produtos SET estoque = estoque - NEW.quantidade WHERE id_produto = NEW.id_produto; END // DELIMITER ; 

-- Novo pedido para Carla (id=3) INSERT INTO pedidos (id_cliente, status) VALUES (3, 'ABERTO'); 

-- Inserindo 3 unidades do Mouse Sem Fio INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario) VALUES (3, 2, 3, 89.90); 

-- Verificando o estoque atualizado SELECT id_produto, nome, estoque FROM produtos WHERE id_produto = 2; 

O estoque foi de **30** para **27** automaticamente — sem nenhum UPDATE manual! 

PARTE 3 — TRIGGER 

## Trigger de Auditoria de Estoque 

Toda vez que o estoque de um produto for alterado, esta Trigger registra automaticamente quem fez a mudança e quais foram os valores: 

DELIMITER // CREATE TRIGGER trg_auditar_estoque AFTER UPDATE ON produtos FOR EACH ROW BEGIN IF OLD.estoque <> NEW.estoque THEN INSERT INTO auditoria_estoque (id_produto, estoque_anterior, estoque_novo, usuario) VALUES (NEW.id_produto, OLD.estoque, NEW.estoque, USER()); END IF; END // DELIMITER ; 

#### Consultando o histórico 

#### Como funciona? 

**OLD.estoque** : valor antes da alteração 

SELECT a.id_auditoria, p.nome AS produto, a.estoque_anterior, a.estoque_novo, a.data_alteracao, a.usuario FROM auditoria_estoque a INNER JOIN produtos p ON a.id_produto = p.id_produto ORDER BY a.data_alteracao DESC; 

**NEW.estoque** : valor após a alteração 

**USER()** : identifica o usuário MySQL conectado 

O IF garante que o registro só ocorre quando o estoque realmente muda 

PARTE 3 — TRIGGER 

## Fluxo Completo de uma Venda 

Veja como uma única operação de inserção ativa uma cadeia de automações em sequência: 



Sistema Dispara Verifica Estoque Estoque OK insere trigger estoque insuf. 

Uma única operação pode ativar **diferentes automações em sequência** . Planeje e documente bem para evitar efeitos inesperados. 

GERENCIAMENTO 

## Consultando e Excluindo Triggers e Procedures 

#### Gerenciando Triggers 

#### Gerenciando Views 

-- Listar todas as Triggers do banco SHOW TRIGGERS; -- Excluir uma Trigger específica DROP TRIGGER trg_diminuir_estoque; 

-- Listar todas as Views SHOW FULL TABLES WHERE Table_type = 'VIEW'; 

-- Ver a definição de uma View SHOW CREATE VIEW vw_detalhes_vendas; 

#### Gerenciando Stored Procedures 

-- Excluir uma View DROP VIEW vw_detalhes_vendas; 

-- Listar Procedures do banco loja_tech SHOW PROCEDURE STATUS WHERE Db = 'loja_tech'; 

-- Excluir uma Procedure específica DROP PROCEDURE sp_pedidos_cliente; 

Tenha cuidado ao excluir objetos. A aplicação pode depender de Triggers, Procedures ou Views que você está removendo. Sempre verifique antes de excluir. 

## Comparação Geral 

|**Recurso**|**Principal Função**|**Execução**||**Exemplo Prático**|
|---|---|---|---|---|
|**View**|Simplificar e organizar<br>consultas|SELECT * FROM view||Relatório de vendas por cliente|
|**Stored Procedure**|Agrupar comandos e regras de<br>negócio|CALL procedure()||Consultar pedidos de um<br>cliente|
|**Trigger**|Automatizar ações após<br>eventos|Executada automatica|mente|Atualizar estoque após venda|
|🪟View|📋Stored Proced|ure|⚡Trig|ger|
|Uma janela organizada para|seus dados<br>Uma receita pronta q<br>parâmetros|ue você executa com|Um sen|sor automático que reage a eventos|



## Quando Utilizar Cada Recurso? 



Use View quando... 

Uma consulta é muito grande ou complexa O mesmo relatório é acessado várias 

vezes 

Deseja ocultar colunas sensíveis 

Precisa padronizar o acesso aos dados 



Use Stored Procedure quando... 

Vários comandos precisam ser executados juntos 

Existe uma regra de negócio reutilizável É necessário receber e retornar parâmetros 

Deseja centralizar operações no banco 



Use Trigger quando... 

Uma ação deve acontecer automaticamente 

É necessário registrar auditoria de alterações 

Deseja impedir dados inválidos na inserção 

Uma tabela precisa reagir à mudança em outra 

## Vantagens e Cuidados 

✅ Vantagens 

#### ⚠ Cuidados 

Menos repetição de código 

#### Triggers invisíveis 

Escreva uma vez, reutilize sempre. 

Podem executar ações que a aplicação não vê diretamente. 

Automação de tarefas 

#### Excesso dificulta manutenção 

O banco trabalha sozinho em resposta a eventos. 

Muitas Triggers encadeadas tornam o sistema difícil de depurar. 

#### Centralização de regras 

Regras de negócio ficam no banco, não dispersas no código. 

Segurança e padronização 

#### Views complexas 

Podem apresentar problemas de desempenho em grandes volumes. 

Operações passam por validações automáticas. 

#### Documente tudo 

Sempre registre o que cada objeto faz e por quê. 

Automação sem documentação pode transformar facilidade em dificuldade. 

## Erros Comuns — Fique Atento! 

- 🔧 Esquecer o DELIMITER 

Sem alterar o delimitador, o MySQL interpreta o primeiro ; como fim da procedure antes do esperado. 

- 🔀 Usar OLD ou NEW no evento errado 

**INSERT** → só tem NEW. **DELETE** → só tem OLD. **UPDATE** → tem os dois. Usar o errado causa erro imediato. 

- 🔁 Trigger duplicada 

O MySQL não permite duas Triggers com o mesmo nome no mesmo banco. Use DROP TRIGGER antes de recriar. 

DELIMITER // -- ... código ... DELIMITER ; 

- ❓ Não validar valores nulos ou negativos 

Sempre use IF e SIGNAL para proteger campos críticos como preço e quantidade. 

- 📝 Regras escondidas sem documentação 

Documente todas as Triggers e Procedures com comentários e um registro centralizado das automações ativas. 

DESAFIO 1 

## Desafio Prático — View de Estoque Baixo 

A empresa precisa monitorar produtos com pouco estoque. Crie uma View chamada **vw_estoque_baixo** que exiba apenas produtos com estoque menor ou igual a 10, com as colunas: código, nome, categoria, estoque e preço. 

Tente criar a View antes de ver a resposta! Lembre-se: use WHERE estoque <= 10. 

#### Resposta 

CREATE VIEW vw_estoque_baixo AS 

SELECT 

id_produto, 

nome, 

categoria, 

estoque, 

preco 

FROM produtos WHERE estoque <= 10; 

-- Consultando 

SELECT * FROM vw_estoque_baixo; 

DESAFIO 2 

## Desafio Prático — Procedure por Categoria 

Crie uma Stored Procedure chamada **sp_produtos_categoria** que receba uma categoria como parâmetro e retorne os produtos daquela categoria, ordenados do menor para o maior preço. 

Tente escrever a procedure completa antes de conferir a resposta! 

#### Resposta 

DELIMITER // CREATE PROCEDURE sp_produtos_categoria( IN p_categoria VARCHAR(50) ) BEGIN SELECT id_produto, nome, preco, estoque FROM produtos WHERE categoria = p_categoria ORDER BY preco ASC; END // DELIMITER ; -- Executando CALL sp_produtos_categoria('Periféricos'); 

DESAFIO 3 

## Desafio Prático — Trigger de Validação de Preço 

Crie uma Trigger que impeça o cadastro de produtos com preço menor ou igual a zero. O banco deve rejeitar a operação e exibir uma mensagem de erro clara. 

Lembre-se: use BEFORE INSERT e SIGNAL SQLSTATE '45000'! 

#### Resposta 

DELIMITER // 

CREATE TRIGGER trg_validar_preco_produto BEFORE INSERT ON produtos 

FOR EACH ROW 

BEGIN 

IF NEW.preco <= 0 THEN SIGNAL SQLSTATE '45000' 

SET MESSAGE_TEXT = 'O preço do produto deve ser maior que zero'; 

END IF; 

END // 

DELIMITER ; 

-- Teste inválido (deve ser rejeitado pelo banco) INSERT INTO produtos (nome, categoria, preco, estoque) 

VALUES ('Produto de Teste', 'Teste', -10.00, 5); 

## Quiz — Teste seus Conhecimentos! 



<!-- Start of picture text -->
1<br>2<br>3<br>4<br><!-- End of picture text -->

#### Qual recurso representa uma tabela virtual? 

- 1 A) Trigger **B) View** ✅ C) Stored Procedure    D) Primary Key 

Uma View é uma tabela virtual baseada em uma consulta SQL. Ela não armazena dados próprios. 

#### Qual comando executa uma Stored Procedure? 

- 2 A) EXECUTE TABLE    B) RUN PROCEDURE **C) CALL** ✅ D) START 

O comando correto é CALL nome_da_procedure(). Os demais não existem no MySQL padrão. 

#### Qual palavra representa o valor anterior em uma Trigger de UPDATE? 

- 3 A) BEFORE    B) NEW **C) OLD** ✅ D) PREVIOUS 

OLD representa o valor antes da alteração; NEW representa o valor após. 

#### Qual recurso é executado automaticamente após um evento? 

- 4 A) View **B) Trigger** ✅ C) SELECT    D) Stored Procedure 

A Trigger é disparada automaticamente pelo banco de dados em resposta a INSERT, UPDATE ou DELETE. 

#### Qual é a principal vantagem de uma View? 

- 5 

- A) Criar um novo servidor    B) Substituir todas as tabelas **C) Simplificar consultas complexas** ✅ D) Excluir dados automaticamente 

A View organiza e simplifica o acesso aos dados, evitando repetição de JOINs e lógicas complexas. 

## Resumo Final 

🪟 View 

Cria uma visão organizada dos dados existentes. 

SELECT * FROM nome_da_view; 

Tabela virtual 

Não armazena dados 

Ideal para relatórios 

- 📋 Stored Procedure 

Armazena e executa um conjunto de comandos com parâmetros. 

CALL nome_da_procedure(); 

Recebe parâmetros IN/OUT 

Centraliza regras de negócio Executa múltiplos comandos 

#### ⚡ Trigger 

Executa ações automaticamente após eventos na tabela. 

BEFORE/AFTER INSERT BEFORE/AFTER UPDATE BEFORE/AFTER DELETE 

Automação total 

Usa OLD e NEW 

Ideal para auditoria 

##### **View organiza, Stored Procedure executa e Trigger automatiza.** 



## Agora é sua vez! 

Escolha um sistema — uma biblioteca, uma clínica, um e-commerce — e aplique os três recursos aprendidos hoje: crie uma **View** para organizar os dados, uma **Stored Procedure** para automatizar uma consulta e uma **Trigger** para proteger a integridade do sistema. 



Crie uma View Organize seus dados em uma consulta reutilizável 

Crie uma Procedure Centralize uma regra de negócio do seu sistema 

Crie uma Trigger 

Automatize uma ação ou registre uma auditoria 

BONS ESTUDOS! 🚀 

