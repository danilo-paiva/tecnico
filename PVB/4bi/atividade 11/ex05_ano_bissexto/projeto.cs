/* *******************************************************************
* Colegio Tecnico Antonio Teixeira Fernandes (Univap)
* Curso Tecnico em Informatica - Data de Entrega: 16/09/2026
* Autores do Projeto: Danilo Paiva
* Turma: 2H
* Atividade Proposta em aula
* Observacao: Exercicio 5 - Verificacao de Ano Bissexto
*
* projeto.cs
* *********************************************************************/


using System;

namespace projeto
{
    class Program
    {
        static void Main(string[] args)
        {
            Console.Write("Digite um ano (ex: 2024): ");
            int ano = int.Parse(Console.ReadLine());

            if (DateTime.IsLeapYear(ano))
                Console.WriteLine("O ano " + ano + " e bissexto.");
            else
                Console.WriteLine("O ano " + ano + " nao e bissexto.");
        }
    }
}
