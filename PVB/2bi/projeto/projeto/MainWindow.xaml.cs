/* *******************************************************************
* Colegio Técnico Antônio Teixeira Fernandes (Univap)
* Curso Técnico em Informática - Data de Entrega: 27/05/2026
* Autores do Projeto: Danilo Paiva
*                     Kelwin Marinho
* Turma: 2H
* Atividade Proposta em aula
* Observação: <colocar se houver>
* 
* MainWindow.xaml.cs
* ************************************************************/

using System;
using System.Windows;


namespace projeto
{

    public partial class MainWindow : Window
    {
        public MainWindow()
        {
            InitializeComponent();
        }

        private void Button_Click(object sender, RoutedEventArgs e)
        {
            int n = input.LineCount;
            double somatoria_x = 0,somatoria_quadrado_x = 0, soma_quadrados = 0;
            double max = double.Parse(input.GetLineText(0)), min = double.Parse(input.GetLineText(0));
            for (int i = 0; i < n; i++)
            {
                double valor = double.Parse(input.GetLineText(i));
                somatoria_x += valor;
                somatoria_quadrado_x += valor * valor;
                if (valor > max)
                    max = valor;
                if (valor < min)
                    min = valor;
            }

            double media = somatoria_x / n;

            for (int i = 0; i < n; i++)
            {
                double valor = double.Parse(input.GetLineText(i));
                soma_quadrados += (valor - media,2)*(valor - media,2);
            }

            double varianca_amostral = (somatoria_quadrado_x - ((somatoria_x*somatoria_x) / n) / (n - 1);
            double desvio_padrao = Math.Sqrt(soma_quadrados / (n - 1));

            resultados.Content = "Quantidade de elementos: " + n.ToString("0")
                + "\nValor Mínimo: " + min.ToString("0.00") 
                + "\nValor Máximo: " + max.ToString("0.00")
                + "\nMédia Aritmética: " + media.ToString("0.00")
                + "\nVariância Amostral: " + varianca_amostral.ToString("0.00")
                + "\nDesvio Padrão Amostral: " + desvio_padrao.ToString("0.00");

       }
    }
}
