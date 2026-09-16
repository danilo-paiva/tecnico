/* *******************************************************************
* Colegio Tecnico Antonio Teixeira Fernandes (Univap)
* Curso Tecnico em Informatica - Data de Entrega: 16/09/2026
* Autores do Projeto: Danilo Paiva
* Turma: 2H
* Atividade Proposta em aula
* Observacao: Exercicio 3 - Adicionando e Subtraindo Dias
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
            Console.Write("Digite uma data (dd/MM/yyyy): ");
            DateTime data = DateTime.ParseExact(Console.ReadLine(), "dd/MM/yyyy", CultureInfo.InvariantCulture);

            DateTime mais15 = data.AddDays(15);
            DateTime menos45 = data.AddDays(-45);

            Console.WriteLine("15 dias depois: " + mais15.ToString("dd/MM/yyyy"));
            Console.WriteLine("45 dias antes: " + menos45.ToString("dd/MM/yyyy"));
        }
    }
}
