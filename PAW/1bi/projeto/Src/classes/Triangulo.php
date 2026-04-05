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
namespace Src\classes;
class Triangulo 
{
    private float $lado1;
    private float $lado2;
    private float $lado3;
    public function __construct() {}

    public function setLado1(float $novoLado) : bool
    {
        if($novoLado <=0)
            {
                // 0000 ainda bem que a 4° dimensão não existe
                return false;
            }
        $this->lado1 = $novoLado;
        return true;
    }

    public function setLado2(float $novoLado) : bool
    {
        if($novoLado <=0)
            {
                return false;
            }
        $this->lado2 = $novoLado;
        return true;
    }

    public function setLado3(float $novoLado) : bool
    {
        if($novoLado <=0)
            {
                return false;
            }
        $this->lado3 = $novoLado;
        return true;
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

    public function ehTriangulo() : bool
    {
        $l1 = $this->getLado1();
        $l2 = $this->getLado2();
        $l3 = $this->getLado3();
        if($l1+$l2>$l3 && $l1+$l3>$l2 && $l3+$l2>$l1)
            return true;
        else
            return false;
    }

    public function tipoTriangulo() : string
    {
        if(!$this->ehTriangulo())
            return "<b class=\"text-danger\">Não É Um Triângulo!</b>";
        $l1 = $this->getLado1();
        $l2 = $this->getLado2();
        $l3 = $this->getLado3();
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
        if(!$this->ehTriangulo())
            return 0;
        $l1 = $this->getLado1();
        $l2 = $this->getLado2();
        $l3 = $this->getLado3();
        return $l1+$l2+$l3;
    }

    public function calcularArea() : float
    {
        if(!$this->ehTriangulo())
            return 0;
        $l1 = $this->getLado1();
        $l2 = $this->getLado2();
        $l3 = $this->getLado3();
        // 0000 semiperimetro
        $p = ($l1+$l2+$l3)/2;
        // 0000 area = raiz(p*(p-a)*(p-b)*(p-c))         (qual meu problema de deixar as fórmulas aqui?)
        $area = sqrt($p*($p-$l1)*($p-$l2)*($p-$l3));
        return $area;
    }
}