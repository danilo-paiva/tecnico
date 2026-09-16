/* *******************************************************************
* Colegio Tecnico Antonio Teixeira Fernandes (Univap)
* Curso Tecnico em Informatica - Data de Entrega: 16/09/2026
* Autores do Projeto: Danilo Paiva
* Turma: 2H
* Atividade Proposta em aula
* Observacao: Exercicio 8 - Primeiro e Ultimo Dia do Mes
*
* projeto.cs
* *********************************************************************/


using System;
using System.Globalization;

namespace projeto
{
    class Program
    {
        static DateTime primeiroDia(DateTime data)
        {
            return new DateTime(data.Year, data.Month, 1);
        }

        static DateTime ultimoDia(DateTime data)
        {
            return new DateTime(data.Year, data.Month, DateTime.DaysInMonth(data.Year, data.Month));
        }

        static void Main(string[] args)
        {
            Console.Write("Digite uma data (dd/MM/yyyy): ");
            DateTime data = DateTime.ParseExact(Console.ReadLine(), "dd/MM/yyyy", CultureInfo.InvariantCulture);

            Console.WriteLine("Primeiro dia do mes: " + primeiroDia(data).ToString("dd/MM/yyyy"));
            Console.WriteLine("Ultimo dia do mes: " + ultimoDia(data).ToString("dd/MM/yyyy"));
        }
    }
}
