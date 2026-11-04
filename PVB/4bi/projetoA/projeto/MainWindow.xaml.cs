using System;
using System.IO;
using System.Windows;
using System.Windows.Controls;

namespace projetoA
{
    public partial class App : Application
    {
    }

    /***************************************************************
    * PROJETO DO 4o BIMESTRE - IMC EM CONSULTORIO DE FISIOTERAPIA
    * Implementacao A - vetores paralelos (um vetor por campo).
    *
    * Uma unica janela: formulario, lista de pacientes, relatorio
    * (caixa de texto multilinha) e rodape com as mensagens.
    * Arquivos: pessoas.json, grafico.html e pessoas.html
    *
    * Todo o codigo esta nesta classe, como no class Program do
    * material: a janela (metodos de evento) e as funcoes e
    * procedimentos do programa (metodos static, chamados pelo
    * nome, sem criar objeto - aula 08).
    * Leitura/gravacao: somente a classe File do System.IO.
    *
    * Template gravado em pessoas.json (item 1):
    * [
    *     {
    *         "Id": 1,
    *         "Nome": "Joao Silva",
    *         "Peso (Kg)": 70,
    *         "Altura (m)": 1.75,
    *         "IMC": 22.86,
    *         "Classificação": "Peso Normal"
    *     }
    * ]
    *
    * Seccoes: dados (vetores), tela, relatorio/IMC, leitura do JSON,
    * gravacao do JSON, operacoes (CRUD) e geracao dos HTML.
    ***************************************************************/
    public partial class MainWindow : Window
    {
        // Nomes relativos: os arquivos ficam na pasta de trabalho do
        // programa (a pasta do projeto no F5; a do .exe na publicacao).
        string arquivo = "pessoas.json";
        string grafico = "grafico.html";
        string dados = "pessoas.html";

        // -------------------------------------------------------
        // Dados: vetores paralelos
        // -------------------------------------------------------
        // Cada posicao dos seis vetores forma um registro (pessoa).
        // Quantidade e o indice da proxima posicao livre; MAX e o limite.
        public const int MAX = 100;
        public static int[] Ids = new int[MAX];
        public static string[] Nomes = new string[MAX];
        public static double[] Pesos = new double[MAX];
        public static double[] Alturas = new double[MAX];
        public static double[] Imcs = new double[MAX];
        public static string[] Classes = new string[MAX];
        public static int Quantidade = 0;

        // Ao abrir a janela: le o JSON e monta a lista/relatorio.
        public MainWindow()
        {
            InitializeComponent();

            Carregar(arquivo);
            AtualizarTela();
        }

        // -------------------------------------------------------
        // Tela
        // -------------------------------------------------------
        // Monta as linhas da lista (item 4: o peso aparece na caixa),
        // atualiza o relatorio e o rodape.
        void AtualizarTela()
        {
            string lista = "";

            for (int i = 0; i < Quantidade; i++)
                lista += "Id " + Ids[i] + " - " + Nomes[i] +
                    " - " + Pesos[i].ToString("0.##") + " kg" +
                    " - IMC " + Imcs[i].ToString("0.00") +
                    " - " + Classes[i] + Environment.NewLine;

            txtLista.Text = lista;

            txtRelatorio.Text = Relatorio();
            lblStatus.Content = Quantidade + " pessoa(s) em " + Nome(arquivo);
        }

        // Joga os dados da posicao i nos campos da tela.
        void Mostrar(int i)
        {
            // Coloca o Id do registro no campo Id da tela
            // (para consultar, alterar ou excluir pelo Id).
            txtId.Text = Ids[i].ToString();
            txtNome.Text = Nomes[i];
            txtPeso.Text = Pesos[i].ToString("0.##");
            txtAltura.Text = Alturas[i].ToString("0.##");
        }


        // -------------------------------------------------------
        // Operacoes (itens 5 e 6 do enunciado)
        // -------------------------------------------------------
        // Todos os botoes leem o Id digitado; consultar/alterar/excluir
        // usam Buscar() e param quando o Id nao existe.
        // Cadastrar: le os tres campos, grava no fim dos vetores e regrava o JSON.
        private void btCadastrar_Click(object sender, RoutedEventArgs e)
        {
            string nome = txtNome.Text.Trim();
            double peso = Numero(txtPeso.Text);
            double altura = Numero(txtAltura.Text);

            if (Quantidade >= MAX)
            {
                lblStatus.Content = "Limite de " + MAX + " registros.";
                return;
            }

            int i = Adicionar(nome, peso, altura);
            Salvar(arquivo);

            AtualizarTela();
            Mostrar(i);

            lblStatus.Content = "Id " + Ids[i] + " cadastrado.";
        }

