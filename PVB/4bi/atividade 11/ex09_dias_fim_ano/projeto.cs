/* *******************************************************************
* Colegio Tecnico Antonio Teixeira Fernandes (Univap)
* Curso Tecnico em Informatica - Data de Entrega: 16/09/2026
* Autores do Projeto: Danilo Paiva
* Turma: 2H
* Atividade Proposta em aula
* Observacao: Exercicio 9 - Quantos Dias Faltam para o Fim do Ano
*
* projeto.cs
* *********************************************************************/


using System;
using System.Globalization;

namespace projeto
{
    class Program
    {
        static int diasParaFimDoAno()
        {
            DateTime hoje = DateTime.Today;
            DateTime fimAno = new DateTime(hoje.Year, 12, 31);
            TimeSpan diferenca = fimAno - hoje;
            return diferenca.Days;
        }

        static void Main(string[] args)
        {
            Console.WriteLine("Faltam " + diasParaFimDoAno() + " dias para o fim do ano.");
        }
    }
}
