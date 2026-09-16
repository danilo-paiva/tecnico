/* *******************************************************************
* Colegio Tecnico Antonio Teixeira Fernandes (Univap)
* Curso Tecnico em Informatica - Data de Entrega: 16/09/2026
* Autores do Projeto: Danilo Paiva
* Turma: 2H
* Atividade Proposta em aula
* Observacao: Exercicio 13 - Calculadora de Pagamento (Prazo de Vencimento)
*
* projeto.cs
* *********************************************************************/


using System;
using System.Globalization;

namespace projeto
{
    class Program
    {
        static DateTime calcularVencimento(DateTime emissao)
        {
            int ano = emissao.Year;
            int mes = emissao.Month + 1;

            if (mes > 12)
            {
                mes = 1;
                ano = ano + 1;
            }

            DateTime vencimento = new DateTime(ano, mes, 10);

            if (vencimento.DayOfWeek == DayOfWeek.Saturday)
                vencimento = vencimento.AddDays(2);
            else if (vencimento.DayOfWeek == DayOfWeek.Sunday)
                vencimento = vencimento.AddDays(1);

            return vencimento;
        }

        static void Main(string[] args)
        {
            Console.Write("Digite a data de emissao da fatura (dd/MM/yyyy): ");
            DateTime emissao = DateTime.ParseExact(Console.ReadLine(), "dd/MM/yyyy", CultureInfo.InvariantCulture);

            DateTime vencimento = calcularVencimento(emissao);

            Console.WriteLine("Vencimento: " + vencimento.ToString("dd/MM/yyyy") + " (" + vencimento.ToString("dddd", new CultureInfo("pt-BR")) + ")");
        }
    }
}
