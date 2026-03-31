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

    public function setNota1(float $novaNota) : void
    {
        if($novaNota < 0)
            {
                throw new \InvalidArgumentException(
                'Nota não pode ser menor que zero.');
            }
        if($novaNota > 10)
            {
                throw new \InvalidArgumentException(
                'Nota não pode ser maior que 10');
            }

        $this->nota1 = $novaNota;
    }

    public function setNota2(float $novaNota) : void
    {
        if($novaNota < 0)
            {
                throw new \InvalidArgumentException(
                'Nota não pode ser menor que zero.');
            }
        if($novaNota > 10)
            {
                throw new \InvalidArgumentException(
                'Nota não pode ser maior que 10');
            }

        $this->nota2 = $novaNota;
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
        $media = calcularMedia();
        if($media >= 6)
            return "Aprovado";
        else
            if($media >= 3)
                return "Recuperação";
            else
                // 0000 caso não entenda esse código, 
                // 0000 provavelmente você vai ficar aqui
                return "Reprovado";
    }
}