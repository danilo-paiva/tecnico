/* *******************************************************************
* Colegio Técnico Antônio Teixeira Fernandes (Univap)
* Curso Técnico em Informática - Data de Entrega: ___/___/2026
* Autores do Projeto: Danilo Paiva
*                     Kelwin Marinho
* Turma: 2H
* Projeto do 3o Bimestre - Calculadora de Trajetoria Interplanetaria
* Observação: Aplicacao WPF para a agencia espacial AeroSpaceX.
*             Calcula o delta-v (Δv), a duracao da viagem e a
*             estimativa de consumo de combustivel de missoes
*             interplanetarias utilizando a manobra de Transferencia
*             de Hohmann e a Equacao do Foguete de Tsiolkovsky.
*             Técnicas utilizadas conforme o material de aulas:
*             - Aula 03/04: TextBox multilinha (AcceptsReturn), Labels
*               e Botoes de comando com eventos Click;
*             - Aula 05: Math.Sqrt, Math.Pow, Math.Abs, Math.PI;
*             - Aula 06: ToString("0.000"), String.Format, {0:F1},
*               {0:G}, casas decimais e separador de milhar;
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

        // Botao "Calcular Trajetoria"
        private void Button_Calcular_Click(object sender, RoutedEventArgs e)
        {
            // Leitura da caixa de texto multilinha:
            // linha 1 = r1, linha 2 = r2, linha 3 = massa, linha 4 = Isp
            // (digitacao com virgula decimal, ex.: 149,6)
            double r1 = double.Parse(entrada.GetLineText(0));
            double r2 = double.Parse(entrada.GetLineText(1));
            double massa = double.Parse(entrada.GetLineText(2));
            double isp = double.Parse(entrada.GetLineText(3));


            // As distancias sao informadas em MILHOES de km -> converter para km
            r1 = r1 * 1000000;
            r2 = r2 * 1000000;

            // Constante gravitacional do Sol (mu), em km^3/s^2
            // (1.327 x 10^11 = 132700000000)
            double mu = 132700000000;

            // 1) Semi-eixo maior da orbita de transferencia (a), em km
            double a = (r1 + r2) / 2;

            // 2) Tempo de viagem (Ttrans), em segundos
            double t_trans = Math.PI * Math.Sqrt(Math.Pow(a, 3) / mu);

            // Conversao para dias (86400 segundos por dia) e meses (30 dias)
            double dias = t_trans / 86400;
            double meses = dias / 30;

            // 3) Delta-V das duas queimaduras (em km/s)
            double dv1 = Math.Sqrt(mu / r1) * (Math.Sqrt(2 * r2 / (r1 + r2)) - 1);
            double dv2 = Math.Sqrt(mu / r2) * (1 - Math.Sqrt(2 * r1 / (r1 + r2)));
            double dv_total = Math.Abs(dv1) + Math.Abs(dv2);

            // 4) Equacao do Foguete de Tsiolkovsky
            //    razao de massa = e^(Δv / (Isp * g))   (Δv em m/s, g = 9.81)
            //    e^x = Math.Pow(Math.E, x)
            double dv_ms = dv_total * 1000;
            double razao = Math.Pow(Math.E, dv_ms / (isp * 9.81));
            double m_combustivel = massa * (razao - 1);
            double toneladas = m_combustivel / 1000;

            // Saida formatada (String.Format e ToString - aula 06)
            // Tempo de viagem: dias inteiros e meses com 1 casa decimal
            // Dias TRUNCADOS (258,84 -> 258), igual ao PDF do enunciado.
            // CASO PRECISE VOLTAR PARA O ARREDONDAMENTO (258,84 -> 259),
            // troque a linha abaixo por:
            //   String.Format("{0:0} dias (~{1:F1} meses)", dias, meses);
            tempo_viagem.Content = "Tempo de viagem: " +
                String.Format("{0:0} dias (~{1:F1} meses)", Math.Truncate(dias), meses);

            // Delta-V total com 3 casas decimais + unidade
            // (equivale ao ToString("N3") pedido no enunciado)
            delta_v_total.Content = "Δv total: " + dv_total.ToString("0.000") + " km/s";

            // Razao de massa com 4 casas decimais
            razao_massa.Content = "Razão de massa: " + razao.ToString("0.0000");

            // Combustivel em kg (2 casas decimais com separador de milhar)
            // e em toneladas via String.Format com especificador generico (G)
            // e unidade customizada (t)
            combustivel.Content = "Combustível necessário: " +
                String.Format("{0:0,0.00} kg ({1:G} t)", m_combustivel,
                              Math.Round(toneladas, 2));
        }

        // Botao "Limpar Campos"
        private void Button_Limpar_Click(object sender, RoutedEventArgs e)
        {
            entrada.Text = "";
            tempo_viagem.Content = "";
            delta_v_total.Content = "";
            razao_massa.Content = "";
            combustivel.Content = "";
        }
    }
}
