/* *******************************************************************
* Colegio Tecnico Antonio Teixeira Fernandes (Univap)
* Curso Tecnico em Informatica - Data de Entrega: 16/09/2026
* Autores do Projeto: Danilo Paiva
* Turma: 2H
* Atividade Proposta em aula
* Observacao: Exercicio 4 - Extracao de Componentes
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
            Console.Write("Digite a data e hora do seu nascimento (dd/MM/yyyy HH:mm): ");
            DateTime nascimento = DateTime.ParseExact(Console.ReadLine(), "dd/MM/yyyy HH:mm", CultureInfo.InvariantCulture);

            Console.WriteLine("Ano: " + nascimento.Year);
            Console.WriteLine("Mes (por extenso): " + nascimento.ToString("MMMM", new CultureInfo("pt-BR")));
            Console.WriteLine("Dia do mes: " + nascimento.Day);
            Console.WriteLine("Hora: " + nascimento.Hour);
            Console.WriteLine("Minuto: " + nascimento.Minute);
        }
    }
}
