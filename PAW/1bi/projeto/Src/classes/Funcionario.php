<?php
/* O sistema deve possuir uma classe Funcionario com os atributos nome
, valorHora e valorHoraExtra, qtdHoras, qtdHorasExtras.
 Devem ser criados métodos para calcular o salário final do funcionário.
  O sistema deve mostrar o salário final calculado */

// status : finalizado (feitos : atributos, setters, getters, salario)

declare(strict_types=1);
namespace src;
class Funcionario
{
    private string $nome = "vazio";
    private float $valorHora;
    private float $valorHoraExtra;
    private int $qtdHoras;
    private int $qtdHorasExtras;
    public function __construct() {}
    
    public function setNome(string $novoNome) : void
    {
        if ($novoNome == '') 
            {
                throw new \InvalidArgumentException(
                'Nome não pode ser vazio.');
            }
        $novoNome = strip_tags($novoNome);
        $this->nome = $novoNome;
    }

    public function setValorHora(float $novoValorHora) : void
    {
        if($novoValorHora <=0 )
            {
                // 0000 Ês a dúvida: escravidão ou dívida do cartão? ( opa até rimou :> )
                throw new \InvalidArgumentException(
                'Salário/Hora menor ou igual a zero.');
            }
        $this->valorHora = $novoValorHora;
    }

    public function setValorHoraExtra(float $novoValorHoraExtra) : void
    {
        if($novoValorHoraExtra <=0 )
            {
                // 0000 Ora, aparentemente é escravidão mermo
                throw new \InvalidArgumentException(
                'Salário/Hora extra menor ou igual a zero.');
            }
        $this->valorHoraExtra = $novoValorHoraExtra;
    }

    public function setQtdHoras(int $novaQtdHoras) : void
    {
         if($novaQtdHoras <=0 )
            {
                // 0000 Como já diria os comunistas, esses são os donos dos meios de produção       (se não entendeu, se tem horas negativadas, quer dizer que falta pagar os funcionarios, ja que quando positivas, é necessário receber e quando negativo, é necessario pagar)
                throw new \InvalidArgumentException(
                'Quantidade de horas inválida.');
            }
        $this->qtdHoras = $novaQtdHoras;
    }

    public function setQtdHorasExtras(int $novaQtdHorasExtras) : void
    {
         if($novaQtdHorasExtras <=0 )
            {
                throw new \InvalidArgumentException(
                'Quantidade de horas extras inválida.');
            }
        $this->qtdHorasExtras = $novaQtdHorasExtras;
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