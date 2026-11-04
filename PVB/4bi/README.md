# Projeto do 4º Bimestre — PVB

Duas implementações completas do projeto de IMC (consultório de fisioterapia), ambas usando
**somente a classe `File` do `System.IO`**, sem bibliotecas de JSON de terceiros e sem
`StreamReader`/`StreamWriter`, `List<>`, `StringBuilder` ou `CultureInfo` — apenas o conteúdo
das aulas 01–08 (vetores, `for`, funções, `Math`, formatação, manipulação de string) e WPF.

Cada projeto tem **um único arquivo `.cs`**: `App`, `MainWindow` e as classes de apoio
(`Imc`, `Banco`/`ArquivoJson`, `Html`) ficam todos dentro de `MainWindow.xaml.cs`.

As duas são de **janela única**: não há segunda tela, diálogo (nem `MessageBox`), aba ou
alteração de UI em tempo de execução. As mensagens de erro e o resultado das operações
aparecem no rótulo do rodapé, e o relatório em texto fica numa **caixa de texto multilinha**
na própria janela (aula 04 — `AcceptsReturn`, `TextWrapping` e `Environment.NewLine`).

## As duas versões

| | **A — `projetoA`** | **B — `projetoB`** |
|---|---|---|
| Modelo em memória | vetores paralelos (`Ids[]`, `Nomes[]`, `Pesos[]`, `Alturas[]`, `Imcs[]`, `Classes[]` + `Quantidade`) | `string[]` em que cada posição é o **bloco JSON** de uma pessoa |
| Leitura do arquivo | linha a linha: `StartsWith` + `IndexOf`/`Substring`/`Trim`/`Replace` | separa os blocos entre `{` e `}` e extrai os campos por chave |
| Cadastrar / alterar / excluir | mexe nas posições dos vetores | gera um **novo vetor de blocos** (sem alterar o anterior) |
| Tela | igual nas duas: formulário no topo, botões, lista de pacientes, relatório e rodapé |

As duas gravam **o mesmo formato JSON** e foram testadas lendo o arquivo uma da outra.

## Como rodar

Requer o SDK 5.0.416 (VS2019) para compilar e qualquer runtime .NET 5+ para rodar.

- **A:** abrir `projetoA/projetoA.sln` no Visual Studio 2019 e F5
  ou `dotnet run --project projetoA/projeto/projetoA.csproj`
- **B:** abrir `projetoB/projetoB.sln` e F5
  ou `dotnet run --project projetoB/projeto/projetoB.csproj`

### Sem instalar .NET (pendrive)

As pastas `publicado/A` e `publicado/B` são publicações **self-contained** (`win-x64`, ~152 MB cada,
**não versionadas**): incluem o runtime e abrem com duplo clique em qualquer Windows x64, mesmo
Para regerar:

```bash
dotnet publish projetoA/projeto/projetoA.csproj -c Release -r win-x64 --self-contained true -o publicado/A
dotnet publish projetoB/projeto/projetoB.csproj -c Release -r win-x64 --self-contained true -o publicado/B
```

Arquivos gerados **na pasta de trabalho do programa**:

- `pessoas.json` — base de dados
- `grafico.html` — template Plotly com o vetor `r:` calculado pelas faixas de IMC
- `pessoas.html` — tabela com os dados das pessoas avaliadas

No F5/`dotnet run`, a pasta de trabalho é a do projeto (`projetoA/projeto`); na pasta
**publicada**, os três arquivos ficam ao lado do próprio `.exe`. Os botões de HTML mostram
só o nome do arquivo no rodapé. Na primeira execução a base começa vazia; use **Cadastrar**.

## Testes

```bash
cd PVB/4bi
dotnet run --project testes/testes.csproj
```

Resultado esperado: **TODOS OS 92 TESTES PASSARAM.**

O projeto `testes` referencia `projetoA.csproj` e `projetoB.csproj` (não copia os `.cs`),
então testa exatamente o código que roda na janela.

O que é coberto:

1. Fórmula do IMC (`70/1,75 = 22,86` etc.)
2. Limites de classificação (18,5 / 25 / 30 / 35 / 40) nas duas versões
3. Conversão dos números JSON com ponto decimal (`1.75`, `1.6`, `22.86`)
4. Leitura do template do enunciado nas duas versões (acentos, `"Classificação"`, `"Peso (Kg)"`)
5. Gravação no formato do template do enunciado (ponto decimal, sem vírgula decimal,
   vírgula em todos os campos menos no último)
6. Geração dos dois HTMLs, vetor `r:` e conteúdo da tabela
7. Cadastrar, consultar/alterar e excluir nas duas versões
8. Compatibilidade cruzada (A lê o arquivo da B e vice-versa)
9. Arquivo inexistente (primeira execução)

## Estrutura

```
4bi/
├── projetoA/
│   ├── projetoA.sln
│   └── projeto/
│       ├── projetoA.csproj
│       ├── App.xaml
│       ├── MainWindow.xaml
│       └── MainWindow.xaml.cs     App + janela + Imc + Banco + Html
├── projetoB/
│   ├── projetoB.sln
│   └── projeto/
│       ├── projetoB.csproj
│       ├── App.xaml
│       ├── MainWindow.xaml
│       └── MainWindow.xaml.cs     App + janela + Imc + ArquivoJson + Html
├── testes/
│   ├── testes.csproj              referencia os dois projetos
│   └── Program.cs                 executor de testes (sem NuGet)
└── README.md
```

## Observações

- Fonte mínima de **20** em toda a janela (`FontSize="20"` no `Window`, herdado por todos os
  controles; nenhum controle define fonte menor).
- O relatório em texto é montado com `Environment.NewLine` e exibido no `TextBox` multilinha
  `IsReadOnly="True"` da própria janela.
- O JSON usa **ponto** decimal (`1.75`); a tela usa **vírgula** (`1,75`). A conversão é feita
  pelo `Replace` na gravação e pela função `Numero` na leitura, sem `CultureInfo`.
- Nomes com aspas duplas são convertidos em apóstrofo na gravação para não quebrar o JSON.
- `File.ReadAllLines` / `File.WriteAllText` usam UTF-8 por padrão, então os acentos
  (`"Classificação"`, `Saúdavel`) são preservados.
- A busca do fim da linha dentro do bloco JSON usa `IndexOf('\n')` (sobrecarga de `char`).
  Em .NET 5 o `IndexOf("\n")` com `string` é sensível à cultura e **não encontra** um `\n`
  isolado dentro de `\r\n` — foi exatamente esse detalhe que quebrou a leitura na primeira versão.
- Os arquivos `pessoas.json`, `grafico.html` e `pessoas.html` são gravados na pasta de
  trabalho do programa, com nome relativo: no F5/`dotnet run`, normalmente a pasta do projeto
  (`projetoA/projeto`); ao abrir o `.exe` publicado, a própria pasta `publicado/A` (ou `B`).
- As pastas ficaram sem espaço (`projetoA`/`projetoB`) para não quebrar caminhos de build.
- `RollForward=Major` nos dois `.csproj`: o build continua em `net5.0`/VS2019, mas o app roda
  em qualquer runtime .NET 5+ instalado (6, 8, 10).
- As classes `Imc`, `Banco`/`ArquivoJson` e `Html` são apenas agrupadores de funções e
  procedimentos `static` — como `Math` e `Console` (aulas 02/05) e as funções da aula 08.
  Não há criação de objeto (`new`), construtor, herança nem propriedade.
