# -*- coding: utf-8 -*-
"""
relatório em PDF para a classificação do Campeonato Brasileiro.

Tudo é desenhado com o canvas do reportlab (apostila 39): Canvas, setFont,
setFillColor, drawString, drawRightString, rect, line,
setLineWidth, setStrokeColor, save.

O layout é desenhado pela classe Relatorio. O método gerar_todos() cria o
PDF na pasta do programa e devolve o catálogo usado pela página web.
"""

from reportlab.pdfgen import canvas


LARGURA = 595.28          # A4 retrato
ALTURA = 841.89

FONTE = {
    'sans': 'Helvetica',
    'sans_b': 'Helvetica-Bold',
    'serif': 'Times-Roman',
    'serif_b': 'Times-Bold',
    'mono': 'Courier',
    'display': 'Helvetica-Bold',
}


# ---------------------------------------------------------------------------
# catálogo do relatório (a página web usa estas informações)
# ---------------------------------------------------------------------------

CATALOGO = [
    {
        'numero': '07',
        'arquivo': '07_minimalista_suico.pdf',
        'titulo': 'Minimalista suíço',
        'descricao': 'Grade tipográfica com faixas de zona verdes/vermelhas e distribuição V/E/D.',
        'cor': '#FFFFFF',
        'destaque': '#E2231A',
    },
]


# ---------------------------------------------------------------------------
# classe base: atalhos de desenho usados pelo layout
# ---------------------------------------------------------------------------

class Relatorio:
    def __init__(self, dados, caminho, largura=LARGURA, altura=ALTURA, titulo=''):
        self.dados = dados
        self.caminho = caminho
        self.largura = largura
        self.altura = altura
        self.pdf = canvas.Canvas(caminho, pagesize=(largura, altura))
        self.pdf.setTitle(titulo)
        self.pdf.setAuthor('Projeto POOI - 4º bimestre')
        self.pdf.setSubject('Classificação do Campeonato Brasileiro Série A 2026')

    # -- texto ------------------------------------------------------------
    def texto(self, x, y, conteudo, tamanho=10, fonte=None, cor='#111111', alinhamento='esquerda'):
        if fonte is None:
            fonte = FONTE['sans']
        self.pdf.setFont(fonte, tamanho)
        self.pdf.setFillColor(cor)
        if alinhamento in ('centro', 'center'):
            self.pdf.drawCentredString(x, y, str(conteudo))
        elif alinhamento in ('direita', 'right'):
            self.pdf.drawRightString(x, y, str(conteudo))
        else:
            self.pdf.drawString(x, y, str(conteudo))

    def caixa(self, x, y, w, h, cor, raio=0, contorno=None, espessura=1):
        self.pdf.setFillColor(cor)
        if contorno is not None:
            self.pdf.setStrokeColor(contorno)
            self.pdf.setLineWidth(espessura)
        self.pdf.rect(x, y, w, h, stroke=1 if contorno else 0, fill=1)

    def linha(self, x1, y1, x2, y2, cor='#111111', espessura=1):
        self.pdf.setStrokeColor(cor)
        self.pdf.setLineWidth(espessura)
        self.pdf.line(x1, y1, x2, y2)

    def nota_empates(self):
        """Texto padrão que explica a regra de posição pedida no projeto."""
        return ('Times com a mesma pontuação ocupam a mesma colocação, sem os critérios '
                'oficiais de desempate da CBF.')

    def salvar(self):
        self.pdf.save()


# ---------------------------------------------------------------------------
# 01 — CLÁSSICO DE JORNAL
# ---------------------------------------------------------------------------

