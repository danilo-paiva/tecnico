---
name: fluxogramas-pvb
description: GATILHO — acionar esta skill SEMPRE que o pedido envolver criar, montar, gerar, corrigir, ajustar ou exportar: "fluxograma", "diagrama de fluxo", "fluxograma do projeto", "algoritmo visual", "diagrama do enunciado", arquivo .drawio, ou exportar fluxograma em PDF. Acionar ANTES de escrever qualquer XML .drawio ou usar Mermaid — a skill define o padrao obrigatorio (shapes nativos do draw.io, notacao generica sem codigo C#, uma instrucao por figura). NAO usar para outros tipos de diagrama (UML, ER, pacotes).
---

# Fluxogramas — padrao dos projetos do tecnico

Gerar fluxogramas no MESMO estilo visual que o aluno cria manualmente no draw.io
(mxfile XML nativo), sem Mermaid, sem codigo de linguagem, e com exportacao em PDF.

## Regras (obrigatorias)

1. **Uma instrucao por figura.** Nada de "a = ... ; b = ..." ou "dias, meses" na
   mesma caixa: cada atribuicao, cada entrada e cada saida tem sua propria figura.
2. **Sem codigo de linguagem.** Nada de `Math.Sqrt`, `double.Parse`, `GetLineText`,
   `ToString`, `.Content`. Notacao matematica generica:
   - `Math.Sqrt(x)` -> `raiz(x)`
   - `Math.Pow(a, 3)` -> `a^3`
   - `Math.Abs(v)` -> `|v|`
   - `Math.PI` -> `pi`
   - `Math.Pow(Math.E, x)` / `Math.Exp(x)` -> `e^(x)`
   - `x * 1000000` quando for "milhoes" -> `x 10^6` (idem 10^11 etc.)
   - `Console.WriteLine`/`label.Content` -> entrada/saida (shapes proprios)
3. **Atribuicao com `=`**: `variavel = expressao` (estilo do projeto do 1bi).
4. **Entrada e saida mostram so variaveis**: entrada `r1` (um por figura);
   saida `dias` (uma por figura). Sem textos de rotulo nas saidas — quando o
   formato/valor exibido importar, use complemento curto entre parenteses
   (ex.: `agora (dd/MM/yyyy HH:mm:ss)`, `mes (extenso)`).
5. **Tipos de figura** (shapes usados nos projetos anteriores):

| Tipo | Shape draw.io (style) | Uso | Altura |
| --- | --- | --- | --- |
| Terminal | `strokeWidth=2;html=1;shape=mxgraph.flowchart.terminator;whiteSpace=wrap;` | inicio / fim (valor `<b>inicio</b>` / `<b>fim</b>`) | 60 |
| Entrada | `html=1;strokeWidth=2;shape=manualInput;whiteSpace=wrap;rounded=1;size=26;arcSize=11;` | variavel lida | 60 |
| Processo | `rounded=0;whiteSpace=wrap;html=1;strokeWidth=2;` | `var = expressao` | 30 (40 se quebra 2 linhas) |
| Saida | `strokeWidth=2;html=1;shape=mxgraph.flowchart.display;whiteSpace=wrap;` | variavel mostrada | 60 |
| Decisao | `strokeWidth=2;html=1;shape=mxgraph.flowchart.decision;whiteSpace=wrap;` | condicao (so se houver if) | 80 |

6. **Arestas**: `edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;exitX=0.5;exitY=1;exitDx=0;exitDy=0;entryX=0.5;entryY=0;entryDx=0;entryDy=0;`
   (vertical, de baixo da figura anterior pra cima da seguinte). Para rotulo S/N,
   filho da edge com style `edgeLabel;html=1;...` e value `S`/`N`.
7. **Largura por conteudo** (evita distorcao): terminais 100; entradas ~80-90;
   processos proporcionais ao texto (curtos ~140, longos ~240); decisoes ~180-240;
   saidas uniformes (150). Fluxo principal em uma coluna unica no centro da pagina
   A4 (pageWidth 827, x = 413 - largura/2), espacamento vertical 40. Com decisao,
   os desvios vao para colunas laterais e voltam a coluna central depois do
   merge (ver formato v2).
8. **Estrutura do algoritmo**: `inicio` -> entradas -> atribuicoes (na ordem do
   codigo) -> saidas (na ordem exibida) -> `fim`. Decisoes so quando existirem
   no codigo. Loop = repete a sequencia com edge de volta (estilo 2bi).

## Como gerar

```bash
node scripts/gera_fluxo.js <especificacao.json>
```

O JSON de especificacao (cadeia sequencial):

```json
{
  "nome": "fluxograma",       // nome dos arquivos de saida
  "nos": [
    ["inicio", "", 100],
    ["input", "r1", 80],
    ["proc", "mu = 1.327 x 10^11", 170],
    ["proc2", "dv1 = raiz(mu/r1) x (...)", 240],
    ["display", "dias", 150],
    ["fim", "", 100]
  ]
}
```

Tipos validos: `inicio`, `fim`, `input`, `proc` (altura 30), `proc2` (altura 40),
`decision` (altura 80), `display`. [tipo, texto, largura].

Saida: `<nome>.drawio` (fonte, abre no app.diagrams.net ou VS Code) e
`<nome>_render.html` (pagina limpa para conferencia/exportacao).

### Formato v2 (decisoes e loops)

Para codigo com `if` (decisao) e `while`/`for` (repeticao), use o grafo explicito:

```json
{
  "nome": "fluxograma",
  "nos": [
    {"id": "i", "tipo": "inicio", "x": 363, "y": 20, "l": 100},
    {"id": "a", "tipo": "input", "texto": "ano", "x": 373, "y": 120, "l": 80},
    {"id": "d", "tipo": "decision", "texto": "ano bissexto?", "x": 313, "y": 220, "l": 200},
    {"id": "o1", "tipo": "display", "texto": "ano bissexto", "x": 90, "y": 230, "l": 170},
    {"id": "o2", "tipo": "display", "texto": "ano nao bissexto", "x": 567, "y": 230, "l": 170},
    {"id": "f", "tipo": "fim", "x": 363, "y": 400, "l": 100}
  ],
  "arestas": [
    {"de": "i", "para": "a"},
    {"de": "d", "para": "o1", "rotulo": "S", "saida": "esq", "entrada": "dir"},
    {"de": "d", "para": "o2", "rotulo": "N", "saida": "dir", "entrada": "esq"},
    {"de": "o1", "para": "f", "entradaPonto": [0.25, 0]},
    {"de": "o2", "para": "f", "entradaPonto": [0.75, 0]}
  ]
}
```

- `x`, `y` = canto superior esquerdo da figura (como no draw.io); `l` = largura,
  `h` = altura (opcionais; sem eles usam-se os padroes por tipo).
- `saida`/`entrada`: `esq`, `dir`, `cima` ou `baixo` (padrao: sai por baixo,
  entra por cima).
- `saidaPonto`/`entradaPonto`: `[fx, fy]` de 0 a 1 para fixar o ponto exato na
  borda (use quando varias setas chegam na mesma figura).
- `pontos`: waypoints `[[x, y], ...]` do roteamento — use para o retorno do loop
  passar por fora das figuras.
- `rotulo`: texto da seta (S/N); `rotuloPos`: `[dx, dy]` desloca o rotulo.
- Loop: seta de volta com `saida: "esq"`, `pontos` de desvio e `entradaPonto` na
  figura de repeticao (a decisao do loop ou a entrada lida de novo).
- Specs de referencia com decisoes/loops: `PVB/4bi/atividade 11/fluxogramas/`
  (ex05, ex06, ex10..ex13).

## Conferencia visual

Abrir o render no browser (tool browser, action `open` com URL
`file:///<caminho>/<nome>_render.html`), aguardar ~6s, e tirar screenshot para
conferir alinhamento/textos antes de exportar. Alternativa headless:

```powershell
& "C:\Program Files\Google\Chrome\Application\chrome.exe" --headless --disable-gpu `
  --hide-scrollbars --screenshot="<caminho>\shot.png" --window-size=900,<altura> `
  --virtual-time-budget=15000 "file:///<caminho>/<nome>_render.html"
```

`<altura>` = maior `y + height` do .drawio + ~60 (evita sobra branca). Conferir:
textos sem corte, setas ligadas nas figuras certas, rotulos S/N visiveis.

## Exportar PDF

Como o export do draw.io: pagina A4, diagrama no TAMANHO NATURAL (sem escala,
fonte legivel) e fluxo quebrando em quantas paginas forem necessarias (tipicamente 2).
O `_render.html` gerado ja inclui `@page A4`, margens zero e centralizacao
automatica quando o SVG carrega — nao e preciso injetar estilo/transform de novo.

Opcao A — browser tool com `tab.pdf` (se disponivel): abrir o render, aguardar o
viewer carregar (~6s) e chamar `tab.pdf({ path: '<caminho>/<nome>.pdf' })`.

Opcao B — Chrome/Edge headless (Windows):

```powershell
& "C:\Program Files\Google\Chrome\Application\chrome.exe" --headless --disable-gpu `
  --no-pdf-header-footer --virtual-time-budget=15000 `
  --print-to-pdf="<caminho>\<nome>.pdf" "file:///<caminho>/<nome>_render.html"
```

Verificar: `/Count N` no PDF e tamanho do arquivo compativel com o conteudo
(ex.: 30-40 KB por fluxograma; um PDF vazio fica ~14 KB). NAO escalar o diagrama
nem gerar pagina unica do tamanho do diagrama — o texto fica miudo e a
visibilidade cai.

Depois limpar os temporarios (`<nome>_render.html`, screenshots) e manter apenas
`<nome>.drawio` e `<nome>.pdf` junto ao projeto.

## Observacoes

- XML do draw.io: `<mxfile>` > `<diagram>` > `<mxGraphModel ... pageWidth="827" pageHeight="1169">` > `<root>` com `<mxCell id="0"/>`, `<mxCell id="1" parent="0"/>` e as celulas.
- `value` com HTML usa entidades (`&lt;b&gt;inicio&lt;/b&gt;`).
- Arquivo gerado sem BOM; o draw.io abre normalmente.
- Se o projeto mudar muito, ajuste as figuras no JSON de especificacao (formato
  legado ou v2) — o script nao precisa ser editado.
