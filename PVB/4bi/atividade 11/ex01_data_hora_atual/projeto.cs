/* *******************************************************************
* Colegio Tecnico Antonio Teixeira Fernandes (Univap)
* Curso Tecnico em Informatica - Data de Entrega: 16/09/2026
* Autores do Projeto: Danilo Paiva
* Turma: 2H
* Atividade Proposta em aula
* Observacao: Exercicio 1 - Data e Hora Atuais
*
* projeto.cs
* *********************************************************************/


using System;

namespace projeto
{
    class Program
    {
        static void Main(string[] args)
        {
            DateTime agora = DateTime.Now;

            Console.WriteLine("Formatado: " + agora.ToString("dd/MM/yyyy HH:mm:ss"));
            Console.WriteLine("Apenas a data: " + agora.ToString("dd/MM/yyyy"));
            Console.WriteLine("Apenas a hora: " + agora.ToString("HH:mm"));
        }
    }
}
