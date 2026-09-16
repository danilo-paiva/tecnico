/* *******************************************************************
* Colegio Tecnico Antonio Teixeira Fernandes (Univap)
* Curso Tecnico em Informatica - Data de Entrega: 16/09/2026
* Autores do Projeto: Danilo Paiva
* Turma: 2H
* Atividade Proposta em aula
* Observacao: Exercicio 2 - Criando uma Data Especifica
*
* projeto.cs
* *********************************************************************/


using System;
using System.Globalization;

namespace projeto
{
    class Program
    {
        static void Main(string[] args)
        {
            DateTime natal = new DateTime(2026, 12, 25, 20, 0, 0);

            Console.WriteLine("Dia da semana em ingles: " + natal.ToString("dddd", new CultureInfo("en-US")));
            Console.WriteLine("Dia da semana em portugues: " + natal.ToString("dddd", new CultureInfo("pt-BR")));
        }
    }
}
