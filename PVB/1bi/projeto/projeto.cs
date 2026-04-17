/* *******************************************************************
* Colegio Técnico Antônio Teixeira Fernandes (Univap)
* Curso Técnico em Informática - Data de Entrega: 14/04/2026
* Autores do Projeto: Danilo Paiva
*                     Kelwin Mrinho
* Turma: 2H
* Atividade Proposta em aula
* Observação: 
* 
* projeto.cs
* *********************************************************************/


using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace projeto
{
    class Program
    {
        static void Main(string[] args)
        {
            int qtdPicos = 0, qtdNaoPicos = 0, qtdNumeros = 0, maiorDigitoPico = 0, maiorDigito = 0, somaNaoPicos = 0;
            int input, numero;
            char condicao;
          
            do
            {
                
                input = int.Parse(Console.ReadLine());

                numero = input;
                maiorDigito = numero % 10;

                if (input == 0)
                    continue;
                condicao = 'c';

                while (numero >= 10 && condicao != 'n')
                {
                    int digito = numero % 10;
                    int proxDigito = (numero / 10) % 10;

                    if (digito > maiorDigito)
                    {
                        maiorDigito = digito;
                    }
                    
                    if (proxDigito > digito && condicao == 'c')
                    {
                        numero /= 10;
                        condicao = '>';
                    }
                    else if(proxDigito > digito && condicao == '>')
                    {
                        numero /= 10;
                    }
                    else if (proxDigito < digito && condicao == '>')
                    {
                        numero /= 10;
                        condicao = '<';
                    }
                    else if (proxDigito < digito && condicao == '<')
                    {
                        numero /= 10;
                    }
                    else
                    {
                        condicao = 'n';
                    }
                }

                if (condicao == '<')
                {
                    qtdPicos++;
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

            } while (input != 0);

            float media = (qtdNaoPicos > 0) ? (somaNaoPicos) / (qtdNaoPicos * 1.0F) : 0;
            float porcentagemPicos = (qtdNumeros > 0) ? (qtdPicos / (qtdNumeros * 1.0F)) * 100 : 0;
            string textoMaiorPico = (qtdPicos > 0) ? maiorDigitoPico.ToString("0") : "N/A";

            Console.WriteLine("Total de numeros picos: " + qtdPicos);
            Console.WriteLine("Maior digito em picos: " + textoMaiorPico);
            Console.WriteLine("Media não-picos: " + media.ToString("0.00"));
            Console.WriteLine("Porcentagem de picos: " + porcentagemPicos.ToString("0.00") + '%');

        }
    }
}
