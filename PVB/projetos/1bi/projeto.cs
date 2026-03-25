
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
            int qtdPicos = 0,qtdNaoPicos = 0, qtdNumeros = 0;
            int maiorDigitoPico = 0, maiorDigito = 0;
            int somaNaoPicos = 0;
            int input, numero;
            char condicao;

            do
            {
                input = int.Parse(Console.ReadLine());
                // impede numeros negativos
                while(input<0)
                {
                    input = int.Parse(Console.ReadLine());
                }
                numero = input;
                if (input == 0)
                    break;
                if (input < 120)
                    condicao = 'n';
                else
                    condicao = 'c';
                while(numero>=10 && condicao!= 'n')
                {
                    int digito = numero % 10;
                    int pdigito = ((numero - digito)/10) % 10;

                    // teste do maior digito do numero digitado
                    if(digito>maiorDigito)
                    {
                        maiorDigito = digito;
                    }
                    //Console.Write(pdigito);
                    //Console.WriteLine(digito);
                    //Console.WriteLine(input);

                    if (pdigito > digito && (condicao == 'c' || condicao == '>'))
                    {
                        numero = (numero - digito) / 10;
                        condicao = '>';
                    }
                    else
                    {
                        if (pdigito < digito && condicao == '<')
                        {
                            numero = (numero - digito) / 10;
                        }
                        else
                        {
                            if (pdigito < digito && condicao == '>')
                            {
                                numero = (numero - digito) / 10;
                                condicao = '<';
                            }
                            else
                            {
                                condicao = 'n';
                            }
                        }
                    }
                    //Console.WriteLine(condicao);    
                }

                if (condicao == '<')
                {
                    qtdPicos++; 
                    // teste que compara o maior digito do numero pico com o maior digito do numero atual, agora sabendo que ele é pico
                    if(maiorDigito>maiorDigitoPico)
                    {
                        maiorDigitoPico = maiorDigito;
                    }
                    //Console.WriteLine("eh pico");
                } 
                else
                {
                    if(input!=0)
                    {
                        qtdNaoPicos++;
                        somaNaoPicos += input;
                    }
                }
                qtdNumeros++;
                //Console.WriteLine(condicao);

            } while (input!=0);


            string textoMaiorDigito = (maiorDigitoPico == 0) ? "N/A" : maiorDigitoPico.ToString("0");
            float media = (qtdNaoPicos>0) ? (somaNaoPicos) / (qtdNaoPicos*1.0F) : 0;
            string textoMedia = (qtdNaoPicos > 0) ? media.ToString("0.00") : "N/A";
            float porcentagemPicos = (qtdNumeros > 0) ? (qtdPicos / (qtdNumeros*1.0F)) * 100 : 0;
            string textoPorcentagem = (qtdNumeros > 0 ) ? porcentagemPicos.ToString("0.00")+'%' : "0.00%";

            Console.WriteLine("Total de numeros picos: " + qtdPicos);
            Console.WriteLine("Maior digito em picos: " + textoMaiorDigito);
            Console.WriteLine("Media não-picos: " + textoMedia);
            Console.WriteLine("Porcentagem de picos: " + textoPorcentagem);


        }
    }
}
