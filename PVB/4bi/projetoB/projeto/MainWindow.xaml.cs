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
    * Implementacao B - um vetor de registros JSON (um registro por pessoa).
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
    * entre "{" e "}"), dentro do vetor registros.
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
    * Seccoes: dados (registros), tela, relatorio/IMC, leitura do JSON,
    * gravacao do JSON, operacoes (CRUD) e geracao dos HTML.
    ***************************************************************/
    public partial class MainWindow : Window
    {
        // Nomes relativos: os arquivos ficam na pasta de trabalho do
        // programa (a pasta do projeto no F5; a do .exe na publicacao).
        string arquivoBase = "pessoas.json";
        string arquivoGrafico = "grafico.html";
        string arquivoTabela = "pessoas.html";

        // -------------------------------------------------------
        // Dados: um registro JSON por pessoa
        // -------------------------------------------------------
        // Cada posicao guarda o texto de um objeto JSON (as linhas
        // entre "{" e "}"). O vetor cresce/diminui nas operacoes.
        public static string[] registros = new string[0];

        // Ao abrir a janela: le o JSON e monta a lista/relatorio.
        public MainWindow()
        {
            InitializeComponent();

            registros = LerRegistros(arquivoBase);
            AtualizarInterface();
        }

        // -------------------------------------------------------
        // Tela
        // -------------------------------------------------------
        // Monta as linhas da lista (item 4: o peso aparece na caixa),
        // atualiza o relatorio e o rodape.
        void AtualizarInterface()
        {
            string[] linhas = new string[registros.Length];

            for (int i = 0; i < registros.Length; i++)
            {
                string registro = registros[i];

                linhas[i] = "Id " + ObterId(registro) +
                    " - " + ObterCampo(registro, "Nome") +
                    " - " + ConverterNumero(ObterCampo(registro, "Peso (Kg)")).ToString("0.##") + " kg" +
                    " - IMC " + ConverterNumero(ObterCampo(registro, "IMC")).ToString("0.00") +
                    " - " + ObterCampo(registro, "Classificação");
            }

            lstAvaliados.ItemsSource = linhas;

            txtResumo.Text = MontarRelatorio();
            lblMensagem.Content = registros.Length + " pessoa(s) em " + NomeArquivo(arquivoBase);
        }

        // Joga os campos do registro da posicao nos campos da tela.
        void PreencherFormulario(int posicao)
        {
            string registro = registros[posicao];

            txtIdentificador.Text = ObterId(registro).ToString();
            txtPaciente.Text = ObterCampo(registro, "Nome");
            txtQuilos.Text = ConverterNumero(ObterCampo(registro, "Peso (Kg)")).ToString("0.##");
            txtEstatura.Text = ConverterNumero(ObterCampo(registro, "Altura (m)")).ToString("0.##");
        }


        // -------------------------------------------------------
        // Operacoes (itens 5 e 6 do enunciado)
        // -------------------------------------------------------
        // Todos os botoes leem o Id digitado; consultar/alterar/excluir
        // usam LocalizarId() e param quando o Id nao existe.
        // Cadastrar: le os tres campos, cria um registro novo e regrava o JSON.
        private void Cadastrar_Click(object sender, RoutedEventArgs e)
        {
            string nome = txtPaciente.Text.Trim();
            double peso = ConverterNumero(txtQuilos.Text);
            double altura = ConverterNumero(txtEstatura.Text);

            registros = IncluirRegistro(registros, nome, peso, altura);
            GravarRegistros(arquivoBase, registros);

            AtualizarInterface();
            lstAvaliados.SelectedIndex = registros.Length - 1;

            lblMensagem.Content = "Id " + ObterId(registros[registros.Length - 1]) + " cadastrado.";
        }

        // Consultar: busca pelo Id digitado e mostra os campos.
        private void Consultar_Click(object sender, RoutedEventArgs e)
        {
            int id = int.Parse(txtIdentificador.Text);

            int posicao = LocalizarId(registros, id);

            if (posicao < 0)
            {
                lblMensagem.Content = "Nenhum registro com Id " + id + ".";
                return;
            }

            PreencherFormulario(posicao);
            lstAvaliados.SelectedIndex = posicao;
            lblMensagem.Content = "Id " + id + " encontrado.";
        }

        // Alterar: busca pelo Id, refaz o registro e regrava o JSON.
        private void Alterar_Click(object sender, RoutedEventArgs e)
        {
            int id = int.Parse(txtIdentificador.Text);

            int posicao = LocalizarId(registros, id);

            if (posicao < 0)
            {
                lblMensagem.Content = "Nenhum registro com Id " + id + ".";
                return;
            }

            string nome = txtPaciente.Text.Trim();
            double peso = ConverterNumero(txtQuilos.Text);
            double altura = ConverterNumero(txtEstatura.Text);

            registros = AtualizarRegistro(registros, posicao, nome, peso, altura);
            GravarRegistros(arquivoBase, registros);

            AtualizarInterface();
            lstAvaliados.SelectedIndex = posicao;

            lblMensagem.Content = "Id " + id + " alterado.";
        }

        // Excluir: busca pelo Id, remove o registro e regrava o JSON.
        private void Excluir_Click(object sender, RoutedEventArgs e)
        {
            int id = int.Parse(txtIdentificador.Text);

            int posicao = LocalizarId(registros, id);

            if (posicao < 0)
            {
                lblMensagem.Content = "Nenhum registro com Id " + id + ".";
                return;
            }

            registros = RemoverRegistro(registros, posicao);
            GravarRegistros(arquivoBase, registros);

            AtualizarInterface();
            lstAvaliados.SelectedIndex = -1;
            txtIdentificador.Text = "";
            txtPaciente.Text = "";
            txtQuilos.Text = "";
            txtEstatura.Text = "";

            lblMensagem.Content = "Id " + id + " excluido.";
        }

        // Clicar em uma linha da lista tambem preenche os campos.
        private void lstAvaliados_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            int posicao = lstAvaliados.SelectedIndex;

            if (posicao < 0 || posicao >= registros.Length) return;

            PreencherFormulario(posicao);
        }

        // -------------------------------------------------------
        // Relatorio e HTML (itens 7, 9, 10 e 11)
        // -------------------------------------------------------
        // Monta o relatorio do item 7 (todos os pacientes + IMC + classe).
        string MontarRelatorio()
        {
            string texto = "RELATORIO DE AVALIACOES - CONSULTORIO DE FISIOTERAPIA" + Environment.NewLine;
            texto += "======================================================" + Environment.NewLine;

            for (int i = 0; i < registros.Length; i++)
            {
                string registro = registros[i];

                texto += "Id: " + ObterId(registro) + Environment.NewLine;
                texto += "Nome: " + ObterCampo(registro, "Nome") + Environment.NewLine;
                texto += "Peso: " + ConverterNumero(ObterCampo(registro, "Peso (Kg)")).ToString("0.##") + " kg" + Environment.NewLine;
                texto += "Altura: " + ConverterNumero(ObterCampo(registro, "Altura (m)")).ToString("0.##") + " m" + Environment.NewLine;
                texto += "IMC: " + ConverterNumero(ObterCampo(registro, "IMC")).ToString("0.00") + Environment.NewLine;
                texto += "Classificacao: " + ObterCampo(registro, "Classificação") + Environment.NewLine;
                texto += "------------------------------------------------------" + Environment.NewLine;
            }

            int[] contagem = ContarPorFaixa(registros);

            texto += "Total de pessoas: " + registros.Length + Environment.NewLine;

            for (int f = 0; f < 6; f++)
                texto += DescreverFaixa(f) + ": " + contagem[f] + Environment.NewLine;

            return texto;
        }

        private void Relatorio_Click(object sender, RoutedEventArgs e)
        {
            txtResumo.Text = MontarRelatorio();
            lblMensagem.Content = "Relatorio atualizado.";
        }

        private void Grafico_Click(object sender, RoutedEventArgs e)
        {
            GerarHtmlGrafico(arquivoGrafico, registros);
            lblMensagem.Content = "Arquivo " + NomeArquivo(arquivoGrafico) + " gerado.";
        }

        private void Dados_Click(object sender, RoutedEventArgs e)
        {
            GerarHtmlDados(arquivoTabela, registros);
            lblMensagem.Content = "Arquivo " + NomeArquivo(arquivoTabela) + " gerado.";
        }

        // -------------------------------------------------------
        // IMC (item 8): aula 05 (Math.Pow / Math.Round) e aula 08
        // -------------------------------------------------------
        // IMC = peso / altura^2, arredondado em duas casas (item 8).
        public static double CalcularIndice(double peso, double altura)
        {
            return Math.Round(peso / Math.Pow(altura, 2), 2);
        }

        // 0 <18,5 | 1 18,5-24,9 | 2 25-29,9 | 3 30-34,9 | 4 35-39,9 | 5 >=40
        public static int ObterFaixa(double imc)
        {
            if (imc < 18.5) return 0;
            if (imc < 25.0) return 1;
            if (imc < 30.0) return 2;
            if (imc < 35.0) return 3;
            if (imc < 40.0) return 4;
            return 5;
        }

        // Classificacao por extenso a partir da mesma faixa do grafico.
        public static string ClassificarImc(double imc)
        {
            return DescreverFaixa(ObterFaixa(imc));
        }

        // Nomes usados no relatorio, na ordem 0..5.
        public static string DescreverFaixa(int faixa)
        {
            string[] nomes = new string[] { "Magreza", "Peso Normal", "Sobrepeso",
                "Obesidade Grau I", "Obesidade Grau II", "Obesidade Grau III" };

            return nomes[faixa];
        }

        // -------------------------------------------------------
        // Leitura do JSON: cada posicao do vetor e um objeto
        // (aula 08: Trim, StartsWith, EndsWith, IndexOf, Substring)
        // -------------------------------------------------------
        // Le o arquivo e devolve um vetor de registros: cada posicao e o
        // texto de um objeto JSON. As linhas entre "{" e "}" sao
        // copiadas na ordem; no fim o vetor e ajustado ao tamanho real.
        public static string[] LerRegistros(string caminho)
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
                    string registro = "";

                    for (int j = inicio + 1; j < i; j++)
                        registro += linhas[j].Trim() + Environment.NewLine;

                    novos[n] = registro;
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

        // Valor de um campo do registro. Usa IndexOf com char no fim da
        // linha: em .NET 5 o IndexOf("\n") com string nao encontra o
        // \n que vem depois do \r (comparacao sensivel a cultura).
        public static string ObterCampo(string registro, string chave)
        {
            int p = registro.IndexOf("\"" + chave + "\"");
            if (p < 0) return "";

            int dp = registro.IndexOf(':', p);
            int fim = registro.IndexOf('\n', dp);
            if (fim < 0) fim = registro.Length;

            string valor = registro.Substring(dp + 1, fim - dp - 1).Trim();

            if (valor.EndsWith(","))
                valor = valor.Substring(0, valor.Length - 1);

            valor = valor.Trim();

            if (valor.StartsWith("\"") && valor.EndsWith("\""))
                valor = valor.Substring(1, valor.Length - 2);

            return valor;
        }

        // O Id vem do registro; se a chave nao existir, devolve 0.
        public static int ObterId(string registro)
        {
            string texto = ObterCampo(registro, "Id");

            if (texto == "") return 0;

            return int.Parse(texto);
        }

        // O JSON usa ponto decimal: troca por virgula antes do Parse.
        // Campo vazio devolve 0 para nao quebrar a leitura.
        public static double ConverterNumero(string texto)
        {
            if (texto == "") return 0;

            return double.Parse(texto.Trim().Replace(".", ","));
        }

        // -------------------------------------------------------
        // Gravacao do JSON
        // -------------------------------------------------------
        // Monta o texto de um objeto JSON com os seis campos do template.
        public static string MontarRegistro(int id, string nome, double peso, double altura, double imc, string classe)
        {
            string registro = "";

            registro += "        \"Id\": " + id + "," + Environment.NewLine;
            registro += "        \"Nome\": \"" + LimparTexto(nome) + "\"," + Environment.NewLine;
            registro += "        \"Peso (Kg)\": " + NumeroComPonto(peso, "0.##") + "," + Environment.NewLine;
            registro += "        \"Altura (m)\": " + NumeroComPonto(altura, "0.##") + "," + Environment.NewLine;
            registro += "        \"IMC\": " + NumeroComPonto(imc, "0.00") + "," + Environment.NewLine;
            registro += "        \"Classificação\": \"" + LimparTexto(classe) + "\"" + Environment.NewLine;

            return registro;
        }

        // Junta os registros no template: "[", objetos separados por "," e "]".
        // O ultimo objeto nao leva virgula.
        public static void GravarRegistros(string caminho, string[] lista)
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
        static string NumeroComPonto(double valor, string formato)
        {
            return valor.ToString(formato).Replace(",", ".");
        }

        // Aspas dentro do nome quebrariam o JSON.
        static string LimparTexto(string texto)
        {
            if (texto == null) return "";

            return texto.Replace("\"", "'");
        }

        // -------------------------------------------------------
        // Operacoes no vetor de registros
        // -------------------------------------------------------
        // Devolve a posicao do Id no vetor de registros, ou -1.
        public static int LocalizarId(string[] lista, int id)
        {
            for (int i = 0; i < lista.Length; i++)
                if (ObterId(lista[i]) == id) return i;

            return -1;
        }

        // Maior Id existente + 1.
        public static int ProximoIdentificador(string[] lista)
        {
            int maior = 0;

            for (int i = 0; i < lista.Length; i++)
            {
                int id = ObterId(lista[i]);

                if (id > maior) maior = id;
            }

            return maior + 1;
        }

        // Cria um vetor com uma posicao a mais, copia os antigos e monta o novo.
        public static string[] IncluirRegistro(string[] lista, string nome, double peso, double altura)
        {
            string[] novo = new string[lista.Length + 1];

            for (int i = 0; i < lista.Length; i++)
                novo[i] = lista[i];

            double imc = CalcularIndice(peso, altura);

            novo[lista.Length] = MontarRegistro(ProximoIdentificador(lista), nome, peso, altura, imc, ClassificarImc(imc));

            return novo;
        }

        // Substitui o registro da posicao mantendo o Id e recalculando o IMC.
        public static string[] AtualizarRegistro(string[] lista, int posicao, string nome, double peso, double altura)
        {
            double imc = CalcularIndice(peso, altura);

            lista[posicao] = MontarRegistro(ObterId(lista[posicao]), nome, peso, altura, imc, ClassificarImc(imc));

            return lista;
        }

        // Cria um vetor uma posicao menor, copiando todos menos a posicao.
        public static string[] RemoverRegistro(string[] lista, int posicao)
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
        public static int[] ContarPorFaixa(string[] lista)
        {
            int[] contagem = new int[6];

            for (int i = 0; i < lista.Length; i++)
            {
                double imc = ConverterNumero(ObterCampo(lista[i], "IMC"));

                contagem[ObterFaixa(imc)]++;
            }

            return contagem;
        }

        // -------------------------------------------------------
        // grafico.html - template do enunciado com o vetor r:
        // montado pelas faixas de IMC do arquivo (item 9)
        // -------------------------------------------------------
        // GerarGrafico: o vetor "r" e a contagem de pessoas em cada faixa
        // de IMC; "theta" sao os rotulos fixos do template; o range
        // [0, 50] tambem vem do template do enunciado.
        public static void GerarHtmlGrafico(string caminho, string[] lista)
        {
            int[] contagem = ContarPorFaixa(lista);

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
        public static void GerarHtmlDados(string caminho, string[] lista)
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
                string registro = lista[i];

                html += "<tr><td>" + ObterId(registro) + "</td><td>" +
                        EscaparHtml(ObterCampo(registro, "Nome")) + "</td><td>" +
                        ConverterNumero(ObterCampo(registro, "Peso (Kg)")).ToString("0.##") + "</td><td>" +
                        ConverterNumero(ObterCampo(registro, "Altura (m)")).ToString("0.##") + "</td><td>" +
                        ConverterNumero(ObterCampo(registro, "IMC")).ToString("0.00") + "</td><td>" +
                        ObterCampo(registro, "Classificação") + "</td></tr>" + Environment.NewLine;
            }

            html += "</table>" + Environment.NewLine;
            html += "<p>Total de pessoas avaliadas: " + lista.Length + "</p>" + Environment.NewLine;
            html += "<p><a href=\"grafico.html\">Ver o grafico de classificacoes</a></p>" + Environment.NewLine;
            html += "</body>" + Environment.NewLine;
            html += "</html>" + Environment.NewLine;

            File.WriteAllText(caminho, html);
        }

        // Somente o nome do arquivo, para mostrar no rodape.
        static string NomeArquivo(string caminho)
        {
            return caminho.Substring(caminho.LastIndexOf('\\') + 1);
        }

        // Nome com < ou & quebraria o HTML.
        static string EscaparHtml(string texto)
        {
            if (texto == null) return "";

            return texto.Replace("&", "&amp;").Replace("<", "&lt;").Replace(">", "&gt;");
        }
    }
}
