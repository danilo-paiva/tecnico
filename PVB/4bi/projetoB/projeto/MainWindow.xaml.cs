using System;
using System.IO;
using System.Windows;
using System.Windows.Controls;

namespace projetoB
{
    public partial class App : Application
    {
    }

    /***************************************************************
    * PROJETO DO 4o BIMESTRE - IMC EM CONSULTORIO DE FISIOTERAPIA
    * Implementacao B - um vetor de blocos JSON (um bloco por pessoa).
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
    * Cada pessoa e guardada como o texto de um objeto JSON (as linhas
    * entre "{" e "}"), dentro do vetor blocos.
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
    * Seccoes: dados (blocos), tela, relatorio/IMC, leitura do JSON,
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
        // Dados: um bloco JSON por pessoa
        // -------------------------------------------------------
        // Cada posicao guarda o texto de um objeto JSON (as linhas
        // entre "{" e "}"). O vetor cresce/diminui nas operacoes.
        public static string[] blocos = new string[0];

        // Ao abrir a janela: le o JSON e monta a lista/relatorio.
        public MainWindow()
        {
            InitializeComponent();

            blocos = Ler(arquivo);
            AtualizarTela();
        }

        // -------------------------------------------------------
        // Tela
        // -------------------------------------------------------
        // Limpa a lista, escreve uma linha por pessoa (item 4: o peso
        // aparece na caixa de lista), atualiza o relatorio e o rodape.
        void AtualizarTela()
        {
            lstPacientes.Items.Clear();

            for (int i = 0; i < blocos.Length; i++)
            {
                string bloco = blocos[i];

                lstPacientes.Items.Add("Id " + Id(bloco) +
                    " - " + Campo(bloco, "Nome") +
                    " - " + Numero(Campo(bloco, "Peso (Kg)")).ToString("0.##") + " kg" +
                    " - IMC " + Numero(Campo(bloco, "IMC")).ToString("0.00") +
                    " - " + Campo(bloco, "Classificação"));
            }

            txtRelatorio.Text = Relatorio();
            lblStatus.Content = blocos.Length + " pessoa(s) em " + Nome(arquivo);
        }

        // Joga os campos do bloco da posicao nos campos da tela.
        void Mostrar(int posicao)
        {
            string bloco = blocos[posicao];

            txtId.Text = Id(bloco).ToString();
            txtNome.Text = Campo(bloco, "Nome");
            txtPeso.Text = Numero(Campo(bloco, "Peso (Kg)")).ToString("0.##");
            txtAltura.Text = Numero(Campo(bloco, "Altura (m)")).ToString("0.##");
        }


        // -------------------------------------------------------
        // Operacoes (itens 5 e 6 do enunciado)
        // -------------------------------------------------------
        // Todos os botoes leem o Id digitado; consultar/alterar/excluir
        // usam Buscar() e param quando o Id nao existe.
        // Cadastrar: le os tres campos, cria um bloco novo e regrava o JSON.
        private void btCadastrar_Click(object sender, RoutedEventArgs e)
        {
            string nome = txtNome.Text.Trim();
            double peso = Numero(txtPeso.Text);
            double altura = Numero(txtAltura.Text);

            blocos = Adicionar(blocos, nome, peso, altura);
            Gravar(arquivo, blocos);

            AtualizarTela();
            lstPacientes.SelectedIndex = blocos.Length - 1;

            lblStatus.Content = "Id " + Id(blocos[blocos.Length - 1]) + " cadastrado.";
        }

        // Consultar: busca pelo Id digitado e mostra os campos.
        private void btConsultar_Click(object sender, RoutedEventArgs e)
        {
            int id = int.Parse(txtId.Text);

            int posicao = Buscar(blocos, id);

            if (posicao < 0)
            {
                lblStatus.Content = "Nenhum registro com Id " + id + ".";
                return;
            }

            Mostrar(posicao);
            lstPacientes.SelectedIndex = posicao;
            lblStatus.Content = "Id " + id + " encontrado.";
        }

        // Alterar: busca pelo Id, refaz o bloco e regrava o JSON.
        private void btAlterar_Click(object sender, RoutedEventArgs e)
        {
            int id = int.Parse(txtId.Text);

            int posicao = Buscar(blocos, id);

            if (posicao < 0)
            {
                lblStatus.Content = "Nenhum registro com Id " + id + ".";
                return;
            }

            string nome = txtNome.Text.Trim();
            double peso = Numero(txtPeso.Text);
            double altura = Numero(txtAltura.Text);

            blocos = Alterar(blocos, posicao, nome, peso, altura);
            Gravar(arquivo, blocos);

            AtualizarTela();
            lstPacientes.SelectedIndex = posicao;

            lblStatus.Content = "Id " + id + " alterado.";
        }

        // Excluir: busca pelo Id, remove o bloco e regrava o JSON.
        private void btExcluir_Click(object sender, RoutedEventArgs e)
        {
            int id = int.Parse(txtId.Text);

            int posicao = Buscar(blocos, id);

            if (posicao < 0)
            {
                lblStatus.Content = "Nenhum registro com Id " + id + ".";
                return;
            }

            blocos = Excluir(blocos, posicao);
            Gravar(arquivo, blocos);

            AtualizarTela();
            lstPacientes.SelectedIndex = -1;
            txtId.Text = "";
            txtNome.Text = "";
            txtPeso.Text = "";
            txtAltura.Text = "";

            lblStatus.Content = "Id " + id + " excluido.";
        }

        // Clicar em uma linha da lista tambem preenche os campos.
        private void lstPacientes_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            int posicao = lstPacientes.SelectedIndex;

            if (posicao < 0 || posicao >= blocos.Length) return;

            Mostrar(posicao);
        }

        // -------------------------------------------------------
        // Relatorio e HTML (itens 7, 9, 10 e 11)
        // -------------------------------------------------------
        // Monta o relatorio do item 7 (todos os pacientes + IMC + classe).
        string Relatorio()
        {
            string texto = "RELATORIO DE AVALIACOES - CONSULTORIO DE FISIOTERAPIA" + Environment.NewLine;
            texto += "======================================================" + Environment.NewLine;

            for (int i = 0; i < blocos.Length; i++)
            {
                string bloco = blocos[i];

                texto += "Id: " + Id(bloco) + Environment.NewLine;
                texto += "Nome: " + Campo(bloco, "Nome") + Environment.NewLine;
                texto += "Peso: " + Numero(Campo(bloco, "Peso (Kg)")).ToString("0.##") + " kg" + Environment.NewLine;
                texto += "Altura: " + Numero(Campo(bloco, "Altura (m)")).ToString("0.##") + " m" + Environment.NewLine;
                texto += "IMC: " + Numero(Campo(bloco, "IMC")).ToString("0.00") + Environment.NewLine;
                texto += "Classificacao: " + Campo(bloco, "Classificação") + Environment.NewLine;
                texto += "------------------------------------------------------" + Environment.NewLine;
            }

            int[] contagem = ContarFaixas(blocos);

            texto += "Total de pessoas: " + blocos.Length + Environment.NewLine;

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
            GerarGrafico(grafico, blocos);
            lblStatus.Content = "Arquivo " + Nome(grafico) + " gerado.";
        }

        private void btDados_Click(object sender, RoutedEventArgs e)
        {
            GerarDados(dados, blocos);
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
        // Leitura do JSON: cada posicao do vetor e um objeto
        // (aula 08: Trim, StartsWith, EndsWith, IndexOf, Substring)
        // -------------------------------------------------------
        // Le o arquivo e devolve um vetor de blocos: cada posicao e o
        // texto de um objeto JSON. As linhas entre "{" e "}" sao
        // copiadas na ordem; no fim o vetor e ajustado ao tamanho real.
        public static string[] Ler(string caminho)
        {
            if (!File.Exists(caminho)) return new string[0];

            string[] linhas = File.ReadAllLines(caminho);
            string[] novos = new string[linhas.Length];   // sobra espaco, ajusta no fim
            int n = 0;
            int inicio = -1;

            for (int i = 0; i < linhas.Length; i++)
            {
                string linha = linhas[i].Trim();

                if (linha.StartsWith("{"))
                {
                    inicio = i;
                }
                else if (linha.StartsWith("}") && inicio >= 0)
                {
                    string bloco = "";

                    for (int j = inicio + 1; j < i; j++)
                        bloco += linhas[j].Trim() + Environment.NewLine;

                    novos[n] = bloco;
                    n++;
                    inicio = -1;
                }
            }

            // Ajusta o vetor ao tamanho real (sem List<>).
            string[] resultado = new string[n];

            for (int i = 0; i < n; i++)
                resultado[i] = novos[i];

            return resultado;
        }

        // Valor de um campo do bloco. Usa IndexOf com char no fim da
        // linha: em .NET 5 o IndexOf("\n") com string nao encontra o
        // \n que vem depois do \r (comparacao sensivel a cultura).
        public static string Campo(string bloco, string chave)
        {
            int p = bloco.IndexOf("\"" + chave + "\"");
            if (p < 0) return "";

            int dp = bloco.IndexOf(':', p);
            int fim = bloco.IndexOf('\n', dp);
            if (fim < 0) fim = bloco.Length;

            string valor = bloco.Substring(dp + 1, fim - dp - 1).Trim();

            if (valor.EndsWith(","))
                valor = valor.Substring(0, valor.Length - 1);

            valor = valor.Trim();

            if (valor.StartsWith("\"") && valor.EndsWith("\""))
                valor = valor.Substring(1, valor.Length - 2);

            return valor;
        }

        // O Id vem do bloco; se a chave nao existir, devolve 0.
        public static int Id(string bloco)
        {
            string texto = Campo(bloco, "Id");

            if (texto == "") return 0;

            return int.Parse(texto);
        }

        // O JSON usa ponto decimal: troca por virgula antes do Parse.
        // Campo vazio devolve 0 para nao quebrar a leitura.
        public static double Numero(string texto)
        {
            if (texto == "") return 0;

            return double.Parse(texto.Trim().Replace(".", ","));
        }

        // -------------------------------------------------------
        // Gravacao do JSON
        // -------------------------------------------------------
        // Monta o texto de um objeto JSON com os seis campos do template.
        public static string Montar(int id, string nome, double peso, double altura, double imc, string classe)
        {
            string bloco = "";

            bloco += "        \"Id\": " + id + "," + Environment.NewLine;
            bloco += "        \"Nome\": \"" + Limpar(nome) + "\"," + Environment.NewLine;
            bloco += "        \"Peso (Kg)\": " + ComPonto(peso, "0.##") + "," + Environment.NewLine;
            bloco += "        \"Altura (m)\": " + ComPonto(altura, "0.##") + "," + Environment.NewLine;
            bloco += "        \"IMC\": " + ComPonto(imc, "0.00") + "," + Environment.NewLine;
            bloco += "        \"Classificação\": \"" + Limpar(classe) + "\"" + Environment.NewLine;

            return bloco;
        }

        // Junta os blocos no template: "[", objetos separados por "," e "]".
        // O ultimo objeto nao leva virgula.
        public static void Gravar(string caminho, string[] lista)
        {
            string texto = "[" + Environment.NewLine;

            for (int i = 0; i < lista.Length; i++)
            {
                texto += "    {" + Environment.NewLine;
                texto += lista[i];

                if (i < lista.Length - 1)
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
        // Operacoes no vetor de blocos
        // -------------------------------------------------------
        // Devolve a posicao do Id no vetor de blocos, ou -1.
        public static int Buscar(string[] lista, int id)
        {
            for (int i = 0; i < lista.Length; i++)
                if (Id(lista[i]) == id) return i;

            return -1;
        }

        // Maior Id existente + 1.
        public static int ProximoId(string[] lista)
        {
            int maior = 0;

            for (int i = 0; i < lista.Length; i++)
            {
                int id = Id(lista[i]);

                if (id > maior) maior = id;
            }

            return maior + 1;
        }

        // Cria um vetor com uma posicao a mais, copia os antigos e monta o novo.
        public static string[] Adicionar(string[] lista, string nome, double peso, double altura)
        {
            string[] novo = new string[lista.Length + 1];

            for (int i = 0; i < lista.Length; i++)
                novo[i] = lista[i];

            double imc = CalcularImc(peso, altura);

            novo[lista.Length] = Montar(ProximoId(lista), nome, peso, altura, imc, Classificar(imc));

            return novo;
        }

        // Substitui o bloco da posicao mantendo o Id e recalculando o IMC.
        public static string[] Alterar(string[] lista, int posicao, string nome, double peso, double altura)
        {
            double imc = CalcularImc(peso, altura);

            lista[posicao] = Montar(Id(lista[posicao]), nome, peso, altura, imc, Classificar(imc));

            return lista;
        }

        // Cria um vetor uma posicao menor, copiando todos menos a posicao.
        public static string[] Excluir(string[] lista, int posicao)
        {
            string[] novo = new string[lista.Length - 1];
            int k = 0;

            for (int i = 0; i < lista.Length; i++)
            {
                if (i == posicao) continue;

                novo[k] = lista[i];
                k++;
            }

            return novo;
        }

        // Quantidade de pessoas em cada faixa (item 9 do enunciado).
        public static int[] ContarFaixas(string[] lista)
        {
            int[] contagem = new int[6];

            for (int i = 0; i < lista.Length; i++)
            {
                double imc = Numero(Campo(lista[i], "IMC"));

                contagem[Faixa(imc)]++;
            }

            return contagem;
        }

        // -------------------------------------------------------
        // grafico.html - template do enunciado com o vetor r:
        // montado pelas faixas de IMC do arquivo (item 9)
        // -------------------------------------------------------
        // GerarGrafico: o vetor "r" e a contagem de pessoas em cada faixa
        // de IMC; "theta" sao os rotulos fixos do template. O eixo radial
        // vai ate o maior valor + 2 (o template mostra 50) para o grafico
        // ficar legivel mesmo com poucos registros.
        public static void GerarGrafico(string caminho, string[] lista)
        {
            int[] contagem = ContarFaixas(lista);

            int maior = 0;
            for (int f = 0; f < 6; f++)
                if (contagem[f] > maior) maior = contagem[f];

            int limite = maior + 2;
            if (limite < 4) limite = 4;

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
            html += "radialaxis: { visible: true, range: [0, " + limite + "]" + Environment.NewLine;
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
        public static void GerarDados(string caminho, string[] lista)
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

            for (int i = 0; i < lista.Length; i++)
            {
                string bloco = lista[i];

                html += "<tr><td>" + Id(bloco) + "</td><td>" +
                        Escapar(Campo(bloco, "Nome")) + "</td><td>" +
                        Numero(Campo(bloco, "Peso (Kg)")).ToString("0.##") + "</td><td>" +
                        Numero(Campo(bloco, "Altura (m)")).ToString("0.##") + "</td><td>" +
                        Numero(Campo(bloco, "IMC")).ToString("0.00") + "</td><td>" +
                        Campo(bloco, "Classificação") + "</td></tr>" + Environment.NewLine;
            }

            html += "</table>" + Environment.NewLine;
            html += "<p>Total de pessoas avaliadas: " + lista.Length + "</p>" + Environment.NewLine;
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
