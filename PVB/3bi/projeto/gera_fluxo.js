const fs = require('fs');

// [tipo, texto, largura] — uma instrução por figura, largura ajustada ao conteúdo
const seq = [
  ['inicio', '', 100],
  ['input', 'r1', 80],
  ['input', 'r2', 80],
  ['input', 'm', 80],
  ['input', 'Isp', 90],
  ['proc', 'mu = 1.327 x 10^11', 170],
  ['proc', 'r1 = r1 x 10^6', 140],
  ['proc', 'r2 = r2 x 10^6', 140],
  ['proc', 'a = (r1 + r2) / 2', 150],
  ['proc', 'T = pi x raiz(a^3 / mu)', 190],
  ['proc', 'dias = T / 86400', 150],
  ['proc', 'meses = dias / 30', 140],
  ['proc2', 'dv1 = raiz(mu/r1) x (raiz(2 x r2 / (r1 + r2)) - 1)', 240],
  ['proc2', 'dv2 = raiz(mu/r2) x (1 - raiz(2 x r1 / (r1 + r2)))', 240],
  ['proc', 'dv = |dv1| + |dv2|', 150],
  ['proc2', 'razao = e^(dv x 1000 / (Isp x 9.81))', 230],
  ['proc', 'combustivel = m x (razao - 1)', 200],
  ['proc', 'toneladas = combustivel / 1000', 200],
  ['display', 'dias', 150],
  ['display', 'meses', 150],
  ['display', 'dv', 150],
  ['display', 'razao', 150],
  ['display', 'combustivel', 150],
  ['display', 'toneladas', 150],
  ['fim', '', 100],
];

const styles = {
  inicio: 'strokeWidth=2;html=1;shape=mxgraph.flowchart.terminator;whiteSpace=wrap;',
  input: 'html=1;strokeWidth=2;shape=manualInput;whiteSpace=wrap;rounded=1;size=26;arcSize=11;',
  proc: 'rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;',
  proc2: 'rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;',
  display: 'strokeWidth=2;html=1;shape=mxgraph.flowchart.display;whiteSpace=wrap;',
  fim: 'strokeWidth=2;html=1;shape=mxgraph.flowchart.terminator;whiteSpace=wrap;',
};
const heights = { inicio: 60, input: 60, proc: 30, proc2: 40, display: 60, fim: 60 };
const labels = { inicio: '&lt;b&gt;inicio&lt;/b&gt;', fim: '&lt;b&gt;fim&lt;/b&gt;' };

const esc = s => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const CX = 413;
let cells = [];
let id = 0, prev = null, y = 20;
for (const [tipo, v, w] of seq) {
  const i = 'n' + (++id);
  const h = heights[tipo];
  const value = v || labels[tipo] || '';
  cells.push(`        <mxCell id="${i}" parent="1" style="${styles[tipo]}" value="${esc(value)}" vertex="1">`);
  cells.push(`          <mxGeometry height="${h}" width="${w}" x="${CX - w / 2}" y="${y}" as="geometry" />`);
  cells.push('        </mxCell>');
  if (prev) {
    const e = 'n' + (++id);
    cells.push(`        <mxCell id="${e}" edge="1" parent="1" source="${prev}" style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;exitX=0.5;exitY=1;exitDx=0;exitDy=0;entryX=0.5;entryY=0;entryDx=0;entryDy=0;" target="${i}">`);
    cells.push('          <mxGeometry relative="1" as="geometry" />');
    cells.push('        </mxCell>');
  }
  prev = i;
  y += h + 40;
}

const drawio = '<mxfile host="app.diagrams.net">\n  <diagram name="Página-1" id="pvb3bi001">\n    <mxGraphModel grid="1" page="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" pageScale="1" pageWidth="827" pageHeight="1169" math="0" shadow="0">\n      <root>\n        <mxCell id="0" />\n        <mxCell id="1" parent="0" />\n' + cells.join('\n') + '\n      </root>\n    </mxGraphModel>\n  </diagram>\n</mxfile>\n';
fs.writeFileSync('fluxograma.drawio', drawio);

const cfg = JSON.stringify({ xml: drawio, toolbar: null, nav: true, page: true });
const html =
  '<!doctype html><html><head><meta charset="utf-8">' +
  '<style>body{margin:0;padding:10px;background:#fff}</style></head><body>' +
  '<div class="mxgraph" data-mxgraph="' + cfg.replace(/&/g, '&amp;').replace(/"/g, '&quot;') + '"></div>' +
  '<script src="https://viewer.diagrams.net/js/viewer-static.min.js"></' + 'script>' +
  '</body></html>';
fs.writeFileSync('fluxo_render.html', html);
console.log('ok');
