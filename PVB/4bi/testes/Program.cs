using System;
using System.IO;
using A = projetoA;
using B = projetoB;

namespace testes
{
    /***************************************************************
    * Testes das duas implementacoes (A = vetores, B = blocos).
    * Roda sem Visual Studio:  dotnet run --project testes
    ***************************************************************/
    class Program
    {
        static int total = 0;
        static int falhas = 0;

        const string TEMPLATE = @"[
    {
        ""Id"": 1,
        ""Nome"": ""João Silva"",
        ""Peso (Kg)"": 70,
        ""Altura (m)"": 1.75,
        ""IMC"": 22.86,
        ""Classificação"": ""Peso Normal""
    },
    {
        ""Id"": 2,
        ""Nome"": ""Maria Santos"",
        ""Peso (Kg)"": 85,
        ""Altura (m)"": 1.68,
        ""IMC"": 30.12,
        ""Classificação"": ""Obesidade Grau I""
    },
    {
        ""Id"": 3,
        ""Nome"": ""Carlos Pereira"",
        ""Peso (Kg)"": 60,
        ""Altura (m)"": 1.6,
        ""IMC"": 23.44,
        ""Classificação"": ""Peso Normal""
    }
]";

        [STAThread]   // as classes usadas sao de WPF (Window)
        static void Main()
        {
            string pasta = Path.Combine(Path.GetTempPath(), "pvb-4bi-testes");

            if (Directory.Exists(pasta)) Directory.Delete(pasta, true);
            Directory.CreateDirectory(pasta);

            string template = Path.Combine(pasta, "template.json");
            File.WriteAllText(template, TEMPLATE);

            Secao("1. Formula do IMC");
            Perto("A: 70 kg / 1,75 m", 22.86, A.MainWindow.CalcularImc(70, 1.75));
            Perto("A: 85 kg / 1,68 m", 30.12, A.MainWindow.CalcularImc(85, 1.68));
            Perto("B: 60 kg / 1,60 m", 23.44, B.MainWindow.CalcularImc(60, 1.6));

            Secao("2. Limites da classificacao");
            Igual("A: 18,49 -> Magreza", "Magreza", A.MainWindow.Classificar(18.49));
            Igual("A: 18,50 -> Peso Normal", "Peso Normal", A.MainWindow.Classificar(18.50));
            Igual("A: 24,99 -> Peso Normal", "Peso Normal", A.MainWindow.Classificar(24.99));
            Igual("A: 25,00 -> Sobrepeso", "Sobrepeso", A.MainWindow.Classificar(25.00));
            Igual("A: 29,99 -> Sobrepeso", "Sobrepeso", A.MainWindow.Classificar(29.99));
            Igual("A: 30,00 -> Obesidade I", "Obesidade Grau I", A.MainWindow.Classificar(30.00));
            Igual("A: 34,99 -> Obesidade I", "Obesidade Grau I", A.MainWindow.Classificar(34.99));
            Igual("A: 35,00 -> Obesidade II", "Obesidade Grau II", A.MainWindow.Classificar(35.00));
            Igual("A: 39,99 -> Obesidade II", "Obesidade Grau II", A.MainWindow.Classificar(39.99));
            Igual("A: 40,00 -> Obesidade III", "Obesidade Grau III", A.MainWindow.Classificar(40.00));

            Igual("B: 18,49 -> Magreza", "Magreza", B.MainWindow.Classificar(18.49));
            Igual("B: 25,00 -> Sobrepeso", "Sobrepeso", B.MainWindow.Classificar(25.00));
            Igual("B: 40,00 -> Obesidade III", "Obesidade Grau III", B.MainWindow.Classificar(40.00));
            Igual("B: faixa 25,00 = 2", 2, B.MainWindow.Faixa(25.00));

            Secao("3. Conversao de numeros do JSON");
            Perto("A: Numero(1.75)", 1.75, A.MainWindow.Numero("1.75"));
            Perto("A: Numero(1.6)", 1.6, A.MainWindow.Numero("1.6"));
            Perto("A: Numero(70)", 70, A.MainWindow.Numero("70"));
            Perto("B: Numero(22.86)", 22.86, B.MainWindow.Numero("22.86"));

            Secao("4. Implementacao A - leitura do template");
            string arquivoA = Path.Combine(pasta, "a.json");
            File.Copy(template, arquivoA, true);

            A.MainWindow.Carregar(arquivoA);
            Igual("A: quantidade", 3, A.MainWindow.Quantidade);
            Igual("A: nome 1", "João Silva", A.MainWindow.Nomes[0]);
            Perto("A: altura 1", 1.75, A.MainWindow.Alturas[0]);
            Perto("A: IMC 2", 30.12, A.MainWindow.Imcs[1]);
            Igual("A: classificacao 2", "Obesidade Grau I", A.MainWindow.Classes[1]);
            Igual("A: proximo Id", 4, A.MainWindow.ProximoId());
            Igual("A: busca Id 2", 1, A.MainWindow.Buscar(2));
            Igual("A: busca Id 99", -1, A.MainWindow.Buscar(99));
            VetorIgual("A: faixas", new int[] { 0, 2, 0, 1, 0, 0 }, A.MainWindow.ContarFaixas());

            A.MainWindow.Salvar(arquivoA);
            string cruA = File.ReadAllText(arquivoA);
            Ok("A: altura com ponto decimal", cruA.Contains("\"Altura (m)\": 1.75"));
            Ok("A: IMC com ponto decimal", cruA.Contains("\"IMC\": 30.12"));
            Ok("A: sem virgula decimal", !cruA.Contains("22,86"));
            Ok("A: Id com virgula", cruA.Contains("\"Id\": 1,"));
            Ok("A: ultimo campo sem virgula", cruA.Contains("\"Classificação\": \"Obesidade Grau I\"" + Environment.NewLine));

            A.MainWindow.Carregar(arquivoA);
            Igual("A: recarrega 3", 3, A.MainWindow.Quantidade);

            Secao("5. Implementacao A - HTML");
            string graficoA = Path.Combine(pasta, "grafico-a.html");
            string dadosA = Path.Combine(pasta, "dados-a.html");

            A.MainWindow.GerarGrafico(graficoA);
            A.MainWindow.GerarDados(dadosA);

            Ok("A: grafico.html criado", File.Exists(graficoA));
            Ok("A: pessoas.html criado", File.Exists(dadosA));

            string htmlGraficoA = File.ReadAllText(graficoA);
            string htmlDadosA = File.ReadAllText(dadosA);

            Ok("A: vetor r calculado", htmlGraficoA.Contains("r: [0, 2, 0, 1, 0, 0],"));
            Ok("A: template Plotly", htmlGraficoA.Contains("scatterpolar") &&
                                     htmlGraficoA.Contains("Plotly.newPlot"));
            Ok("A: tabela com nome", htmlDadosA.Contains("João Silva"));
            Ok("A: classificacao por extenso", htmlDadosA.Contains("Obesidade Grau I"));
            Ok("A: dois HTML diferentes", htmlGraficoA != htmlDadosA);

            Secao("6. Implementacao A - cadastrar, alterar e excluir");
            int posicao = A.MainWindow.Adicionar("Ana, Ção", 80, 1.8);
            Igual("A: posicao nova", 3, posicao);
            Igual("A: Id novo", 4, A.MainWindow.Ids[3]);
            Perto("A: IMC novo", 24.69, A.MainWindow.Imcs[3]);
            Igual("A: classe nova", "Peso Normal", A.MainWindow.Classes[3]);
            A.MainWindow.Salvar(arquivoA);

            A.MainWindow.Carregar(arquivoA);
            Igual("A: recarrega 4", 4, A.MainWindow.Quantidade);
            Igual("A: nome com virgula", "Ana, Ção", A.MainWindow.Nomes[3]);
            Igual("A: proximo Id 5", 5, A.MainWindow.ProximoId());

            int p4 = A.MainWindow.Buscar(4);
            Igual("A: busca Id 4", 3, p4);

            A.MainWindow.Alterar(p4, "Ana Alterada", 100, 2.0);
            A.MainWindow.Salvar(arquivoA);
            A.MainWindow.Carregar(arquivoA);

            Igual("A: nome alterado", "Ana Alterada", A.MainWindow.Nomes[3]);
            Perto("A: IMC 25,00", 25.00, A.MainWindow.Imcs[3]);
            Igual("A: classe 25,00", "Sobrepeso", A.MainWindow.Classes[3]);

            A.MainWindow.Excluir(0);
            A.MainWindow.Salvar(arquivoA);
            A.MainWindow.Carregar(arquivoA);

            Igual("A: apos excluir", 3, A.MainWindow.Quantidade);
            Igual("A: Id 1 sumiu", -1, A.MainWindow.Buscar(1));
            Igual("A: primeiro Id virou 2", 2, A.MainWindow.Ids[0]);

            Secao("7. Implementacao B - leitura do template");
            string arquivoB = Path.Combine(pasta, "b.json");
            File.Copy(template, arquivoB, true);

            string[] blocos = B.MainWindow.Ler(arquivoB);
            Igual("B: quantidade de blocos", 3, blocos.Length);
            Igual("B: nome 1", "João Silva", B.MainWindow.Campo(blocos[0], "Nome"));
            Igual("B: Id 2", 2, B.MainWindow.Id(blocos[1]));
            Perto("B: peso 2", 85, B.MainWindow.Numero(B.MainWindow.Campo(blocos[1], "Peso (Kg)")));
            Perto("B: IMC 2", 30.12, B.MainWindow.Numero(B.MainWindow.Campo(blocos[1], "IMC")));
            Igual("B: classificacao 2", "Obesidade Grau I", B.MainWindow.Campo(blocos[1], "Classificação"));
            VetorIgual("B: faixas", new int[] { 0, 2, 0, 1, 0, 0 }, B.MainWindow.ContarFaixas(blocos));
            Igual("B: proximo Id", 4, B.MainWindow.ProximoId(blocos));
            Igual("B: busca Id 2", 1, B.MainWindow.Buscar(blocos, 2));
            Igual("B: busca Id 99", -1, B.MainWindow.Buscar(blocos, 99));

            Secao("8. Implementacao B - HTML");
            string graficoB = Path.Combine(pasta, "grafico-b.html");
            string dadosB = Path.Combine(pasta, "dados-b.html");

            B.MainWindow.GerarGrafico(graficoB, blocos);
            B.MainWindow.GerarDados(dadosB, blocos);

            Ok("B: grafico.html criado", File.Exists(graficoB));
            Ok("B: pessoas.html criado", File.Exists(dadosB));

            string htmlGraficoB = File.ReadAllText(graficoB);
            string htmlDadosB = File.ReadAllText(dadosB);

            Ok("B: vetor r calculado", htmlGraficoB.Contains("r: [0, 2, 0, 1, 0, 0],"));
            Ok("B: tabela com nome", htmlDadosB.Contains("Maria Santos"));
            Ok("B: dois HTML diferentes", htmlGraficoB != htmlDadosB);

            Secao("9. Implementacao B - cadastrar, alterar e excluir");
            blocos = B.MainWindow.Adicionar(blocos, "Teste B", 80, 1.8);
            Igual("B: adicionou", 4, blocos.Length);
            Igual("B: Id novo", 4, B.MainWindow.Id(blocos[3]));
            B.MainWindow.Gravar(arquivoB, blocos);

            blocos = B.MainWindow.Ler(arquivoB);
            Igual("B: recarrega 4", 4, blocos.Length);
            Igual("B: nome novo", "Teste B", B.MainWindow.Campo(blocos[3], "Nome"));

            blocos = B.MainWindow.Alterar(blocos, 0, "João B", 90, 2.0);
            B.MainWindow.Gravar(arquivoB, blocos);

            string cruB = File.ReadAllText(arquivoB);
            Ok("B: Id com virgula", cruB.Contains("\"Id\": 1,"));
            Ok("B: IMC com ponto decimal", cruB.Contains("\"IMC\": 22.50,"));
            Ok("B: sem virgula decimal", !cruB.Contains("22,50"));
            blocos = B.MainWindow.Ler(arquivoB);

            Igual("B: nome alterado", "João B", B.MainWindow.Campo(blocos[0], "Nome"));
            Perto("B: IMC 22,50", 22.50, B.MainWindow.Numero(B.MainWindow.Campo(blocos[0], "IMC")));
            Igual("B: Id preservado", 1, B.MainWindow.Id(blocos[0]));

            blocos = B.MainWindow.Excluir(blocos, 2);
            B.MainWindow.Gravar(arquivoB, blocos);
            blocos = B.MainWindow.Ler(arquivoB);

            Igual("B: apos excluir", 3, blocos.Length);
            Igual("B: Id 3 sumiu", -1, B.MainWindow.Buscar(blocos, 3));

            Secao("10. Compatibilidade entre as duas implementacoes");
            string[] lidosPelaB = B.MainWindow.Ler(arquivoA);
            Igual("B le o arquivo da A", 3, lidosPelaB.Length);
            Igual("B le primeiro Id", 2, B.MainWindow.Id(lidosPelaB[0]));
            Igual("B le nome alterado", "Ana Alterada", B.MainWindow.Campo(lidosPelaB[2], "Nome"));

            A.MainWindow.Carregar(arquivoB);
            Igual("A le o arquivo da B", 3, A.MainWindow.Quantidade);
            Igual("A le nome alterado", "João B", A.MainWindow.Nomes[0]);
            Igual("A le ultimo Id", 4, A.MainWindow.Ids[2]);

            Secao("11. Arquivo inexistente");
            string inexistente = Path.Combine(pasta, "nao-existe.json");
            A.MainWindow.Carregar(inexistente);
            Igual("A: arquivo inexistente = 0", 0, A.MainWindow.Quantidade);
            Igual("B: arquivo inexistente = 0", 0, B.MainWindow.Ler(inexistente).Length);

            Console.WriteLine();
            if (falhas == 0)
                Console.WriteLine("TODOS OS " + total + " TESTES PASSARAM.");
            else
                Console.WriteLine(falhas + " DE " + total + " TESTES FALHARAM.");

            Environment.ExitCode = falhas == 0 ? 0 : 1;
        }

