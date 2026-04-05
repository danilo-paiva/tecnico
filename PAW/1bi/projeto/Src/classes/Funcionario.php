<?php
/* O sistema deve possuir uma classe Funcionario com os atributos nome
, valorHora e valorHoraExtra, qtdHoras, qtdHorasExtras.
 Devem ser criados métodos para calcular o salário final do funcionário.
  O sistema deve mostrar o salário final calculado */

// status : finalizado (feitos : atributos, setters, getters, salario)

declare(strict_types=1);
namespace Src\classes;
class Funcionario
{
    private string $nome = "vazio";
    private float $valorHora;
    private float $valorHoraExtra;
    private int $qtdHoras;
    private int $qtdHorasExtras;
    public function __construct() {}
    
    public function setNome(string $novoNome) : bool
    {
        if ($novoNome == '') 
            {
                return false;
            }
        $novoNome = strip_tags($novoNome);
        $this->nome = $novoNome;
        return true;
    }

    public function setValorHora(float $novoValorHora) : bool
    {
        if($novoValorHora <=0 )
            {
                // 0000 Ês a dúvida: escravidão ou dívida do cartão? ( opa até rimou :> )
                return false;
            }
        $this->valorHora = $novoValorHora;
        return true;
    }

    public function setValorHoraExtra(float $novoValorHoraExtra) : bool
    {
        if($novoValorHoraExtra <=0 )
            {
                // 0000 Ora, aparentemente é escravidão mermo
                return false;
            }
        $this->valorHoraExtra = $novoValorHoraExtra;
        return true;
    }

    public function setQtdHoras(int $novaQtdHoras) : bool
    {
         if($novaQtdHoras <=0 )
            {
                // 0000 Como já diria os comunistas, esses são os donos dos meios de produção       (se não entendeu, se tem horas negativadas, quer dizer que falta pagar os funcionarios, ja que quando positivas, é necessário receber e quando negativo, é necessario pagar)
                return false;
            }
        $this->qtdHoras = $novaQtdHoras;
        return true;
    }

    public function setQtdHorasExtras(int $novaQtdHorasExtras) : bool
    {
         if($novaQtdHorasExtras <=0 )
            {
                return false;
            }
        $this->qtdHorasExtras = $novaQtdHorasExtras;
        return true;
    }

    public function getNome() : string
    {
        return $this->nome;
    }

    public function getValorHora() : float
    {
        return $this->valorHora;
    }

    public function getValorHoraExtra() : float
    {
        return $this->valorHoraExtra;
    }

    public function getQtdHoras() : int
    {
        return $this->qtdHoras;
    }

    public function getQtdHorasExtras() : int
    {
        return $this->qtdHorasExtras;
    }

    public function salarioFinal() : float
    {
        // sabadão e eu fazendo essa merda,sem beber agua o dia inteiro, enfim. (inclusive vou ter que fever água agr mesmo. resultado: agua com gosto de metal pois tive que usar a chaleira e to sem gas. ps: opa chegou o filtro :D)
        return ($this->valorHora*$this->qtdHoras) + ($this->valorHoraExtra*$this->qtdHorasExtras);
    }

}