<?php
/*O sistema deve possuir uma classe Triangulo que permita armazenar os três lados de um triângulo.
    Devem ser criados métodos para verificar e determinar o tipo de triângulo formado pelos lados informados,
        considerando: triângulo equilátero (três lados iguais), triângulo isósceles (dois lados iguais)
            e triângulo escaleno (todos os lados diferentes).
                A classe também deve possuir métodos para calcular o perímetro e a área do triângulo.
                    As invariantes do objeto devem garantir que os lados sejam maiores que zero
                        e que respeitem a regra de existência do triângulo.
                            Para calcular a área considerar Heron.
*/

// status : finalizado (feitos: atributos, setters, getters, area, perimetro, tipo)


declare(strict_types=1);
namespace src;
class Triangulo 
{
    private float $lado1;
    private float $lado2;
    private float $lado3;
    public function __construct() {}

    public function setLado1(float $novoLado) : void
    {
        if($novoLado <=0)
            {
                // 0000 ainda bem que a 4° dimensão não existe
                throw new \InvalidArgumentException(
                'Lado não pode ser menor ou igual a zero.');
            }
        $this->lado1 = $novoLado;
    }

    public function setLado2(float $novoLado) : void
    {
        if($novoLado <=0)
            {
                throw new \InvalidArgumentException(
                'Lado não pode ser menor ou igual a zero.');
            }
        $this->lado2 = $novoLado;
    }

    public function setLado3(float $novoLado) : void
    {
        if($novoLado <=0)
            {
                throw new \InvalidArgumentException(
                'Lado não pode ser menor ou igual a zero.');
            }
        $this->lado3 = $novoLado;
    }

    public function getLado1() : float
    {
        return $this->lado1;
    }

    public function getLado2() : float
    {
        return $this->lado2;
    }

    public function getLado3() : float
    {
        return $this->lado3;
    }

    public function tipoTriangulo() : string
    {
        $l1 = getLado1();
        $l2 = getLado2();
        $l3 = getLado3();
        if($l1 == $l2 and $l1 == $l3)
            return "Equilátero";
        else
            if($l1 == $l2 or $l1 == $l3 or $l2 == $l3)
                return "Isósceles";
            else
                return "Escaleno";
    }

    public function calcularPerimetro() : float
    {
        $l1 = getLado1();
        $l2 = getLado2();
        $l3 = getLado3();
        return $l1+$l2+$l3;
    }

    public function calcularArea() : float
    {
        $l1 = getLado1();
        $l2 = getLado2();
        $l3 = getLado3();
        // 0000 semiperimetro
        $p = ($l1+$l2+$l3)/2;
        // 0000 area = raiz(p*(p-a)*(p-b)*(p-c))         (qual meu problema de deixar as fórmulas aqui?)
        $area = sqrt($p*($p-$l1)*($p-$l2)*($p-$l3));
        return $area;
    }
}