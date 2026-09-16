/* *******************************************************************
* Colegio Tecnico Antonio Teixeira Fernandes (Univap)
* Curso Tecnico em Informatica - Data de Entrega: 16/09/2026
* Autores do Projeto: Danilo Paiva
* Turma: 2H
* Atividade Proposta em aula
* Observacao: Exercicio 7 - Diferenca Entre Datas (TimeSpan)
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
            Console.Write("Digite a data de inicio do projeto (dd/MM/yyyy): ");
            DateTime inicio = DateTime.ParseExact(Console.ReadLine(), "dd/MM/yyyy", CultureInfo.InvariantCulture);

            Console.Write("Digite a data de fim do projeto (dd/MM/yyyy): ");
            DateTime fim = DateTime.ParseExact(Console.ReadLine(), "dd/MM/yyyy", CultureInfo.InvariantCulture);

            TimeSpan duracao = fim - inicio;

            Console.WriteLine("Total de dias: " + duracao.Days);
            Console.WriteLine("Total de horas: " + duracao.TotalHours);
        }
    }
}
