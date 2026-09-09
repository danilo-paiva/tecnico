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
   saida `dias` (uma por figura). Sem textos de rotulo nas saidas.
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
   processos proporcionais ao texto (curtos ~140, longos ~240); saidas uniformes
   (150). Tudo centralizado em uma coluna unica, espacamento vertical 40, coluna
   no centro da pagina A4 (pageWidth 827, x = 413 - largura/2).
8. **Estrutura do algoritmo**: `inicio` -> entradas -> atribuicoes (na ordem do
   codigo) -> saidas (na ordem exibida) -> `fim`. Decisoes so quando existirem
   no codigo. Loop = repete a sequencia com edge de volta (estilo 2bi).

## Como gerar

```bash
node scripts/gera_fluxo.js <especificacao.json>
```

O JSON de especificacao:

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
`display`. [tipo, texto, largura].

Saida: `<nome>.drawio` (fonte, abre no app.diagrams.net ou VS Code) e
`<nome>_render.html` (pagina limpa para conferencia/exportacao).

## Conferencia visual

Abrir o render no browser (tool browser, action `open` com URL
`file:///<caminho>/fluxo_render.html`), aguardar ~6s, e tirar screenshot para
conferir alinhamento/textos antes de exportar.

## Exportar PDF

Como o export do draw.io: pagina A4, diagrama no TAMANHO NATURAL (sem escala,
fonte legivel) e fluxo quebrando em quantas paginas forem necessarias (tipicamente 2):

```js
await wait(6000); // aguarda o viewer carregar o SVG
await tab.evaluate(() => {
  const st = document.createElement('style');
  st.textContent = '@page { size: A4 portrait; margin: 0; } body { margin: 0; padding: 0; }';
  document.head.appendChild(st);
  const d = document.querySelector('.mxgraph');
  const r = d.getBoundingClientRect();
  const delta = (793.7 - r.width) / 2 - r.left; // centraliza na largura da A4
  d.style.transform = 'translateX(' + delta.toFixed(1) + 'px)';
});
await tab.pdf({ path: '<caminho>/<nome>.pdf' });
```

Verificar: `/Count 2` no PDF (2 paginas). NAO escalar o diagrama nem gerar
pagina unica do tamanho do diagrama — o texto fica miudo e a visibilidade cai.

Depois limpar os temporarios (`fluxo_render.html`, screenshots) e manter apenas
`<nome>.drawio` e `<nome>.pdf` junto ao projeto.

## Observacoes

- XML do draw.io: `<mxfile>` > `<diagram>` > `<mxGraphModel ... pageWidth="827" pageHeight="1169">` > `<root>` com `<mxCell id="0"/>`, `<mxCell id="1" parent="0"/>` e as celulas.
- `value` com HTML usa entidades (`&lt;b&gt;inicio&lt;/b&gt;`).
- Arquivo gerado sem BOM; o draw.io abre normalmente.
- Se o projeto mudar muito, ajuste a tabela `seq` no JSON de especificacao —
  o script nao precisa ser editado.