        // Consultar: busca pelo Id digitado e preenche os campos da tela.
        private void btConsultar_Click(object sender, RoutedEventArgs e)
        {
            int id = int.Parse(txtId.Text);

            int i = Buscar(id);

            if (i < 0)
            {
                lblStatus.Content = "Nenhum registro com Id " + id + ".";
                return;
            }

            Mostrar(i);
            lblStatus.Content = "Id " + id + " encontrado.";
        }

        // Alterar: busca pelo Id, troca nome/peso/altura e regrava o JSON.
        private void btAlterar_Click(object sender, RoutedEventArgs e)
        {
            int id = int.Parse(txtId.Text);

            int i = Buscar(id);

            if (i < 0)
            {
                lblStatus.Content = "Nenhum registro com Id " + id + ".";
                return;
            }

            string nome = txtNome.Text.Trim();
            double peso = Numero(txtPeso.Text);
            double altura = Numero(txtAltura.Text);

            Alterar(i, nome, peso, altura);
            Salvar(arquivo);

            AtualizarTela();
            Mostrar(i);

            lblStatus.Content = "Id " + id + " alterado.";
        }

        // Excluir: busca pelo Id, remove do vetor (deslocando os demais) e regrava.
        private void btExcluir_Click(object sender, RoutedEventArgs e)
        {
            int id = int.Parse(txtId.Text);

            int i = Buscar(id);

            if (i < 0)
            {
                lblStatus.Content = "Nenhum registro com Id " + id + ".";
                return;
            }

            Excluir(i);
            Salvar(arquivo);

            AtualizarTela();
            txtId.Text = "";
            txtNome.Text = "";
            txtPeso.Text = "";
            txtAltura.Text = "";

            lblStatus.Content = "Id " + id + " excluido.";
        }


        // -------------------------------------------------------
        // Relatorio e HTML (itens 7, 9, 10 e 11)
        // -------------------------------------------------------
        // Monta o relatorio do item 7 (todos os pacientes + IMC + classe).
        string Relatorio()
        {
            string texto = "RELATORIO DE AVALIACOES - CONSULTORIO DE FISIOTERAPIA" + Environment.NewLine;
            texto += "======================================================" + Environment.NewLine;

            for (int i = 0; i < Quantidade; i++)
            {
                texto += "Id: " + Ids[i] + Environment.NewLine;
                texto += "Nome: " + Nomes[i] + Environment.NewLine;
                texto += "Peso: " + Pesos[i].ToString("0.##") + " kg" + Environment.NewLine;
                texto += "Altura: " + Alturas[i].ToString("0.##") + " m" + Environment.NewLine;
                texto += "IMC: " + Imcs[i].ToString("0.00") + Environment.NewLine;
                texto += "Classificacao: " + Classes[i] + Environment.NewLine;
                texto += "------------------------------------------------------" + Environment.NewLine;
            }

            // Conta quantas pessoas ha em cada faixa de IMC.
            // contagem[0] = Magreza ... contagem[5] = Obesidade Grau III;
            // o rodape do relatorio usa esses totais.
            int[] contagem = ContarFaixas();

            texto += "Total de pessoas: " + Quantidade + Environment.NewLine;

            for (int f = 0; f < 6; f++)
                texto += NomeFaixa(f) + ": " + contagem[f] + Environment.NewLine;

            return texto;
        }

        private void btRelatorio_Click(object sender, RoutedEventArgs e)
        {
            txtRelatorio.Text = Relatorio();
            lblStatus.Content = "Relatorio atualizado.";
        }

        private void btGrafico_Click(object sender, RoutedEventArgs e)
        {
            GerarGrafico(grafico);
            lblStatus.Content = "Arquivo " + Nome(grafico) + " gerado.";
        }

        private void btDados_Click(object sender, RoutedEventArgs e)
        {
            GerarDados(dados);
            lblStatus.Content = "Arquivo " + Nome(dados) + " gerado.";
        }

