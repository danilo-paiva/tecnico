/* *******************************************************************
* Colegio Técnico Antônio Teixeira Fernandes (Univap)
* Curso Técnico em Informática - Data de Entrega: DD/MM/2026
* Autores do Projeto: Danilo Paiva
*                     Kelwin Mrinho
* Turma: 2H
* Atividade Proposta em aula
* Observação: 
* 
* projeto.cs
* ************************************************************/


using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace ConsoleApplication1
{
    class Program
    {
        static void Main(string[] args)
        {
            int qtdPicos = 0, qtdNaoPicos = 0, qtdNumeros = 0;
            int maiorDigitoPico = 0, maiorDigito = 0;
            int somaNaoPicos = 0;
            int input, numero;
            // flag ou variavel onde é guardado o estado do teste para descobrir se é numero pico, podendo assumir 
            // 4 valores diferentes:
            // 'c' - significa 'começo' que é o primeiro teste a ser feito no laço;
            // 'n' - quer dizer que já foi enquadrado como não pico;
            // '>' numeros crescendo, da direita à esquerda;
            // '<' numeros diminuindo, da direita à esquerda.
            char condicao; 

            do
            {
                do
                {
                    input = int.Parse(Console.ReadLine());
                } while (input < 0);

                numero = input;
                maiorDigito = 0; //zerar  maior digito

                if (input == 0)
                    break;
                if (input >= 120)
                    condicao = 'c';
                else
                    condicao = 'n';

                while (numero >= 10 && condicao != 'n')
                {
                    int digito = numero % 10;
                    int pdigito = (numero / 10) % 10;

                    // teste do maior digito do numero digitado

                    // erro ingnorado : 120 nesse numero por exemplo o digito 1 foi ingnorado a partir do código existente
                    // já que é levado em consideração o 'digito' e não o 'pdigito', no entanto, esse erro não faz diferença 
                    // no resultado final pois é impossível o digito mais à esquerda ser maior do que os outros à direita num número pico.
                    if (digito > maiorDigito)
                    {
                        maiorDigito = digito;
                    }
                    // a entrada aqui acontece de duas formas: seja por ter iniciado o laço (onde a flag = 'c') ou por estar 
                    // em ordem crescente os números da direita à esquerda (onde flag = '>')
                    // em caso de estar incializando e não entrar nesse if, o número automaticamente já é considerado não pico 
                    if (pdigito > digito && (condicao == 'c' || condicao == '>'))
                    {
                        numero /= 10;
                        condicao = '>';
                    }
                    else if (pdigito < digito && condicao == '<')
                    {
                        numero = numero / 10;
                    }
                    else if (pdigito < digito && condicao == '>')
                    {
                        numero = numero / 10;
                        condicao = '<';
                    }
                    else
                    {
                        condicao = 'n';
                    }
                }

                if (condicao == '<')
                {
                    qtdPicos++;
                    // teste que compara o maior digito do numero pico com o maior digito do numero atual, agora sabendo que ele é pico
                    if (maiorDigito > maiorDigitoPico)
                    {
                        maiorDigitoPico = maiorDigito;
                    }
                }
                else
                {
                    qtdNaoPicos++;
                    somaNaoPicos += input;
                }
                qtdNumeros++;
                //Console.WriteLine(condicao);

            } while (input != 0);

            string textoMaiorDigito = (maiorDigitoPico == 0) ? "N/A" : maiorDigitoPico.ToString("0");
            float media = (qtdNaoPicos > 0) ? (somaNaoPicos) / (qtdNaoPicos * 1.0F) : 0;
            string textoMedia = (qtdNaoPicos > 0) ? media.ToString("0.00") : "N/A";
            float porcentagemPicos = (qtdNumeros > 0) ? (qtdPicos / (qtdNumeros * 1.0F)) * 100 : 0;
            string textoPorcentagem = (qtdNumeros > 0) ? porcentagemPicos.ToString("0.00") + '%' : "0.00%";

            Console.WriteLine("Total de numeros picos: " + qtdPicos);
            Console.WriteLine("Maior digito em picos: " + textoMaiorDigito);
            Console.WriteLine("Media não-picos: " + textoMedia);
            Console.WriteLine("Porcentagem de picos: " + textoPorcentagem);

        }
    }
}
