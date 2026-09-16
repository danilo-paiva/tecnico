/* *******************************************************************
* Colegio Tecnico Antonio Teixeira Fernandes (Univap)
* Curso Tecnico em Informatica - Data de Entrega: 16/09/2026
* Autores do Projeto: Danilo Paiva
* Turma: 2H
* Atividade Proposta em aula
* Observacao: Exercicio 10 - Comparacao de Datas
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
            Console.Write("Digite a primeira data (dd/MM/yyyy): ");
            DateTime data1 = DateTime.ParseExact(Console.ReadLine(), "dd/MM/yyyy", CultureInfo.InvariantCulture);

            Console.Write("Digite a segunda data (dd/MM/yyyy): ");
            DateTime data2 = DateTime.ParseExact(Console.ReadLine(), "dd/MM/yyyy", CultureInfo.InvariantCulture);

            int comparacao = DateTime.Compare(data1, data2);

            if (comparacao < 0)
                Console.WriteLine("A data mais recente e: " + data2.ToString("dd/MM/yyyy"));
            else if (comparacao > 0)
                Console.WriteLine("A data mais recente e: " + data1.ToString("dd/MM/yyyy"));
            else
                Console.WriteLine("As duas datas sao identicas.");
        }
    }
}
