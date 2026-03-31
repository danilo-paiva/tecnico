<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Src\classes\Produto;

$app = AppFactory::create();
// rota exemplo get

$app->get('/teste', function (Request $request, Response $response): Response {
    $queryParams = $request->getQueryParams();

    
    $produtos = new Produto();
    $produtos->setPreco(15.0);
    $produtos->addEstoque(5);
    $produtos->setNome("arroz");

    $quantidade = $queryParams['quantidade'];
    $produtos->subEstoque($quantidade);
    $nome = $produtos->getNome();
    $response->getBody()->write("<br> comprou $nome <br> quantidade $quantidade ");

    
    return $response;
});

$app->get('/rotaExemploGet', function ($request, $response) {
    $queryParams = $request->getQueryParams();

    
    $nome = $queryParams['nome'] ?? 'Não informado';
    $cpf = $queryParams['cpf'] ?? 'Não informado';

    // 0000 IMPORTANTE 
    $nome = strip_tags($nome);
    $cpf = strip_tags($cpf);

    $response->getBody()->write('Olá Mundo!<br>');
    $response->getBody()->write(" nome: <b>$nome</b> <br> cpf: <b>$cpf</b>");
    return $response;
});

$app->post('/rotaExemploPost', function ($request, $response) {
    // Captura os dados enviados pelo formulário via POST
    $dados = $request->getParsedBody();

    $nome = $dados['nome'] ?? '';
    $cpf = $dados['cpf'] ?? '';
    $nome = strip_tags($nome);
    $cpf = strip_tags($cpf);
    

    // Escreve a resposta
    $response->getBody()->write('Olá Mundo!<br>');
     $response->getBody()->write(" nome: <b>$nome</b> <br> cpf: <b>$cpf</b>");
    return $response;
});

// rota exemplo get
$app->post('/rotaProduto', function ($request, $response) {
    $queryParams = $request->getParsedBody();


    $produtos[0] = new Produto();
    $produtos[1] = new Produto();
    $produtos[2] = new Produto();
    $produtos[3] = new Produto();
    $produtos[4] = new Produto();

    $produtos[0]->setNome("Arroz Ouro Nobre 5kg");
    $produtos[0]->setPreco(16.80);
    $produtos[0]->addEstoque(20);

    $produtos[1]->setNome("Peito de frango kg");
    $produtos[1]->setPreco(27.90);
    $produtos[1]->addEstoque(50);

    $produtos[2]->setNome("Batata palha Yoki 100g");
    $produtos[2]->setPreco(8.90);
    $produtos[2]->addEstoque(20);

    $produtos[3]->setNome("Ketchup Heinz 567g");
    $produtos[3]->setPreco(13.99);
    $produtos[3]->addEstoque(20);

    $produtos[4]->setNome("Mostarda Heinz 255g");
    $produtos[4]->setPreco(14.98);
    $produtos[4]->addEstoque(20);
    
    $opcao = $queryParams['opcao'];
    if($opcao < 0 || $opcao > 4)
        {
        $response->getBody()->write("Opção de produto inexistente");
        return $response;
        }
    
    

    $quantidade = $queryParams['quantidade'] ?? 0;
    $produtos[$opcao] -> subEstoque($quantidade); 
    $nome = $produtos[$opcao] -> getNome();
    
    $estoque = $produtos[$opcao]->getEstoque();

    $response->getBody()->write("<br> comprou $nome <br> quantidade $quantidade <br> restante: $estoque");
    
    return $response;
});






$app->run();