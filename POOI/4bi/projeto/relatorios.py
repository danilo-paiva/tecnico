# -*- coding: utf-8 -*-
"""
Relatório em PDF com o canvas do reportlab (apostila 39).

Comandos usados: Canvas, setTitle, setFont, setFillColor, drawString, save.
O canto (0,0) fica embaixo à esquerda; por isso o y cresce de baixo para cima.
"""

from reportlab.pdfgen import canvas


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


def texto(pdf, x, y, conteudo, tamanho=9, fonte='Helvetica', cor='#111111'):
    """Escreve um texto na posição (x, y)."""
    pdf.setFont(fonte, tamanho)
    pdf.setFillColor(cor)
    pdf.drawString(x, y, str(conteudo))


def linha(pdf, x, y, largura=490, cor='#D9D9D9'):
    """Desenha uma linha usando uma sequência de traços (sem rect/line)."""
    pdf.setFont('Helvetica', 6)
    pdf.setFillColor(cor)
    quantidade = int(largura / 3.34)
    pdf.drawString(x, y, '_' * quantidade)


def nota_empates():
    return 'Times com a mesma pontuação ocupam a mesma colocação, sem os critérios oficiais de desempate da CBF.'


def layout_07_minimalista(dados, caminho):
    """Desenha o layout 07 inteiro, de cima para baixo."""
    pdf = canvas.Canvas(caminho)
    pdf.setTitle('Minimalista Suíço — Série A 2026')

    tinta = '#111111'
    cinza = '#8A8A8A'
    verde = '#2F7D4A'
    vermelho = '#B3261E'
    vitoria = '#1F9D55'
    empate = '#E8A33D'
    derrota = '#C0392B'

    texto(pdf, 50, 812, 'CAMPEONATO BRASILEIRO  ·  SÉRIE A', 6.5, 'Helvetica-Bold', cinza)
    texto(pdf, 48, 768, 'SÉRIE A', 40, 'Helvetica-Bold', tinta)
    texto(pdf, 48, 726, '2026', 40, 'Helvetica', '#B9B9B9')

    data = dados['data_consulta']
    if data == '':
        data = 'data não informada'
    texto(pdf, 50, 704, f'Classificação após {dados["total_partidas"]} partidas  ·  dados de {data}', 7.5, 'Helvetica', cinza)

    if dados['rodada'] == 0:
        rodada = '-'
    else:
        rodada = str(dados['rodada'])
    metadados = [
        ('RODADA', rodada),
        ('CLUBES', str(dados['total_times'])),
        ('MÉDIA', f'{dados["media_pontos"]:.2f}'.replace('.', ',')),
    ]
    y = 800
    for rotulo, valor in metadados:
        texto(pdf, 470, y, rotulo, 5.5, 'Helvetica-Bold', cinza)
        texto(pdf, 470, y - 16, valor, 17, 'Helvetica-Bold', tinta)
        y -= 36

    linha(pdf, 50, 690, 490, tinta)

    y_header = 670
    texto(pdf, 50, y_header, 'POS', 6, 'Helvetica-Bold', cinza)
    texto(pdf, 84, y_header, 'CLUBE', 6, 'Helvetica-Bold', cinza)
    texto(pdf, 210, y_header, 'DISTRIBUIÇÃO DOS JOGOS', 5.5, 'Helvetica-Bold', cinza)
    texto(pdf, 430, y_header, 'J', 6, 'Helvetica-Bold', cinza)
    texto(pdf, 462, y_header, 'V', 6, 'Helvetica-Bold', cinza)
    texto(pdf, 494, y_header, 'E', 6, 'Helvetica-Bold', cinza)
    texto(pdf, 516, y_header, 'D', 6, 'Helvetica-Bold', cinza)
    texto(pdf, 545, y_header, 'PTS', 6, 'Helvetica-Bold', cinza)

    topo = 660
    altura = 500 / len(dados['times'])
    tamanho_nome = 9.2 * altura / 25.2
    if tamanho_nome < 6:
        tamanho_nome = 6
    if tamanho_nome > 9.2:
        tamanho_nome = 9.2
    for time in dados['times']:
        y_topo = topo - time['indice'] * altura
        y_base = y_topo - 16.5

        if time['g4']:
            cor_posicao = verde
        elif time['z4']:
            cor_posicao = vermelho
        else:
            cor_posicao = tinta

        texto(pdf, 50, y_base, time['posicao_texto'], 9.5, 'Helvetica-Bold', cor_posicao)
        texto(pdf, 84, y_base, time['time'][:22], tamanho_nome, 'Helvetica', tinta)

        x = 210
        for i in range(time['jogos']):
            if i < time['vitorias']:
                cor = vitoria
            elif i < time['vitorias'] + time['empates']:
                cor = empate
            else:
                cor = derrota
            texto(pdf, x, y_base + 1.5, '·', 8, 'Helvetica-Bold', cor)
            x += 180 / dados['jogos_max']

        texto(pdf, 430, y_base, time['jogos'], 9, 'Helvetica', '#555555')
        texto(pdf, 462, y_base, time['vitorias'], 9, 'Helvetica', '#555555')
        texto(pdf, 494, y_base, time['empates'], 9, 'Helvetica', '#555555')
        texto(pdf, 516, y_base, time['derrotas'], 9, 'Helvetica', '#555555')
        texto(pdf, 545, y_base, time['pontos'], 12, 'Helvetica-Bold', tinta)

    linha(pdf, 50, 660 - (len(dados['times']) - 1) * altura - 22.5, 490, tinta)

    texto(pdf, 50, 132, 'FONTE', 5.5, 'Helvetica-Bold', cinza)
    if dados['fonte'] == '':
        fonte = 'Planilha da CBF'
    else:
        fonte = dados['fonte']
    texto(pdf, 50, 118, fonte, 7, 'Helvetica', tinta)
    texto(pdf, 50, 100, nota_empates(), 6.5, 'Helvetica', cinza)

    if len(dados['times_menos_jogos']) > 0:
        nomes = []
        for time in dados['times_menos_jogos']:
            nomes.append(time['time'])
        texto(pdf, 50, 90, 'Com menos jogos: ' + ', '.join(nomes) + '.', 6.5, 'Helvetica', cinza)

    texto(pdf, 470, 118, 'Página 1 de 1', 7, 'Helvetica', tinta)
    texto(pdf, 470, 100, 'G4: 4 primeiros  ·  Z4: 4 últimos', 6.5, 'Helvetica', cinza)

    texto(pdf, 430, 88, '·', 8, 'Helvetica-Bold', vitoria)
    texto(pdf, 438, 88, 'vitória', 5.5, 'Helvetica', cinza)
    texto(pdf, 473, 88, '·', 8, 'Helvetica-Bold', empate)
    texto(pdf, 481, 88, 'empate', 5.5, 'Helvetica', cinza)
    texto(pdf, 516, 88, '·', 8, 'Helvetica-Bold', derrota)
    texto(pdf, 524, 88, 'derrota', 5.5, 'Helvetica', cinza)
    texto(pdf, 430, 77, 'cada ponto = um jogo', 5.5, 'Helvetica', cinza)

    pdf.save()
    return caminho


def gerar_todos(dados):
    """Gera o PDF e devolve a lista de arquivos criados."""
    gerados = []
    for item in CATALOGO:
        caminho = item['arquivo']
        layout_07_minimalista(dados, caminho)
        print(f'  PDF criado: {caminho}')
        gerados.append(caminho)
    return gerados
