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
            double somatoria_x = 0, somatoria_quadrado_x = 0;
            double max = 0, min = 0;
            for (int i = 0; i < n; i++)
            {
                double valor = double.Parse(input.GetLineText(i));
                if (valor > max)
                    max = valor;
                if (valor < min)
                    min = valor;
                somatoria_x += valor;
                somatoria_quadrado_x += valor * valor;
            }

            double media = somatoria_x / n;
            double varianca_amostral = (somatoria_quadrado_x - somatoria_x*somatoria_x / n) / (n - 1);
            double desvio_padrao = Math.Sqrt(varianca_amostral);

            elementos.Content = "Quantidade de elementos: " + n.ToString("0");
            val_min.Content = "Valor Mínimo: " + min.ToString("0.00");
            val_max.Content = "Valor Máximo: " + max.ToString("0.00");
            media_aritimetica.Content = "Média Aritmética: " + media.ToString("0.00");
            varianca.Content = "Variância Amostral: " + varianca_amostral.ToString("0.00");
            desvio.Content = "Desvio Padrão Amostral: " + desvio_padrao.ToString("0.00");

       }
    }
}
