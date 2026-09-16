// Gera fluxograma .drawio (padrao draw.io nativo) + pagina de render HTML.
// Uso: node gera_fluxo.js <especificacao.json>
//
// Formato legado (cadeia sequencial):
//   { "nome": "fluxograma", "nos": [["tipo", "texto", largura], ...] }
//   Tipos: inicio | fim | input | proc | proc2 | display
//
// Formato v2 (grafo explicito, para decisoes/loops):
//   {
//     "nome": "fluxograma",
//     "nos": [ {"id":"i","tipo":"inicio","x":363,"y":20}, ... ],
//     "arestas": [
//       {"de":"i","para":"a"},
//       {"de":"d","para":"o1","rotulo":"S","saida":"esq","entrada":"dir"},
//       {"de":"d","para":"g","pontos":[[240,620],[240,430]],"entradaPonto":[0,0.35]},
//       {"de":"b","para":"d","rotuloPos":[-0.2,0]}
//     ]
//   }
//   Campos do no: id, tipo, texto, x, y, l (largura), h (altura).
//   Campos da aresta: rotulo, saida/entrada (esq|dir|cima|baixo), saidaPonto/entradaPonto
//   (fracao [x,y] de 0 a 1), pontos (waypoints do roteamento), rotuloPos (offset do rotulo).
//   Ver SKILL.md para regras e larguras recomendadas.

'use strict';
const fs = require('fs');

const spec = JSON.parse(fs.readFileSync(process.argv[2] || 'fluxo_spec.json', 'utf8'));
const nome = spec.nome || 'fluxograma';

const styles = {
  inicio: 'strokeWidth=2;html=1;shape=mxgraph.flowchart.terminator;whiteSpace=wrap;',
  fim: 'strokeWidth=2;html=1;shape=mxgraph.flowchart.terminator;whiteSpace=wrap;',
  input: 'html=1;strokeWidth=2;shape=manualInput;whiteSpace=wrap;rounded=1;size=26;arcSize=11;',
  proc: 'rounded=0;whiteSpace=wrap;html=1;strokeWidth=2;',
  proc2: 'rounded=0;whiteSpace=wrap;html=1;strokeWidth=2;',
  decision: 'strokeWidth=2;html=1;shape=mxgraph.flowchart.decision;whiteSpace=wrap;',
  display: 'strokeWidth=2;html=1;shape=mxgraph.flowchart.display;whiteSpace=wrap;',
};
const heights = { inicio: 60, fim: 60, input: 60, proc: 30, proc2: 40, decision: 80, display: 60 };
const widths = { inicio: 100, fim: 100, input: 80, proc: 140, proc2: 240, decision: 200, display: 150 };
const labels = { inicio: '<b>inicio</b>', fim: '<b>fim</b>' };

const esc = s => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const CX = 413; // centro da pagina A4 (827)
const EDGE_BASE = 'edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;';
const EDGE = EDGE_BASE + 'exitX=0.5;exitY=1;exitDx=0;exitDy=0;entryX=0.5;entryY=0;entryDx=0;entryDy=0;';
const SIDES = { esq: [0, 0.5], dir: [1, 0.5], cima: [0.5, 0], baixo: [0.5, 1] };

const cells = [];

function legado() {
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
}

function grafo() {
  let eid = 0;
  const pos = new Map();
  let autoY = 20;
  for (const n of spec.nos) {
    if (!styles[n.tipo]) throw new Error('tipo invalido: ' + n.tipo);
    const w = n.l || widths[n.tipo];
    const h = n.h || heights[n.tipo];
    const x = n.x !== undefined ? n.x : CX - w / 2;
    const y = n.y !== undefined ? n.y : autoY;
    autoY = y + h + 40;
    pos.set(n.id, { x, y, w, h });
    const value = n.texto || labels[n.tipo] || '';
    cells.push(`        <mxCell id="${n.id}" parent="1" style="${styles[n.tipo]}" value="${esc(value)}" vertex="1">`);
    cells.push(`          <mxGeometry height="${h}" width="${w}" x="${x}" y="${y}" as="geometry" />`);
    cells.push('        </mxCell>');
  }
  for (const a of spec.arestas || []) {
    if (!pos.has(a.de)) throw new Error('aresta com origem inexistente: ' + a.de);
    if (!pos.has(a.para)) throw new Error('aresta com destino inexistente: ' + a.para);
    const [sx, sy] = a.saidaPonto || SIDES[a.saida || 'baixo'];
    const [ex, ey] = a.entradaPonto || SIDES[a.entrada || 'cima'];
    const e = 'e' + (++eid);
    const style = EDGE_BASE + `exitX=${sx};exitY=${sy};exitDx=0;exitDy=0;entryX=${ex};entryY=${ey};entryDx=0;entryDy=0;`;
    cells.push(`        <mxCell id="${e}" edge="1" parent="1" source="${a.de}" style="${style}" target="${a.para}">`);
    if (a.pontos && a.pontos.length) {
      cells.push('          <mxGeometry relative="1" as="geometry">');
      cells.push('            <Array as="points">');
      for (const [px, py] of a.pontos) cells.push(`              <mxPoint x="${px}" y="${py}" />`);
      cells.push('            </Array>');
      cells.push('          </mxGeometry>');
    } else {
      cells.push('          <mxGeometry relative="1" as="geometry" />');
    }
    cells.push('        </mxCell>');
    if (a.rotulo) {
      const l = 'e' + (++eid);
      const lx = a.rotuloPos ? a.rotuloPos[0] : 0;
      const ly = a.rotuloPos ? a.rotuloPos[1] : 0;
      cells.push(`        <mxCell id="${l}" parent="${e}" style="edgeLabel;html=1;align=center;verticalAlign=middle;resizable=0;points=[];" value="${esc(a.rotulo)}" vertex="1" connectable="0">`);
      cells.push(`          <mxGeometry x="${lx}" y="${ly}" relative="1" as="geometry"><mxPoint as="offset" /></mxGeometry>`);
      cells.push('        </mxCell>');
    }
  }
}

if (Array.isArray(spec.nos[0])) legado();
else grafo();

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
  '<style>@page{size:A4 portrait;margin:0}body{margin:0;padding:0;background:#fff}</style></head><body>' +
  '<div class="mxgraph" data-mxgraph="' + cfg.replace(/&/g, '&amp;').replace(/"/g, '&quot;') + '"></div>' +
  '<script src="https://viewer.diagrams.net/js/viewer-static.min.js"></' + 'script>' +
  '<script>(function(){var t=0;function c(){var d=document.querySelector(".mxgraph");' +
  'if(!d||!d.querySelector("svg")){if(++t<200)setTimeout(c,50);return;}' +
  'var r=d.getBoundingClientRect();var x=(793.7-r.width)/2-r.left;' +
  'd.style.transform="translateX("+x.toFixed(1)+"px)";}c();})();</' + 'script>' +
  '</body></html>';
fs.writeFileSync(nome + '_render.html', html);
console.log('gerado: ' + nome + '.drawio + ' + nome + '_render.html');
