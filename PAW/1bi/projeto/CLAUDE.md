# CLAUDE.md

Este arquivo fornece orientações ao Claude Code (claude.ai/code) ao trabalhar com o código neste repositório.

## Comandos de Desenvolvimento

- **Instalar dependências**: `composer install`
- **Rodar servidor local**: `php -S localhost:8000 -t public`
- **Acessar aplicação**: Navegar para `http://localhost:8000` após iniciar o servidor.

## Arquitetura e Estrutura

Este repositório contém um projeto PHP utilizando o micro-framework **Slim 4**, focado em cálculos matemáticos e lógicos simples.

- **Ponto de Entrada**: `public/index.php` define todas as rotas da aplicação (GET/POST) e renderiza as respostas HTML.
- **Classes de Domínio**: Localizadas em `Src/classes/`, seguindo o padrão de namespace `Src\classes` (PSR-4).
    - `Pessoa.php`: Lógica para cálculo e classificação de IMC.
    - `Produto.php`: Gerenciamento de estoque, preços e valor total de estoque.
    - `Nota.php`: Processamento de notas escolares e situação final do aluno.
    - `Funcionario.php`: Cálculo de salários, incluindo horas extras.
    - `Triangulo.php`: Classificação de tipos de triângulos, cálculo de área e perímetro.
- **Frontend**: A aplicação utiliza arquivos HTML estáticos em `public/` para os formulários de entrada e o framework **Bootstrap 5** (via CDN) para a interface do usuário e exibição de resultados.

## Padrões de Código
- **Tipagem**: Uso de `declare(strict_types=1);` em novas classes.
- **Validação**: As classes de domínio utilizam métodos setters que validam os dados e retornam `bool` para indicar sucesso ou falha.
- **Segurança**: Uso básico de `strip_tags()` para sanitização de entradas vindas das superglobais de requisição.
- **Dependências**: Gerenciadas via Composer, incluindo `slim/slim` e `slim/psr7`.
- **Documentação de Mudanças**: Toda e qualquer alteração realizada no código ou no design deve ser obrigatoriamente registrada no arquivo `LAST_CHANGES.md`, detalhando o que foi modificado e o motivo.
