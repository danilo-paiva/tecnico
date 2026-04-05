<?php
/* O sistema deve possuir uma classe Nota responsável por representar as notas de um estudante.
 A classe deve possuir os atributos nome do aluno, nota1 e nota2.
  Devem ser criados métodos para calcular a média das notas e determinar a situação do aluno. 
A situação pode ser: aprovado, recuperação ou reprovado.*/

// status : finalizado (feitos: atributos, setters, getters, mediaAluno, situaçãoAluno)

declare(strict_types=1);
namespace Src\classes;
class Nota
{
    private string $aluno = "vazio";
    private float $nota1;
    private float $nota2;
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

    public function setNota1(float $novaNota) : bool
    {
        if($novaNota < 0)
            {   
                return false;
            }
        if($novaNota > 10)
            {
                return false;
            }

        $this->nota1 = $novaNota;
        return true;
    }

    public function setNota2(float $novaNota) : bool
    {
        if($novaNota < 0)
            {
                return false;
            }
        if($novaNota > 10)
            {
                return false;
            }

        $this->nota2 = $novaNota;
        return true;
    }

    public function getNome() : string
    {
        return $this->nome;
    }

    public function getNota1() : float
    {
        return $this->nota1;
    }

    public function getNota2() : float
    {
        return $this->nota2;
    }

    public function calcularMedia() : float
    {
        // 0000 media = (nota1 + nota2) / 2 (pq me dei o trabalho de escrever isso???)
        return ($this->nota1 + $this->nota2) / 2;
    }

    public function situacaoAluno() : string 
    {
        $media = $this->calcularMedia();
        if($media >= 6)
            return "<b class=\"text-success\">Aprovado<b>";
        else
            if($media >= 3)
                return "<b class=\"text-warning\">Recuperação</b>";
            else
                // 0000 caso não entenda esse código, 
                // 0000 provavelmente você vai ficar aqui
                return "<b class=\"text-danger\">Reprovado</b>";
    }
}