def layout_07_minimalista(dados, caminho):
    rel = Relatorio(dados, caminho, titulo='Minimalista Suíço — Série A 2026')
    tinta = '#111111'
    cinza = '#8A8A8A'
    linha_cor = '#D9D9D9'
    verde_zona = '#2F4A32'
    verde_fundo = '#E5EBDC'
    vermelho = '#8E1B1B'
    vermelho_fundo = '#F3DFD8'
    vitoria = '#1F9D55'
    empate = '#E8A33D'
    derrota = '#C0392B'

    rel.caixa(0, 0, rel.largura, rel.altura, '#FFFFFF')
    # cabeçalho tipográfico
    rel.texto(50, 812, 'CAMPEONATO BRASILEIRO  ·  SÉRIE A', 6.5, FONTE['sans_b'], cinza)
    rel.texto(48, 768, 'SÉRIE A', 40, FONTE['sans_b'], tinta)
    rel.texto(48, 726, '2026', 40, FONTE['sans'], '#B9B9B9')
    rel.texto(50, 704, f'Classificação após {dados["total_partidas"]} partidas  ·  dados de {dados["data_consulta"] or "14/10/2026"}',
              7.5, FONTE['sans'], cinza)

    # metadados à direita
    metadados = [
        ('RODADA', str(dados['rodada'] or 30)),
        ('CLUBES', str(dados['total_times'])),
        ('MÉDIA', f'{dados["media_pontos"]:.2f}'.replace('.', ',')),
    ]
    y = 800
    for rotulo, valor in metadados:
        rel.texto(545, y, rotulo, 5.5, FONTE['sans_b'], cinza, 'direita')
        rel.texto(545, y - 16, valor, 17, FONTE['sans_b'], tinta, 'direita')
        y -= 36

    rel.linha(50, 690, 545, 690, tinta, 2)

    # cabeçalho da tabela
    y_header = 670
    rel.texto(50, y_header, 'POS', 6, FONTE['sans_b'], cinza)
    rel.texto(84, y_header, 'CLUBE', 6, FONTE['sans_b'], cinza)
    rel.texto(210, y_header, 'DISTRIBUIÇÃO DOS JOGOS', 5.5, FONTE['sans_b'], cinza)
    rel.texto(420, y_header, 'J', 6, FONTE['sans_b'], cinza, 'direita')
    rel.texto(452, y_header, 'V', 6, FONTE['sans_b'], cinza, 'direita')
    rel.texto(484, y_header, 'E', 6, FONTE['sans_b'], cinza, 'direita')
    rel.texto(516, y_header, 'D', 6, FONTE['sans_b'], cinza, 'direita')
    rel.texto(545, y_header, 'PTS', 6, FONTE['sans_b'], cinza, 'direita')

    topo = 660
    altura = 25.2
    for time in dados['times']:
        y_topo = topo - time['indice'] * altura
        y_base = y_topo - 16.5

        # fundo das zonas: verde para o G4, vermelho para o Z4 (como no relatório 01)
        if time['g4']:
            rel.caixa(50, y_topo - altura, 495, altura, verde_fundo)
        elif time['z4']:
            rel.caixa(50, y_topo - altura, 495, altura, vermelho_fundo)

        rel.linha(50, y_topo, 545, y_topo, linha_cor, 0.5)
        rel.linha(74, y_topo, 74, y_topo - altura, '#E6E6E6', 0.4)

        cor_posicao = tinta
        if time['g4']:
            cor_posicao = verde_zona
        elif time['z4']:
            cor_posicao = vermelho
        rel.texto(50, y_base, time['posicao_texto'], 9.5, FONTE['sans_b'], cor_posicao)
        rel.texto(84, y_base, time['time'][:22], 9.2, FONTE['sans'], tinta)

        # distribuição V/E/D: um quadradinho por jogo (como no relatório 09)
        x = 210
        for i in range(time['jogos']):
            if i < time['vitorias']:
                cor = vitoria
            elif i < time['vitorias'] + time['empates']:
                cor = empate
            else:
                cor = derrota
            rel.caixa(x, y_base + 1.5, 5, 5, cor)
            x += 6

        rel.texto(420, y_base, time['jogos'], 9, FONTE['sans'], '#555555', 'direita')
        rel.texto(452, y_base, time['vitorias'], 9, FONTE['sans'], '#555555', 'direita')
        rel.texto(484, y_base, time['empates'], 9, FONTE['sans'], '#555555', 'direita')
        rel.texto(516, y_base, time['derrotas'], 9, FONTE['sans'], '#555555', 'direita')
        rel.texto(545, y_base, time['pontos'], 12, FONTE['sans_b'], tinta, 'direita')

    rel.linha(50, 660 - 20 * altura, 545, 660 - 20 * altura, tinta, 1.2)

    # rodapé
    rel.texto(50, 132, 'FONTE', 5.5, FONTE['sans_b'], cinza)
    rel.texto(50, 118, dados['fonte'] or 'Planilha da CBF', 7, FONTE['sans'], tinta)
    rel.texto(50, 100, rel.nota_empates(), 6.5, FONTE['sans'], cinza)
    if dados['times_menos_jogos']:
        nomes = ', '.join(t['time'] for t in dados['times_menos_jogos'])
        rel.texto(50, 90, f'Com menos jogos: {nomes}.', 6.5, FONTE['sans'], cinza)
    rel.texto(545, 118, 'Página 1 de 1', 7, FONTE['sans'], tinta, 'direita')
    rel.texto(545, 100, 'G4: 4 primeiros  ·  Z4: 4 últimos', 6.5, FONTE['sans'], cinza, 'direita')

    # legenda da distribuição
    rel.caixa(430, 89, 5, 5, vitoria)
    rel.texto(438, 88, 'vitória', 5.5, FONTE['sans'], cinza)
    rel.caixa(473, 89, 5, 5, empate)
    rel.texto(481, 88, 'empate', 5.5, FONTE['sans'], cinza)
    rel.caixa(516, 89, 5, 5, derrota)
    rel.texto(524, 88, 'derrota', 5.5, FONTE['sans'], cinza)
    rel.texto(545, 77, 'cada quadradinho = um jogo', 5.5, FONTE['sans'], cinza, 'direita')

    rel.salvar()
    return caminho


# ---------------------------------------------------------------------------
def gerar_todos(dados):
    """Gera o PDF na pasta do programa e devolve a lista de arquivos criados."""
    funcoes = [layout_07_minimalista]
    gerados = []
    for item, funcao in zip(CATALOGO, funcoes):
        caminho = item['arquivo']
        funcao(dados, caminho)
        print(f'  PDF criado: {caminho}')
        gerados.append(caminho)
    return gerados