        // -------------------------------------------------------
        // IMC (item 8): aula 05 (Math.Pow / Math.Round) e aula 08
        // -------------------------------------------------------
        // IMC = peso / altura^2, arredondado em duas casas (item 8).
        public static double CalcularImc(double peso, double altura)
        {
            return Math.Round(peso / Math.Pow(altura, 2), 2);
        }

        // 0 <18,5 | 1 18,5-24,9 | 2 25-29,9 | 3 30-34,9 | 4 35-39,9 | 5 >=40
        public static int Faixa(double imc)
        {
            if (imc < 18.5) return 0;
            if (imc < 25.0) return 1;
            if (imc < 30.0) return 2;
            if (imc < 35.0) return 3;
            if (imc < 40.0) return 4;
            return 5;
        }

        // Classificacao por extenso a partir da mesma faixa do grafico.
        public static string Classificar(double imc)
        {
            return NomeFaixa(Faixa(imc));
        }

        // Nomes usados no relatorio, na ordem 0..5.
        public static string NomeFaixa(int faixa)
        {
            string[] nomes = new string[] { "Magreza", "Peso Normal", "Sobrepeso",
                "Obesidade Grau I", "Obesidade Grau II", "Obesidade Grau III" };

            return nomes[faixa];
        }

        // -------------------------------------------------------
        // Leitura do JSON, linha a linha (aula 08: Trim,
        // StartsWith, EndsWith, IndexOf, Substring, Replace)
        // -------------------------------------------------------
        // Le o arquivo linha a linha. Cada chave ("Id", "Nome", ...)
        // preenche a posicao Quantidade; a linha "}" fecha o registro
        // e passa para a proxima posicao.
        public static void Carregar(string caminho)
        {
            Quantidade = 0;

            // Primeira execucao: o arquivo ainda nao existe. Sem esta
            // linha, File.ReadAllLines lancaria FileNotFoundException.
            if (!File.Exists(caminho)) return;

            string[] linhas = File.ReadAllLines(caminho);

            for (int l = 0; l < linhas.Length; l++)
            {
                if (Quantidade >= MAX) break;

                string linha = linhas[l].Trim();

                if (linha.StartsWith("\"Id\""))
                    Ids[Quantidade] = int.Parse(Valor(linha));
                else if (linha.StartsWith("\"Nome\""))
                    Nomes[Quantidade] = Valor(linha);
                else if (linha.StartsWith("\"Peso"))
                    Pesos[Quantidade] = Numero(Valor(linha));
                else if (linha.StartsWith("\"Altura"))
                    Alturas[Quantidade] = Numero(Valor(linha));
                else if (linha.StartsWith("\"IMC\""))
                    Imcs[Quantidade] = Numero(Valor(linha));
                else if (linha.StartsWith("\"Classifica"))
                    Classes[Quantidade] = Valor(linha);
                else if (linha.StartsWith("}"))
                    Quantidade++;
            }
        }

        // Valor que vem depois dos dois-pontos, sem espacos, aspas e virgula.
        // Extrai o valor depois dos dois-pontos. Exemplo:
        //     "Nome": "Joao Silva",  ->  Joao Silva
        // Remove espacos, aspas e a virgula do fim da linha.
        public static string Valor(string linha)
        {
            int p = linha.IndexOf(':');
            string v = linha.Substring(p + 1).Trim().Replace("\"", "");

            if (v.EndsWith(","))
                v = v.Substring(0, v.Length - 1);

            return v.Trim();
        }

        // O JSON usa ponto decimal: troca por virgula antes do Parse.
        public static double Numero(string texto)
        {
            return double.Parse(texto.Trim().Replace(".", ","));
        }

