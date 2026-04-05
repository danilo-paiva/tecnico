<?php
// status : finalizado (sem ressalvas.)

declare(strict_types=1); //ativa tipagem restrita
namespace Src\classes;
class Pessoa
{
    private string $nome;
    private float $peso;
    private float $altura;
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

    public function setPeso(float $novoPeso) : bool
    {
        if ($novoPeso <= 0) 
            {
                return false;
            }
        $this->peso = $novoPeso;
        return true;
    }

    public function setAltura(float $novaAltura) : bool
    {
        if ($novaAltura <= 0) 
            {
                return false;
            }

        $this->altura = $novaAltura;
        return true;
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
        $IMC = $this->calcularIMC();
        if($IMC<18.5)
            return "<b>abaixo do peso</b>";
        else 
            if($IMC<25)
                return "com <b>peso normal</b>";
            else
                if($IMC<30)
                    return "com <b>sobrepeso</b>";
                else
                    return "com <b>obesidade</b>";

    }
    
    
}