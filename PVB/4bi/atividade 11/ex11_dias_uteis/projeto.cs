/* *******************************************************************
* Colegio Tecnico Antonio Teixeira Fernandes (Univap)
* Curso Tecnico em Informatica - Data de Entrega: 16/09/2026
* Autores do Projeto: Danilo Paiva
* Turma: 2H
* Atividade Proposta em aula
* Observacao: Exercicio 11 - Contagem de Dias Uteis
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
            Console.Write("Digite a data inicial (dd/MM/yyyy): ");
            DateTime data = DateTime.ParseExact(Console.ReadLine(), "dd/MM/yyyy", CultureInfo.InvariantCulture);

            Console.Write("Digite o numero de dias uteis: ");
            int diasUteis = int.Parse(Console.ReadLine());

            int contagem = 0;
            while (contagem < diasUteis)
            {
                data = data.AddDays(1);
                if (data.DayOfWeek != DayOfWeek.Saturday && data.DayOfWeek != DayOfWeek.Sunday)
                    contagem = contagem + 1;
            }

            Console.WriteLine("Data final: " + data.ToString("dd/MM/yyyy") + " (" + data.ToString("dddd", new CultureInfo("pt-BR")) + ")");
        }
    }
}
