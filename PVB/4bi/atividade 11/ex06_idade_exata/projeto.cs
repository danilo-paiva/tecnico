/* *******************************************************************
* Colegio Tecnico Antonio Teixeira Fernandes (Univap)
* Curso Tecnico em Informatica - Data de Entrega: 16/09/2026
* Autores do Projeto: Danilo Paiva
* Turma: 2H
* Atividade Proposta em aula
* Observacao: Exercicio 6 - Calculando a Idade Exata
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
            Console.Write("Digite sua data de nascimento (dd/MM/yyyy): ");
            DateTime nascimento = DateTime.ParseExact(Console.ReadLine(), "dd/MM/yyyy", CultureInfo.InvariantCulture);

            DateTime hoje = DateTime.Today;
            int idade = hoje.Year - nascimento.Year;

            if (hoje.Month < nascimento.Month || (hoje.Month == nascimento.Month && hoje.Day < nascimento.Day))
                idade = idade - 1;

            Console.WriteLine("Idade exata: " + idade + " anos");
        }
    }
}
