# POOI — 4º bimestre · Brasileirão Série A 2026

Página web + **1 relatório em PDF** com a classificação do Campeonato Brasileiro, lidos de uma planilha Excel.

O programa foi feito para o projeto do 4º bimestre de **Programação Orientada a Objetos**: usa arquivos texto (item 38), o canvas do **reportlab** (item 39) e **pandas/openpyxl** (item 40) da apostila.

![Página web do projeto](docs/web-desktop.png)

---

## O que o programa faz

1. Lê a planilha `.xlsx` da CBF (a planilha indicada pelo professor ou a de exemplo em `planilha/projeto4bi.xlsx`).
2. Calcula os pontos: **vitória = 3, empate = 1, derrota = 0**.
3. Ordena da maior para a menor pontuação e define a posição de cada clube.
4. Gera:
   - `classificacao.html` — a página web;
   - `07_minimalista_suico.pdf` — o relatório;

> Na apresentação, o professor pode indicar outra planilha. Basta informar o caminho quando o programa perguntar. A planilha **não é alterada**: o programa só lê.

---

## Como executar

Requisitos: Python 3.10 ou superior.

```bash
pip install -r requirements.txt
python main.py
```

Há duas versões do arquivo principal:

- `main.py` — versão **principal**, sem comentários, para entregar;
- `main_comentado.py` — mesma lógica, com cada etapa explicada, para estudar e apresentar;
- `comentado/` — projeto completo com versões comentadas de todos os módulos.

O menu interativo oferece:

```text
1 - Gerar a página web (classificacao.html)
2 - Gerar o relatório em PDF
3 - Gerar tudo (página web + PDF)
4 - Mostrar a classificação no terminal
0 - Sair
```

O programa é guiado por menu: informe a planilha e escolha a opção **3** para gerar a página e o PDF.

Depois de gerar, abra o arquivo `classificacao.html` no navegador.

---

## Regra de classificação

- Pontos: `3 × vitórias + 1 × empates`.
- A tabela é ordenada por pontos, do maior para o menor.
- **Times com a mesma pontuação ocupam a mesma posição** (`1º`, `2º`, `2º`, `4º`…), como pede o projeto, sem os critérios oficiais de desempate da CBF. Dentro do empate, a ordem de exibição usa vitórias e, por fim, o nome — mas a posição continua igual.
- **G4**: as quatro primeiras linhas da tabela.
- **Z4**: as quatro últimas linhas da tabela.
- Como a planilha tem Vasco e Chapecoense com 29 jogos, o programa não faz média nem projeção: usa os números exatamente como estão na planilha.

### O caso da 16ª posição

Corinthians e Internacional terminam com **32 pontos** e, pela regra do projeto, os dois ficam na **16ª posição**. Como o Z4 é definido pelas quatro últimas linhas, o **Internacional** aparece no Z4 e o Corinthians não. Os relatórios e a página web deixam isso explícito em uma nota. Se o professor preferir outra interpretação, basta mudar a linha que define `time['z4']` em `dados.py`.

---

# A página web

Arquivo: **`classificacao.html`**

- Placar de liderança com Flamengo (64) e Palmeiras (63).
- Tabela completa com posição, clube, jogos, vitórias, empates, derrotas, pontos e aproveitamento.
- G4 com fundo verde-claro e faixa amarela; Z4 com fundo vermelho-claro e faixa vermelha.
- Empates na pontuação marcados com `=` e com a legenda “posição dividida”.
- **Botão “Abrir classificação em PDF”** e **“Abrir tabela em PDF”**: os dois são apenas links para o PDF que o Python já gerou com reportlab. Nenhuma biblioteca web gera o PDF.
- Galeria com o layout, cada card abrindo o respectivo PDF.
- Layout responsivo: no celular, a tabela vira uma versão compacta com os números de cada clube abaixo do nome e o total de pontos sempre visível.
- A página não depende de internet: fontes do sistema e CSS embutido.

![Versão mobile da página](docs/web-mobile.png)

---

# O relatório

Todos usam os mesmos dados e o mesmo cálculo; muda a linguagem visual, o formato da página e a hierarquia da informação.

| # | Relatório | Página | Ideia visual |
|---|---|---|---|
| 07 | **Minimalista suíço** | A4 retrato | Grade rígida, faixas de zona verdes/vermelhas e distribuição V/E/D dos jogos. |

