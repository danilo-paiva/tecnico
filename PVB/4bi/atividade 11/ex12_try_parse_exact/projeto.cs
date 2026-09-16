/* *******************************************************************
* Colegio Tecnico Antonio Teixeira Fernandes (Univap)
* Curso Tecnico em Informatica - Data de Entrega: 16/09/2026
* Autores do Projeto: Danilo Paiva
* Turma: 2H
* Atividade Proposta em aula
* Observacao: Exercicio 12 - Validacao e TryParseExact
*
* projeto.cs
* *********************************************************************/


using System;
using System.Globalization;

namespace projeto
{
    class Program
    {
        static DateTime lerValidade()
        {
            DateTime validade;
            bool valido;

            do
            {
                Console.Write("Digite a validade do cartao (MM/yy): ");
                valido = DateTime.TryParseExact(Console.ReadLine(), "MM/yy",
                    CultureInfo.InvariantCulture, DateTimeStyles.None, out validade);

                if (!valido)
                    Console.WriteLine("Erro: formato invalido! Use MM/yy (ex: 08/29).");
            } while (!valido);

            return validade;
        }

        static void Main(string[] args)
        {
            DateTime validade = lerValidade();
            Console.WriteLine("Validade aceita: " + validade.ToString("MM/yy"));
        }
    }
}