        // -------------------------------------------------------
        // Gravacao do JSON
        // -------------------------------------------------------
        // Grava o template do enunciado: um objeto por pessoa, campos
        // com ponto decimal, virgula entre campos e "}," entre objetos.
        public static void Salvar(string caminho)
        {
            string texto = "[" + Environment.NewLine;

            for (int i = 0; i < Quantidade; i++)
            {
                texto += "    {" + Environment.NewLine;
                texto += "        \"Id\": " + Ids[i] + "," + Environment.NewLine;
                texto += "        \"Nome\": \"" + Limpar(Nomes[i]) + "\"," + Environment.NewLine;
                texto += "        \"Peso (Kg)\": " + ComPonto(Pesos[i], "0.##") + "," + Environment.NewLine;
                texto += "        \"Altura (m)\": " + ComPonto(Alturas[i], "0.##") + "," + Environment.NewLine;
                texto += "        \"IMC\": " + ComPonto(Imcs[i], "0.00") + "," + Environment.NewLine;
                texto += "        \"Classificação\": \"" + Limpar(Classes[i]) + "\"" + Environment.NewLine;

                if (i < Quantidade - 1)
                    texto += "    }," + Environment.NewLine;
                else
                    texto += "    }" + Environment.NewLine;
            }

            texto += "]" + Environment.NewLine;

            File.WriteAllText(caminho, texto);
        }

        // O ToString do Windows usa virgula; o JSON usa ponto.
        static string ComPonto(double valor, string formato)
        {
            return valor.ToString(formato).Replace(",", ".");
        }

        // Aspas dentro do nome quebrariam o JSON.
        static string Limpar(string texto)
        {
            if (texto == null) return "";

            return texto.Replace("\"", "'");
        }

        // -------------------------------------------------------
        // Operacoes nos vetores
        // -------------------------------------------------------
        // Maior Id existente + 1 (o primeiro cadastro recebe 1).
        public static int ProximoId()
        {
            int maior = 0;

            for (int i = 0; i < Quantidade; i++)
                if (Ids[i] > maior) maior = Ids[i];

            return maior + 1;
        }

        // Devolve a posicao do Id no vetor, ou -1 se nao existir.
        public static int Buscar(int id)
        {
            for (int i = 0; i < Quantidade; i++)
                if (Ids[i] == id) return i;

            return -1;
        }

        // Cria o registro na proxima posicao livre e calcula IMC/classe.
        public static int Adicionar(string nome, double peso, double altura)
        {
            int i = Quantidade;

            Ids[i] = ProximoId();
            Nomes[i] = nome;
            Pesos[i] = peso;
            Alturas[i] = altura;
            Imcs[i] = CalcularImc(peso, altura);
            Classes[i] = Classificar(Imcs[i]);
            Quantidade++;

            return i;
        }

        // Troca os campos da posicao e recalcula o IMC (o Id nao muda).
        public static void Alterar(int posicao, string nome, double peso, double altura)
        {
            if (posicao < 0 || posicao >= Quantidade) return;

            Nomes[posicao] = nome;
            Pesos[posicao] = peso;
            Alturas[posicao] = altura;
            Imcs[posicao] = CalcularImc(peso, altura);
            Classes[posicao] = Classificar(Imcs[posicao]);
        }

        // Remove a posicao deslocando os seguintes uma casa a esquerda.
        public static void Excluir(int posicao)
        {
            if (posicao < 0 || posicao >= Quantidade) return;

            for (int i = posicao; i < Quantidade - 1; i++)
            {
                Ids[i] = Ids[i + 1];
                Nomes[i] = Nomes[i + 1];
                Pesos[i] = Pesos[i + 1];
                Alturas[i] = Alturas[i + 1];
                Imcs[i] = Imcs[i + 1];
                Classes[i] = Classes[i + 1];
            }

            Quantidade--;
        }

        // Conta quantas pessoas estao em cada faixa de IMC.
        // Faixa(Imcs[i]) devolve 0..5; o vetor contagem guarda os totais
        // e e usado no rodape do relatorio e no vetor "r" do grafico.
        public static int[] ContarFaixas()
        {
            int[] contagem = new int[6];

            for (int i = 0; i < Quantidade; i++)
                contagem[Faixa(Imcs[i])]++;

            return contagem;
        }

