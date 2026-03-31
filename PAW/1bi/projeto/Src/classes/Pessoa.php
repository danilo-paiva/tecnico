<?php
// status : finalizado (sem ressalvas.)

declare(strict_types=1); //ativa tipagem restrita
namespace src;
class Pessoa
{
    private string $nome;
    private float $peso;
    private float $altura;
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

    public function setPeso(float $novoPeso) : void
    {
        if ($novoPeso <= 0) 
            {
                throw new \InvalidArgumentException(
                'Peso não pode ser igual ou menor que zero.');
            }
        $this->peso = $novoPeso;
    }

    public function setAltura(float $novaAltura) : void
    {
        if ($novaAltura <= 0) 
            {
                throw new \InvalidArgumentException(
                'Altura não pode ser igual ou menor que zero');
            }

        
        if ($novaAltura > 2.5) 
            {
                throw new \InvalidArgumentException(
                'Altura muito grande');
            }
        $this->altura = $novaAltura;
    }

    public function getNome() : string
    {
        return $this->nome;
    }

    public function getPeso() : float
    {
        return $this->peso;
    }

    public function getAltura() : float
    {
        return $this->altura;
    }

    public function calcularIMC() : float
    {
        return ($this->peso/($this->altura*$this->altura));
    }

    public function classificarIMC() : string 
    {
        $IMC = calcularIMC();
        if($IMC<18.5)
            return "Abaixo do peso";
        else 
            if($IMC<25)
                return "Peso normal";
            else
                if($IMC<30)
                    return "Sobrepeso";
                else
                    return "Obesidade";

    }
    
    
}