### Prévia do layout

| | |
|---|---|
| <img src="docs/relatorios/07.png" width="220"> | O layout 07 reúne a grade tipográfica, as faixas verdes do G4 e vermelhas do Z4 e a distribuição V/E/D de cada clube. |

O PDF e o HTML ficam na pasta do programa e são gerados novamente a cada execução.

---

# Estrutura do projeto

```text
POOI/4bi/projeto/
├── main.py                 # versão principal, sem comentários
├── main_comentado.py       # mesma versão, comentada para estudo
├── comentado/              # todos os módulos comentados (versão de estudo)
├── dados.py                # leitura do Excel, pontos, posições e estatísticas
├── relatorios.py           # classe Relatorio + o layout em reportlab
├── pagina_web.py           # gera o HTML da classificação e a galeria
├── clubes.py               # cores e apelidos dos 20 clubes
├── planilha/
│   └── projeto4bi.xlsx     # planilha de exemplo da CBF
├── classificacao.html      # página gerada
├── 07_minimalista_suico.pdf # relatório gerado
├── docs/                   # prévias para o README
├── requirements.txt
└── README.md
```

---

# Conteúdo do 4º bimestre aplicado

| Conteúdo da apostila | Onde aparece |
|---|---|
| **38 — Arquivos texto** (`open`, `write`, `close`, `try/except`) | `pagina_web.py` grava o HTML com `open(..., 'w', encoding='utf-8')`. |
| **39 — PDF com reportlab** (`Canvas`, `setFont`, `setFillColor`, `drawString`, `setTitle`, `save`) | O layout em `relatorios.py`. |
| **40 — pandas / openpyxl** (`ExcelFile`, `parse`, `read_excel`, DataFrame) | `dados.py` lê a planilha, encontra a linha de cabeçalho e percorre as linhas. |
| **Orientação a objetos** | Classe `Relatorio`, com métodos de desenho reaproveitados pelo layout. |
| **Funções, listas, dicionários, laços, f-strings** | Em todos os módulos. |

### Comandos de desenho usados no PDF

O layout 07 usa apenas a API básica do canvas apresentada na apostila:

| Comando | Para que foi usado |
|---|---|
| `setFont` / `setFillColor` | Tipografia e cores do texto. |
| `drawString` / `drawRightString` | Nomes dos clubes e colunas numéricas alinhadas à direita. |
| `rect` | Fundos das faixas do G4 e do Z4. |
| `line` / `setLineWidth` / `setStrokeColor` | Fios da tabela e separadores. |
| `setTitle` / `save` | Metadados e fechamento do PDF. |

Nenhuma biblioteca web é usada para gerar PDF. O único uso de HTML/CSS é na página que o Python escreve.

Para organizar o projeto também é usado `open(..., encoding='utf-8')` para o HTML sair com os acentos corretos.

---

# Observações

- **Outra planilha:** o leitor procura uma linha de cabeçalho que tenha `Time` e `Jogos`, e colunas com `Vitórias`, `Empates` e `Derrotas`. Linhas de título antes da tabela e linhas de fonte/observação depois são ignoradas. A planilha pode estar em qualquer aba.
- **Fonte dos dados:** a planilha de exemplo é a fornecida pelo professor; a fonte citada nos relatórios é a que está na própria planilha.
- **Galeria da página:** feita só com HTML/CSS, usando a paleta de cada relatório. Nenhuma biblioteca de imagem é necessária.
- **Fontes:** o PDF usa as fontes internas do reportlab (Helvetica e Helvetica-Bold), sem depender de arquivos externos.
- **Impressão:** a página web tem um bloco `@media print` que esconde botões e galeria e imprime a tabela.

---

## Checklist do projeto

- [x] Lê a planilha Excel indicada no momento da execução.
- [x] Calcula 3 pontos por vitória e 1 por empate.
- [x] Ordena da maior para a menor pontuação.
- [x] Exibe posição, clube, jogos, vitórias, empates, derrotas e pontos.
- [x] Times empatados ocupam a mesma posição.
- [x] Destaca os 4 primeiros e os 4 últimos com cores de fundo diferentes.
- [x] Página web com botão que abre a classificação em PDF.
- [x] PDFs gerados exclusivamente por Python/reportlab.
- [x] **layout 07 (Minimalista suíço)**, além do layout da página web.
- [x] Código comentado e organizado por assunto da apostila.