        static void Secao(string titulo)
        {
            Console.WriteLine();
            Console.WriteLine("== " + titulo + " ==");
        }

        static void Ok(string nome, bool condicao)
        {
            total++;
            if (!condicao) falhas++;

            Console.WriteLine((condicao ? "  PASS  " : "  FALHA ") + nome);
        }

        static void Igual(string nome, object esperado, object obtido)
        {
            bool ok = Equals(esperado, obtido);
            total++;
            if (!ok) falhas++;

            string detalhe = ok ? "" : "  (esperado: " + esperado + " | obtido: " + obtido + ")";
            Console.WriteLine((ok ? "  PASS  " : "  FALHA ") + nome + detalhe);
        }

        static void Perto(string nome, double esperado, double obtido)
        {
            bool ok = Math.Abs(esperado - obtido) < 0.005;
            total++;
            if (!ok) falhas++;

            string detalhe = ok ? "" : "  (esperado: " + esperado + " | obtido: " + obtido + ")";
            Console.WriteLine((ok ? "  PASS  " : "  FALHA ") + nome + detalhe);
        }

        static void VetorIgual(string nome, int[] esperado, int[] obtido)
        {
            bool ok = esperado.Length == obtido.Length;

            for (int i = 0; ok && i < esperado.Length; i++)
                if (esperado[i] != obtido[i]) ok = false;

            total++;
            if (!ok) falhas++;

            Console.WriteLine((ok ? "  PASS  " : "  FALHA ") + nome);
        }
    }
}
