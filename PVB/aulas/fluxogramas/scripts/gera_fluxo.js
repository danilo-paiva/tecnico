// Gera fluxograma .drawio (padrao draw.io nativo) + pagina de render HTML.
// Uso: node gera_fluxo.js <especificacao.json>
// Spec: { "nome": "fluxograma", "nos": [["tipo", "texto", largura], ...] }
// Tipos: inicio | fim | input | proc | proc2 | display
// Ver SKILL.md para regras e larguras recomendadas.

'use strict';
const fs = require('fs');

const spec = JSON.parse(fs.readFileSync(process.argv[2] || 'fluxo_spec.json', 'utf8'));
const nome = spec.nome || 'fluxograma';

const styles = {
  inicio: 'strokeWidth=2;html=1;shape=mxgraph.flowchart.terminator;whiteSpace=wrap;',
  fim: 'strokeWidth=2;html=1;shape=mxgraph.flowchart.terminator;whiteSpace=wrap;',
  input: 'html=1;strokeWidth=2;shape=manualInput;whiteSpace=wrap;rounded=1;size=26;arcSize=11;',
  proc: 'rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;',
  proc2: 'rounded=1;whiteSpace=wrap;html=1;absoluteArcSize=1;arcSize=14;strokeWidth=2;',
  display: 'strokeWidth=2;html=1;shape=mxgraph.flowchart.display;whiteSpace=wrap;',
};
const heights = { inicio: 60, fim: 60, input: 60, proc: 30, proc2: 40, display: 60 };
const labels = { inicio: '&lt;b&gt;inicio&lt;/b&gt;', fim: '&lt;b&gt;fim&lt;/b&gt;' };

const esc = s => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const CX = 413; // centro da pagina A4 (827)
const EDGE = 'edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;exitX=0.5;exitY=1;exitDx=0;exitDy=0;entryX=0.5;entryY=0;entryDx=0;entryDy=0;';

const cells = [];
let id = 0, prev = null, y = 20;
for (const [tipo, texto, largura] of spec.nos) {
  if (!styles[tipo]) throw new Error('tipo invalido: ' + tipo);
  const i = 'n' + (++id);
  const h = heights[tipo];
  const value = texto || labels[tipo] || '';
  cells.push(`        <mxCell id="${i}" parent="1" style="${styles[tipo]}" value="${esc(value)}" vertex="1">`);
  cells.push(`          <mxGeometry height="${h}" width="${largura}" x="${CX - largura / 2}" y="${y}" as="geometry" />`);
  cells.push('        </mxCell>');
  if (prev) {
    const e = 'n' + (++id);
    cells.push(`        <mxCell id="${e}" edge="1" parent="1" source="${prev}" style="${EDGE}" target="${i}">`);
    cells.push('          <mxGeometry relative="1" as="geometry" />');
    cells.push('        </mxCell>');
  }
  prev = i;
  y += h + 40;
}

const drawio =
  '<mxfile host="app.diagrams.net">\n' +
  '  <diagram name="Página-1" id="pvb3bi001">\n' +
  '    <mxGraphModel grid="1" page="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" pageScale="1" pageWidth="827" pageHeight="1169" math="0" shadow="0">\n' +
  '      <root>\n' +
  '        <mxCell id="0" />\n' +
  '        <mxCell id="1" parent="0" />\n' +
  cells.join('\n') +
  '\n      </root>\n    </mxGraphModel>\n  </diagram>\n</mxfile>\n';
fs.writeFileSync(nome + '.drawio', drawio);

const cfg = JSON.stringify({ xml: drawio, toolbar: null, nav: true, page: true });
const html =
  '<!doctype html><html><head><meta charset="utf-8">' +
  '<style>body{margin:0;padding:10px;background:#fff}</style></head><body>' +
  '<div class="mxgraph" data-mxgraph="' + cfg.replace(/&/g, '&amp;').replace(/"/g, '&quot;') + '"></div>' +
  '<script src="https://viewer.diagrams.net/js/viewer-static.min.js"></' + 'script>' +
  '</body></html>';
fs.writeFileSync(nome + '_render.html', html);
console.log('gerado: ' + nome + '.drawio + ' + nome + '_render.html');