        // -------------------------------------------------------
        // grafico.html - template do enunciado com o vetor r:
        // montado pelas faixas de IMC do arquivo (item 9)
        // -------------------------------------------------------
        // GerarGrafico: o vetor "r" e a contagem de pessoas em cada faixa
        // de IMC; "theta" sao os rotulos fixos do template; o range
        // [0, 50] tambem vem do template do enunciado.
        public static void GerarGrafico(string caminho)
        {
            int[] contagem = ContarFaixas();

            string html = "";

            html += "<!DOCTYPE html>" + Environment.NewLine;
            html += "<html lang=\"en\">" + Environment.NewLine;
            html += "<head>" + Environment.NewLine;
            html += "<meta charset=\"UTF-8\">" + Environment.NewLine;
            html += "<title>Document</title>" + Environment.NewLine;
            html += "<script src='https://cdn.plot.ly/plotly-2.27.0.min.js'></script>" + Environment.NewLine;
            html += "</head>" + Environment.NewLine;
            html += "<body>" + Environment.NewLine;
            html += "<center><div id='myDiv' style=\"width: 500px; height:500px;\">Pesquisa de Indice de Massa Corporal</div></center>" + Environment.NewLine;
            html += "<script>" + Environment.NewLine;
            html += "var data = [{" + Environment.NewLine;
            html += "type: 'scatterpolar'," + Environment.NewLine;
            html += "r: [" + contagem[0] + ", " + contagem[1] + ", " + contagem[2] + ", " +
                                 contagem[3] + ", " + contagem[4] + ", " + contagem[5] + "]," + Environment.NewLine;
            html += "theta: ['Magreza','Saúdavel','Sobrepeso', 'OBG-I', 'OBG-II', 'OBG-III'], fill: 'toself'" + Environment.NewLine;
            html += "}]" + Environment.NewLine;
            html += "layout = { polar: {" + Environment.NewLine;
            html += "radialaxis: { visible: true, range: [0, 50]" + Environment.NewLine;
            html += "}" + Environment.NewLine;
            html += "}," + Environment.NewLine;
            html += "showlegend: false" + Environment.NewLine;
            html += "}" + Environment.NewLine;
            html += "Plotly.newPlot(\"myDiv\", data, layout)" + Environment.NewLine;
            html += "</script>" + Environment.NewLine;
            html += "</body>" + Environment.NewLine;
            html += "</html>" + Environment.NewLine;

            File.WriteAllText(caminho, html);
        }

        // -------------------------------------------------------
        // pessoas.html - tabela com os dados das pessoas (item 10)
        // -------------------------------------------------------
        // Gera a tabela HTML com os dados das pessoas (item 10).
        public static void GerarDados(string caminho)
        {
            string html = "";

            html += "<!DOCTYPE html>" + Environment.NewLine;
            html += "<html lang=\"pt-BR\">" + Environment.NewLine;
            html += "<head>" + Environment.NewLine;
            html += "<meta charset=\"UTF-8\">" + Environment.NewLine;
            html += "<title>Avaliacoes de IMC</title>" + Environment.NewLine;
            html += "</head>" + Environment.NewLine;
            html += "<body>" + Environment.NewLine;
            html += "<h1>Consultorio de Fisioterapia - Avaliacao de IMC</h1>" + Environment.NewLine;
            html += "<table border=\"1\">" + Environment.NewLine;
            html += "<tr><th>Id</th><th>Nome</th><th>Peso (Kg)</th><th>Altura (m)</th><th>IMC</th><th>Classificacao</th></tr>" + Environment.NewLine;

            for (int i = 0; i < Quantidade; i++)
            {
                html += "<tr><td>" + Ids[i] + "</td><td>" +
                        Escapar(Nomes[i]) + "</td><td>" +
                        Pesos[i].ToString("0.##") + "</td><td>" +
                        Alturas[i].ToString("0.##") + "</td><td>" +
                        Imcs[i].ToString("0.00") + "</td><td>" +
                        Classes[i] + "</td></tr>" + Environment.NewLine;
            }

            html += "</table>" + Environment.NewLine;
            html += "<p>Total de pessoas avaliadas: " + Quantidade + "</p>" + Environment.NewLine;
            html += "<p><a href=\"grafico.html\">Ver o grafico de classificacoes</a></p>" + Environment.NewLine;
            html += "</body>" + Environment.NewLine;
            html += "</html>" + Environment.NewLine;

            File.WriteAllText(caminho, html);
        }

        // Somente o nome do arquivo, para mostrar no rodape.
        static string Nome(string caminho)
        {
            return caminho.Substring(caminho.LastIndexOf('\\') + 1);
        }

        // Nome com < ou & quebraria o HTML.
        static string Escapar(string texto)
        {
            if (texto == null) return "";

            return texto.Replace("&", "&amp;").Replace("<", "&lt;").Replace(">", "&gt;");
        }
    }
}
