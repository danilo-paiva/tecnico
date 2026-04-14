<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Src\classes\Produto;
use Src\classes\Pessoa;
use Src\classes\Nota;
use Src\classes\Funcionario;
use Src\classes\Triangulo;


$app = AppFactory::create();
// rota exemplo get


//IMC
$app->get('/IMC', function ($request, $response) {
    $queryParams = $request->getQueryParams();

    
    $nome = $queryParams['nome'] ?? 'Não informado';
    $peso = $queryParams['peso'] ?? -1;
    $altura = $queryParams['altura'] ?? -1;
    // 0000 IMPORTANTE 
    $nome = strip_tags($nome);
    $peso = strip_tags($peso);
    $altura = strip_tags($altura);

    $resultado = "";
    $temErros = false;
    $pessoa = new Pessoa();
    if(!$pessoa -> setNome($nome))
        {
        $resultado .= "
        <div class=\"conteiner m-5 alert alert-warning\">
            <strong>Cuidado!</strong> Nome vazio foi dectado.
        </div>";
        $nome = "Sem Nome";
        }

    if(!$pessoa -> setPeso($peso))
        {
        $resultado .= "
        <div class=\"conteiner m-5 alert alert-danger\">
            <strong>Erro!</strong> Peso negativo ou nulo.
        </div>";
        $temErros = true;
        }
    if(!$pessoa -> setAltura($altura))
        {
        $resultado .= "
        <div class=\"conteiner m-5 alert alert-danger\">
            <strong>Erro!</strong> Altura negativa ou nula.
        </div>";
        $temErros = true;
        }

    if(!$temErros)
        {
        $imc = sprintf("%.2f",$pessoa->calcularIMC());
        $classificacao = $pessoa->classificarIMC();

        // se tiver com imc diferente do ideal entao escreve o range onde seu peso eh ideal
        // pra saber o peso ideal tem considerar o seguinte:
        // imc = peso
        //      altura^2
        //
        //  portanto
        //
        // imc * altura^2 = peso
        
        $pesoIdeal = ($imc<18 || $imc>=25) ? sprintf("
        <br>Seu peso ideal está entre <b>%.0f kg - %.0f kg</b> "
        ,(18.5*($altura*$altura)),(25.0*($altura*$altura))): "";

    
        $resultado .= "
        <h3 class = \"text-success m-5\">
            <b>Pronto!</b>
        </h3> 
        <br>
        <div class=\"container p-5 bg-light border\">
            <h5>$nome, seu IMC é igual a: <b>$imc</b>
            <br>
            isso significa que está $classificacao
            $pesoIdeal
            </h5>
        </div>";
        }
        else
        {
            // se tiver erros
            $resultado .=
            " <div class = \"text-center\">
            <a class=\"btn btn-primary\" href=\"IMC.html\">tente novamente</a>
            </div>";
        }
    
    $response->getbody()->write("
    <!DOCTYPE html>
<html lang =\"pt-br\">
    <head>
        <title>Projeto 1° Bimestre/IMC</title>
        <meta charset=\"utf-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
        <link rel=\"stylesheet\" href=\"style.css\">
        <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
        <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>
        <style>
            :root { --bg-light: #f0f2f5; --primary-health: #2ecc71; --primary-health-hover: #58d68d; }
            body { background-color: var(--bg-light); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
            .hero-section { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); padding: 4rem 0; color: white; text-align: center; margin-bottom: 3rem; }
            .navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .result-card { background: white; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-top: 8px solid var(--primary-health); }
            .btn-calc { background-color: var(--primary-health); color: white !important; border: 2px solid var(--primary-health); font-weight: bold; padding: 0.8rem; transition: all 0.2s ease; border-radius: 50px; }
            .btn-calc:hover { background-color: var(--primary-health-hover); border-color: var(--primary-health); transform: scale(1.02); color: white !important; }
            .footer { background-color: #212529; color: #ced4da; padding: 2rem 0; margin-top: auto; }
        </style>
    </head>
    <body class=\"d-flex flex-column min-vh-100\">
        <nav class=\"navbar navbar-expand-sm navbar-dark bg-dark sticky-top\">
            <div class=\"container\">
                <a class=\"navbar-brand\" href=\"pagInicial.html\">Projeto 1°B</a>
                <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarNav\">
                    <span class=\"navbar-toggler-icon\"></span>
                </button>
                <div class=\"collapse navbar-collapse\" id=\"navbarNav\">
                    <ul class=\"navbar-nav ms-auto\">
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"pagInicial.html\">Início</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link active\" href=\"IMC.html\">IMC</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Produto.html\">Produtos</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Nota.html\">Nota</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Funcionario.html\">Funcionário</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Triangulo.html\">Triângulo</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <header class=\"hero-section\">
            <div class=\"container text-center\">
                <h1 class=\"display-4 fw-bold\">Resultado do IMC</h1>
                <p class=\"lead\">Confira a análise do seu Índice de Massa Corporal</p>
            </div>
        </header>

        <main class=\"container mb-5\">
            <div class=\"row justify-content-center\">
                <div class=\"col-md-6 col-lg-5\">
                    <div class=\"card result-card p-4 p-md-5 text-center\">
                        $resultado
                    </div>
                </div>
            </div>
        </main>

        <footer class=\"footer text-center\">
            <div class=\"container\">
                <p class=\"mb-0\">Feito por</p>
                <p class=\"fw-bold\">Danilo Paiva, Kelwin Marinho e Ruan Zanini</p>
            </div>
        </footer>
    </body>
</html>");
    return $response;
});


// Produto
$app->post('/Produto', function ($request, $response) {
    $queryParams = $request->getParsedBody();


    $produtos[0] = new Produto();
    $produtos[1] = new Produto();
    $produtos[2] = new Produto();
    $produtos[3] = new Produto();
    $produtos[4] = new Produto();

    $produtos[0]->setNome("Arroz Ouro Nobre 5kg");
    $produtos[0]->setPreco(16.80);
    $produtos[0]->addEstoque(20);

    $produtos[1]->setNome("Peito de frango Seara kg");
    $produtos[1]->setPreco(27.90);
    $produtos[1]->addEstoque(50);

    $produtos[2]->setNome("Batata palha Yoki 100g");
    $produtos[2]->setPreco(8.90);
    $produtos[2]->addEstoque(75);

    $produtos[3]->setNome("Ketchup Heinz 567g");
    $produtos[3]->setPreco(13.99);
    $produtos[3]->addEstoque(41);

    $produtos[4]->setNome("Mostarda Heinz 255g");
    $produtos[4]->setPreco(14.98);
    $produtos[4]->addEstoque(28);

    $resultado = "";
    $quantidade[0] = $queryParams['quantidade0'];
    $quantidade[1] = $queryParams['quantidade1'];
    $quantidade[2] = $queryParams['quantidade2'];
    $quantidade[3] = $queryParams['quantidade3'];
    $quantidade[4] = $queryParams['quantidade4'];
    $resultado .= "
    <div class=\"container m-5 p-5 bg-light border\">
    <h5>Alterações feitas:<br></h5>
    ";
    for($x=0;$x<5;$x++)
        {
        if($quantidade[$x]!=0)
            {
            if($produtos[$x] -> subEstoque($quantidade[$x]))
                {
                $resultado .= sprintf("<h6> <b class=\"text-success\">FEITA</b> compra de <b>%.0f un</b> de <b>%s</b> por <b>R$%.2f</b> valor compra : <b>R$%.2f</b></h6><br>",
                $quantidade[$x],$produtos[$x]->getNome(),$produtos[$x]->getPreco(),$produtos[$x]->getPreco()*$quantidade[$x]);
                }
                else
                    {
                        $resultado .= sprintf("<h6> <b class=\"text-danger\">REJEITADA</b> compra de <b>%.0f un</b> de <b>%s</b> por <b>R$%.2f</b></h6><br>",
                $quantidade[$x],$produtos[$x]->getNome(),$produtos[$x]->getPreco());
                    }
            }
            
        }
        $resultado .=
        "
        </div>
        <br>
        ";


        $resultado .= "
        <h3 class = \"m-5\">Produtos</h3>
        <div class=\"row m-4\">";
        for($x=0;$x<5;$x++)
            { 
                $resultado .= sprintf("      
            <div class=\"col container-fluid m-2 p-4 bg-light border rounded d-flex flex-column\">
                <br>    
                <h5>%s</h5>
                <br>
                <h4>Preço: <b class=\"text-success\">R$%.2f</b></h4> 
                <h4>Quantidade: <b class=\"text-primary\">%.0f</b></h4>
                <h4>Valor do Estoque: <b class=\"text-success\">R$%.2f</b></h4>

            </div>",$produtos[$x]->getNome(),$produtos[$x]->getPreco(),$produtos[$x]->getEstoque(),$produtos[$x]->valorEstoque());
            }
           $resultado .= "</div>";
    
    
    
     $response->getbody()->write("
    <!DOCTYPE html>
<html lang =\"pt-br\">
    <head>
        <title>Projeto 1° Bimestre/Produtos</title>
        <meta charset=\"utf-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
        <link rel=\"stylesheet\" href=\"style.css\">
        <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
        <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>
        <style>
            :root { --bg-light: #f0f2f5; --primary-inventory: #3498db; --primary-inventory-hover: #5dade2; }
            body { background-color: var(--bg-light); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
            .hero-section { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); padding: 4rem 0; color: white; text-align: center; margin-bottom: 3rem; }
            .navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .result-card { background: white; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-top: 8px solid var(--primary-inventory); }
            .btn-buy { background-color: var(--primary-inventory); color: white !important; border: 2px solid var(--primary-inventory); font-weight: bold; padding: 0.8rem; transition: all 0.2s ease; border-radius: 50px; }
            .btn-buy:hover { background-color: var(--primary-inventory-hover); border-color: var(--primary-inventory); transform: scale(1.02); color: white !important; }
            .footer { background-color: #212529; color: #ced4da; padding: 2rem 0; margin-top: auto; }
        </style>
    </head>
    <body class=\"d-flex flex-column min-vh-100\">
        <nav class=\"navbar navbar-expand-sm navbar-dark bg-dark sticky-top\">
            <div class=\"container\">
                <a class=\"navbar-brand\" href=\"pagInicial.html\">Projeto 1°B</a>
                <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarNav\">
                    <span class=\"navbar-toggler-icon\"></span>
                </button>
                <div class=\"collapse navbar-collapse\" id=\"navbarNav\">
                    <ul class=\"navbar-nav ms-auto\">
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"pagInicial.html\">Início</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"IMC.html\">IMC</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link active\" href=\"Produto.html\">Produtos</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Nota.html\">Nota</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Funcionario.html\">Funcionário</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Triangulo.html\">Triângulo</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <header class=\"hero-section\">
            <div class=\"container text-center\">
                <h1 class=\"display-4 fw-bold\">Gestão de Estoque</h1>
                <p class=\"lead\">Confira as atualizações de compra e o status atual dos produtos</p>
            </div>
        </header>

        <main class=\"container mb-5\">
            <div class=\"row justify-content-center\">
                <div class=\"col-12\">
                    <div class=\"card result-card p-4 p-md-5\">
                        $resultado
                    </div>
                </div>
            </div>
        </main>

        <footer class=\"footer text-center\">
            <div class=\"container\">
                <p class=\"mb-0\">Feito por</p>
                <p class=\"fw-bold\">Danilo Paiva, Kelwin Marinho e Ruan Zanini</p>
            </div>
        </footer>
    </body>
</html>");
    
    return $response;
});
// Nota
$app->get('/Nota', function ($request, $response) {
    $queryParams = $request->getQueryParams();

    
    $nome = $queryParams['nome'] ?? 'Não informado';
    $nota1 = $queryParams['nota1'] ?? -1;
    $nota2 = $queryParams['nota2'] ?? -1;
    // 0000 IMPORTANTE 
    $nome = strip_tags($nome);
    $nota1 = strip_tags($nota1);
    $nota2 = strip_tags($nota2);

    $resultado = "";
    $temErros = false;
    $aluno = new Nota();
    if(!$aluno -> setNome($nome))
        {
        $resultado .= "
        <div class=\"conteiner m-5 alert alert-warning\">
            <strong>Cuidado!</strong> Nome vazio foi dectado.
        </div>";
        $nome = "Sem Nome";
        $aluno -> setNome($nome);
        }

    if(!$aluno -> setNota1($nota1))
        {
        $resultado .= "
        <div class=\"conteiner m-5 alert alert-danger\">
            <strong>Erro!</strong> Nota 1 não pode ser maior que 10 ou negativa.
        </div>";
        $temErros = true;
        }
    if(!$aluno -> setNota2($nota2))
        {
        $resultado .= "
        <div class=\"conteiner m-5 alert alert-danger\">
            <strong>Erro!</strong>  Nota 2 não pode ser maior maior que 10 ou negativa.
        </div>";
        $temErros = true;
        }

    if(!$temErros)
        {
        $media = sprintf("%.2f",$aluno->calcularMedia());
        $classificacao = $aluno->situacaoAluno();

    
        $resultado .= "
        <h3 class = \"text-success m-5\">
            <b>Pronto!</b>
        </h3> 
        <br>
        <div class=\"container p-5 bg-light border\">
            <h5> Aluno : <b>$nome</b>
            <br>
            1° Nota = <b>$nota1</b> <br>
            2° Nota = <b>$nota2</b> <br>
            Media = <b>$media</b> <br>
            Situação do Aluno: <b>$classificacao</b>
            </h5>
        </div>";
        }
        else
        {
            // se tiver erros
            $resultado .=
            " <div class = \"text-center\">
            <a class=\"btn btn-primary\" href=\"Nota.html\">tente novamente</a>
            </div>";
        }
    
    $response->getbody()->write("
    <!DOCTYPE html>
<html lang =\"pt-br\">
    <head>
        <title>Projeto 1° Bimestre/Nota</title>
        <meta charset=\"utf-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
        <link rel=\"stylesheet\" href=\"style.css\">
        <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
        <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>
        <style>
            :root { --bg-light: #f0f2f5; --primary-edu: #f1c40f; --primary-edu-hover: #f3cf2f; }
            body { background-color: var(--bg-light); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
            .hero-section { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); padding: 4rem 0; color: white; text-align: center; margin-bottom: 3rem; }
            .navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .result-card { background: white; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-top: 8px solid var(--primary-edu); }
            .btn-calc { background-color: var(--primary-edu); color: #333 !important; border: 2px solid var(--primary-edu); font-weight: bold; padding: 0.8rem; transition: all 0.2s ease; border-radius: 50px; }
            .btn-calc:hover { background-color: var(--primary-edu-hover); border-color: var(--primary-edu); transform: scale(1.02); color: #333 !important; }
            .footer { background-color: #212529; color: #ced4da; padding: 2rem 0; margin-top: auto; }
        </style>
    </head>
    <body class=\"d-flex flex-column min-vh-100\">
        <nav class=\"navbar navbar-expand-sm navbar-dark bg-dark sticky-top\">
            <div class=\"container\">
                <a class=\"navbar-brand\" href=\"pagInicial.html\">Projeto 1°B</a>
                <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarNav\">
                    <span class=\"navbar-toggler-icon\"></span>
                </button>
                <div class=\"collapse navbar-collapse\" id=\"navbarNav\">
                    <ul class=\"navbar-nav ms-auto\">
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"pagInicial.html\">Início</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"IMC.html\">IMC</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Produto.html\">Produtos</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link active\" href=\"Nota.html\">Nota</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Funcionario.html\">Funcionário</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Triangulo.html\">Triângulo</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <header class=\"hero-section\">
            <div class=\"container text-center\">
                <h1 class=\"display-4 fw-bold\">Resultado Escolar</h1>
                <p class=\"lead\">Confira a média e a situação final do aluno</p>
            </div>
        </header>

        <main class=\"container mb-5\">
            <div class=\"row justify-content-center\">
                <div class=\"col-md-6 col-lg-5\">
                    <div class=\"card result-card p-4 p-md-5 text-center\">
                        $resultado
                    </div>
                </div>
            </div>
        </main>

        <footer class=\"footer text-center\">
            <div class=\"container\">
                <p class=\"mb-0\">Feito por</p>
                <p class=\"fw-bold\">Danilo Paiva, Kelwin Marinho e Ruan Zanini</p>
            </div>
        </footer>
    </body>
</html>");
    return $response;
});
// Funcionario
$app->post('/Funcionario', function ($request, $response) {
    $queryParams = $request->getParsedBody();

    
    $nome = $queryParams['nome'] ?? 'Não informado';
    $valorHora = $queryParams['valorHora'] ?? -1;
    $qtdHoras = $queryParams['qtdHoras'] ?? -1;
    $valorHoraExtra = $queryParams['valorHoraExtra'] ?? -1;
    $qtdHorasExtras = $queryParams['qtdHorasExtras'] ?? -1;
    // 0000 IMPORTANTE 
    $nome = strip_tags($nome);
    $valorHora = strip_tags($valorHora);
    $valorHoraExtra = strip_tags($valorHoraExtra);
    $qtdHoras = strip_tags($qtdHoras);
    $qtdHorasExtras = strip_tags($qtdHorasExtras);

    $resultado = "";
    $temErros = false;
    $funcionario = new Funcionario();
    if(!$funcionario -> setNome($nome))
        {
        $resultado .= "
        <div class=\"conteiner m-5 alert alert-warning\">
            <strong>Cuidado!</strong> Nome vazio foi dectado.
        </div>";
        $nome = "Sem Nome";
        $funcionario -> setNome($nome);
        }

    if(!$funcionario -> setValorHora($valorHora))
        {
        $resultado .= "
        <div class=\"conteiner m-5 alert alert-danger\">
            <strong>Erro!</strong> Valor/Hora não pode ser zero ou negativo.
        </div>";
        $temErros = true;
        }
        if(!$funcionario -> setValorHoraExtra($valorHoraExtra))
        {
        $resultado .= "
        <div class=\"conteiner m-5 alert alert-danger\">
            <strong>Erro!</strong> Valor/Hora Extra não pode ser zero ou negativo.
        </div>";
        $temErros = true;
        }

        if(!$funcionario -> setQtdHoras($qtdHoras))
        {
        $resultado .= "
        <div class=\"conteiner m-5 alert alert-danger\">
            <strong>Erro!</strong> Quantidade de horas não pode ser zero ou negativo.
        </div>";
        $temErros = true;
        }

        if(!$funcionario -> setQtdHorasExtras($qtdHorasExtras))
        {
        $resultado .= "
        <div class=\"conteiner m-5 alert alert-danger\">
            <strong>Erro!</strong> Quantidade de horas extras não pode ser zero ou negativo.
        </div>";
        $temErros = true;
        }
    

    if(!$temErros)
        {
        $salario = sprintf("%.2f",$funcionario->salarioFinal());
        $resultado .= "
        <h3 class = \"text-success m-5\">
            <b>Pronto!</b>
        </h3> 
        <br>
        <div class=\"container p-5 bg-light border\">
            <h5> Funcionário : <b>$nome</b>
            <br>
            Valor/Hora : <b>R$$valorHora</b><br>
            Quantidade de Horas Trabalhadas: <b>$qtdHoras</b><br><br>
            Valor/Hora : <b>R$$valorHoraExtra</b><br>
            Quantidade de Horas Trabalhadas: <b>$qtdHorasExtras</b><br><br>
            Salário Final: <b class=\"text-success\">R$$salario</b>
            </h5>
        </div>";
        }
        else
        {
            // se tiver erros
            $resultado .=
            " <div class = \"text-center\">
            <a class=\"btn btn-primary\" href=\"Funcionario.html\">tente novamente</a>
            </div>";
        }
    
    $response->getbody()->write("
    <!DOCTYPE html>
<html lang =\"pt-br\">
    <head>
        <title>Projeto 1° bimestre/Funcionario</title>
        <meta charset=\"utf-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
        <link rel=\"stylesheet\" href=\"style.css\">
        <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
        <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js\"></script>
        <style>
            :root { --bg-light: #f0f2f5; --primary-finance: #e67e22; --primary-finance-hover: #f39c12; }
            body { background-color: var(--bg-light); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
            .hero-section { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); padding: 4rem 0; color: white; text-align: center; margin-bottom: 3rem; }
            .navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .result-card { background: white; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-top: 8px solid var(--primary-finance); }
            .btn-calc { background-color: var(--primary-finance); color: white !important; border: 2px solid var(--primary-finance); font-weight: bold; padding: 0.8rem; transition: all 0.2s ease; border-radius: 50px; }
            .btn-calc:hover { background-color: var(--primary-finance-hover); border-color: var(--primary-finance); transform: scale(1.02); color: white !important; }
            .footer { background-color: #212529; color: #ced4da; padding: 2rem 0; margin-top: auto; }
        </style>
    </head>
    <body class=\"d-flex flex-column min-vh-100\">
        <nav class=\"navbar navbar-expand-sm navbar-dark bg-dark sticky-top\">
            <div class=\"container\">
                <a class=\"navbar-brand\" href=\"pagInicial.html\">Projeto 1°B</a>
                <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarNav\">
                    <span class=\"navbar-toggler-icon\"></span>
                </button>
                <div class=\"collapse navbar-collapse\" id=\"navbarNav\">
                    <ul class=\"navbar-nav ms-auto\">
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"pagInicial.html\">Início</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"IMC.html\">IMC</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Produto.html\">Produtos</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Nota.html\">Nota</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link active\" href=\"Funcionario.html\">Funcionário</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Triangulo.html\">Triângulo</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <header class=\"hero-section\">
            <div class=\"container text-center\">
                <h1 class=\"display-4 fw-bold\">Cálculo de Salário</h1>
                <p class=\"lead\">Calcule o salário final com horas extras</p>
            </div>
        </header>

        <main class=\"container mb-5\">
            <div class=\"row justify-content-center\">
                <div class=\"col-md-6 col-lg-5\">
                    <div class=\"card result-card p-4 p-md-5 text-center\">
                        $resultado
                    </div>
                </div>
            </div>
        </main>

        <footer class=\"footer text-center\">
            <div class=\"container\">
                <p class=\"mb-0\">Desenvolvido por</p>
                <p class=\"fw-bold\">Danilo Paiva, Kelwin Marinho e Ruan Zanini</p>
            </div>
        </footer>
    </body>
</html>");
    return $response;
});
// Triangulo
$app->get('/Triangulo', function ($request, $response) {
    $queryParams = $request->getQueryParams();

    
    $lado1 = $queryParams['lado1'] ?? -1;
    $lado2 = $queryParams['lado2'] ?? -1;
    $lado3 = $queryParams['lado3'] ?? -1;
    // 0000 IMPORTANTE 
    $lado1 = strip_tags($lado1);
    $lado2 = strip_tags($lado2);
    $lado3 = strip_tags($lado3);

    $resultado = "";
    $temErros = false;
    $triangulo = new Triangulo();

    if(!$triangulo -> setLado1($lado1))
        {
        $resultado .= "
        <div class=\"conteiner m-5 alert alert-danger\">
            <strong>Erro!</strong> Lado 1 tem que ser maior que zero.
        </div>";
        $temErros = true;
        }
         if(!$triangulo -> setLado2($lado2))
        {
        $resultado .= "
        <div class=\"conteiner m-5 alert alert-danger\">
            <strong>Erro!</strong> Lado 2 tem que ser maior que zero.
        </div>";
        $temErros = true;
        }
         if(!$triangulo -> setLado3($lado3))
        {
        $resultado .= "
        <div class=\"conteiner m-5 alert alert-danger\">
            <strong>Erro!</strong> Lado 3 tem que ser maior que zero.
        </div>";
        $temErros = true;
        }

    if(!$temErros)
        {
        $perimetro = sprintf("%.2f",$triangulo->calcularPerimetro());
        $area = sprintf("%.2f",$triangulo->calcularArea());
        $tipo = $triangulo->tipoTriangulo();


        $resultado .= "
        <h3 class = \"text-success m-5\">
            <b>Pronto!</b>
        </h3> 
        <br>
        <div class=\"container p-5 bg-light border\">
            <h5>Lado 1: <b>$lado1</b><br>
            Lado 2: <b>$lado2</b><br>
            Lado 3: <b>$lado3</b><br>
            <br>
            Tipo: <b class=\"text-primary\">$tipo</b><br>
            Perímetro: <b class=\"text-primary\">$perimetro</b><br>
            Área: <b class=\"text-primary\">$area</b><br>
            </h5>
        </div>";
        }
        else
        {
            // se tiver erros
            $resultado .=
            " <div class = \"text-center\">
            <a class=\"btn btn-primary\" href=\"Triangulo.html\">tente novamente</a>
            </div>";
        }
    
    $response->getbody()->write("
    <!DOCTYPE html>
<html lang =\"pt-br\">
    <head>
        <title>Projeto 1° bimestre/Triangulo</title>
        <meta charset=\"utf-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
        <link rel=\"stylesheet\" href=\"style.css\">
        <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
        <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js\"></script>
        <style>
            :root { --bg-light: #f0f2f5; --primary-math: #9b59b6; --primary-math-hover: #af7ac5; }
            body { background-color: var(--bg-light); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
            .hero-section { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); padding: 4rem 0; color: white; text-align: center; margin-bottom: 3rem; }
            .navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .result-card { background: white; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-top: 8px solid var(--primary-math); }
            .btn-calc { background-color: var(--primary-math); color: white !important; border: 2px solid var(--primary-math); font-weight: bold; padding: 0.8rem; transition: all 0.2s ease; border-radius: 50px; }
            .btn-calc:hover { background-color: var(--primary-math-hover); border-color: var(--primary-math); transform: scale(1.02); color: white !important; }
            .footer { background-color: #212529; color: #ced4da; padding: 2rem 0; margin-top: auto; }
        </style>
    </head>
    <body class=\"d-flex flex-column min-vh-100\">
        <nav class=\"navbar navbar-expand-sm navbar-dark bg-dark sticky-top\">
            <div class=\"container\">
                <a class=\"navbar-brand\" href=\"pagInicial.html\">Projeto 1°B</a>
                <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarNav\">
                    <span class=\"navbar-toggler-icon\"></span>
                </button>
                <div class=\"collapse navbar-collapse\" id=\"navbarNav\">
                    <ul class=\"navbar-nav ms-auto\">
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"pagInicial.html\">Início</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"IMC.html\">IMC</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Produto.html\">Produtos</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Nota.html\">Nota</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link\" href=\"Funcionario.html\">Funcionário</a></li>
                        <li class=\"nav-item\"><a class=\"nav-link active\" href=\"Triangulo.html\">Triângulo</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <header class=\"hero-section\">
            <div class=\"container text-center\">
                <h1 class=\"display-4 fw-bold\">Análise de Triângulos</h1>
                <p class=\"lead\">Descubra o tipo, área e perímetro do triângulo</p>
            </div>
        </header>

        <main class=\"container mb-5\">
            <div class=\"row justify-content-center\">
                <div class=\"col-md-6 col-lg-5\">
                    <div class=\"card result-card p-4 p-md-5 text-center\">
                        $resultado
                    </div>
                </div>
            </div>
        </main>

        <footer class=\"footer text-center\">
            <div class=\"container\">
                <p class=\"mb-0\">Desenvolvido por</p>
                <p class=\"fw-bold\">Danilo Paiva, Kelwin Marinho e Ruan Zanini</p>
            </div>
        </footer>
    </body>
</html>");
    return $response;
});

$app->run();