# -*- coding: utf-8 -*-
"""
VERSÃO COMENTADA — Projeto POOI 4º bimestre
Classificação do Campeonato Brasileiro Série A 2026

Lê apenas Time, Jogos, Vitórias, Empates e Derrotas da planilha.
Calcula os pontos, gera o PDF e a página HTML.

Execute:
    python projeto_comentado.py
"""

import pandas as pd
from reportlab.pdfgen import canvas


# ===========================================================================
# 1) LEITURA DA PLANILHA E CÁLCULO
# ===========================================================================

def vazio(valor):
    """True quando a célula está vazia."""
    texto = str(valor).strip()
    if texto == '':
        return True
    if texto.upper() == 'NAN':
        return True
    if texto.upper() == 'NONE':
        return True
    return False


def numero(valor):
    """Converte a célula em inteiro, aceitando 30, 30.0, '30' e '30,0'."""
    if vazio(valor):
        return 0
    texto = str(valor).strip().replace(' ', '').replace(',', '.')
    try:
        return int(float(texto))
    except ValueError:
        return 0


def ler_planilha(caminho):
    """Lê o .xlsx e devolve os dados da classificação."""
    try:
        arquivo_excel = pd.ExcelFile(caminho)
    except Exception as erro:
        print(f'Não foi possível abrir a planilha: {erro}')
        return {}

    for nome_aba in arquivo_excel.sheet_names:
        # header=None mantém as linhas de título acima da tabela.
        tabela = arquivo_excel.parse(nome_aba, header=None)

        linha_cabecalho = -1
        for i in range(len(tabela)):
            valores = []
            for j in range(len(tabela.columns)):
                valores.append(tabela.iloc[i, j])

            normalizados = []
            for valor in valores:
                if not vazio(valor):
                    normalizados.append(str(valor).upper().strip())

            tem_time = False
            tem_jogos = False
            for valor in normalizados:
                if 'TIME' in valor:
                    tem_time = True
                if 'JOGOS' in valor:
                    tem_jogos = True

            if tem_time and tem_jogos:
                linha_cabecalho = i
                break

        if linha_cabecalho == -1:
            continue

        colunas = {'time': -1, 'jogos': -1, 'vitorias': -1, 'empates': -1, 'derrotas': -1}
        for i in range(len(tabela.columns)):
            valor = tabela.iloc[linha_cabecalho, i]
            nome = str(valor).upper().strip()
            if 'TIME' in nome and colunas['time'] == -1:
                colunas['time'] = i
            if 'JOGOS' in nome and colunas['jogos'] == -1:
                colunas['jogos'] = i
            if 'VIT' in nome and colunas['vitorias'] == -1:
                colunas['vitorias'] = i
            if 'EMP' in nome and colunas['empates'] == -1:
                colunas['empates'] = i
            if 'DER' in nome and colunas['derrotas'] == -1:
                colunas['derrotas'] = i

        if colunas['time'] == -1:
            continue
        if colunas['vitorias'] == -1 or colunas['empates'] == -1 or colunas['derrotas'] == -1:
            print('A planilha precisa das colunas Vitórias, Empates e Derrotas.')
            continue

        times = []
        linhas_vazias = 0
        for i in range(linha_cabecalho + 1, len(tabela)):
            nome_time = tabela.iloc[i, colunas['time']]

            if vazio(nome_time):
                linhas_vazias += 1
                if linhas_vazias >= 3:
                    break
                continue
            linhas_vazias = 0

            nome_time = str(nome_time).strip()

            valor_jogos = tabela.iloc[i, colunas['jogos']]
            if vazio(valor_jogos):
                break
            jogos = numero(valor_jogos)
            if jogos == 0:
                break

            vitorias = numero(tabela.iloc[i, colunas['vitorias']])
            empates = numero(tabela.iloc[i, colunas['empates']])
            derrotas = numero(tabela.iloc[i, colunas['derrotas']])

            times.append({
                'time': nome_time,
                'jogos': jogos,
                'vitorias': vitorias,
                'empates': empates,
                'derrotas': derrotas,
            })

        if len(times) == 0:
            continue

        dados = {'times': times}
        return classificar(dados)

    print('A planilha não tem uma tabela com as colunas Time e Jogos.')
    return {}


def classificar(dados):
    """Calcula pontos, ordena e define a posição de cada time."""
    times = dados['times']

    for time in times:
        time['pontos'] = time['vitorias'] * 3 + time['empates']
        if time['jogos'] > 0:
            time['aproveitamento'] = time['pontos'] / (time['jogos'] * 3) * 100
        else:
            time['aproveitamento'] = 0

    ordenados = []
    for time in times:
        posicao = len(ordenados)
        for i in range(len(ordenados)):
            atual = ordenados[i]
            if time['pontos'] > atual['pontos']:
                posicao = i
                break
            if time['pontos'] == atual['pontos']:
                if time['time'].upper() < atual['time'].upper():
                    posicao = i
                    break
        ordenados.insert(posicao, time)

    for i in range(len(ordenados)):
        time = ordenados[i]
        if i > 0 and time['pontos'] == ordenados[i - 1]['pontos']:
            time['posicao'] = ordenados[i - 1]['posicao']
        else:
            time['posicao'] = i + 1
        time['indice'] = i
        if len(ordenados) >= 8:
            time['g4'] = i < 4
            time['z4'] = i >= len(ordenados) - 4
        else:
            time['g4'] = True
            time['z4'] = False

    for i in range(len(ordenados)):
        time = ordenados[i]
        empatado = False
        if i > 0 and time['pontos'] == ordenados[i - 1]['pontos']:
            empatado = True
        if i < len(ordenados) - 1 and time['pontos'] == ordenados[i + 1]['pontos']:
            empatado = True
        time['empatado'] = empatado

        if empatado:
            time['posicao_texto'] = str(time['posicao']) + 'º='
        else:
            time['posicao_texto'] = str(time['posicao']) + 'º'

    total_jogos = 0
    total_pontos = 0
    for time in ordenados:
        total_jogos += time['jogos']
        total_pontos += time['pontos']

    dados['times'] = ordenados
    dados['total_times'] = len(ordenados)
    dados['total_partidas'] = total_jogos // 2
    dados['total_pontos'] = total_pontos

    if dados['total_partidas'] > 0:
        dados['media_pontos'] = total_pontos / dados['total_partidas']
    else:
        dados['media_pontos'] = 0

    dados['lider'] = ordenados[0]
    dados['lanterna'] = ordenados[-1]

    jogos_max = 0
    for time in ordenados:
        if time['jogos'] > jogos_max:
            jogos_max = time['jogos']
    dados['jogos_max'] = jogos_max

    times_menos_jogos = []
    for time in ordenados:
        if time['jogos'] < jogos_max:
            times_menos_jogos.append(time)
    dados['times_menos_jogos'] = times_menos_jogos

    return dados


def imprimir_classificacao(dados):
    """Mostra a classificação no terminal."""
    print()
    print()
    print('POS  TIME                   J    V    E    D   PTS   APROV.')
    print('-' * 66)
    for time in dados['times']:
        marca = ' '
        if time['g4']:
            marca = 'G'
        if time['z4']:
            marca = 'Z'
        print(
            f"{time['posicao']:>3}º {marca} "
            f"{time['time'][:20]:<20} "
            f"{time['jogos']:>3} {time['vitorias']:>4} {time['empates']:>4} "
            f"{time['derrotas']:>4} {time['pontos']:>5} "
            f"{time['aproveitamento']:>6.1f}%"
        )
    print('-' * 66)
    print(f"Líder: {dados['lider']['time']} ({dados['lider']['pontos']} pts)")
    print(f"Lanterna: {dados['lanterna']['time']} ({dados['lanterna']['pontos']} pts)")
    print()



# ===========================================================================
# 2) RELATÓRIO PDF
# ===========================================================================

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


NOTA_EMPATES = 'Times com a mesma pontuação ocupam a mesma colocação, sem os critérios oficiais de desempate da CBF.'


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

    texto(pdf, 50, 704, f'Classificação após {dados["total_partidas"]} partidas', 7.5, 'Helvetica', cinza)

    metadados = [
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

    texto(pdf, 50, 100, NOTA_EMPATES, 6.5, 'Helvetica', cinza)

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



# ===========================================================================
# 3) PÁGINA WEB
# ===========================================================================

def montar_linhas(dados):
    """Monta o corpo da tabela, com uma linha por time."""
    linhas = []
    for time in dados['times']:
        classes = []
        if time['g4']:
            classes.append('g4')
        if time['z4']:
            classes.append('z4')
        classe = ' '.join(classes)

        posicao = time['posicao_texto']
        if time['empatado']:
            posicao = posicao.replace('=', '<span class="empate">=</span>')

        linhas.append(
            f'<tr class="{classe}">'
            f'<td class="pos">{posicao}</td>'
            f'<td>{time["time"]}</td>'
            f'<td class="num">{time["jogos"]}</td>'
            f'<td class="num">{time["vitorias"]}</td>'
            f'<td class="num">{time["empates"]}</td>'
            f'<td class="num">{time["derrotas"]}</td>'
            f'<td class="num pts">{time["pontos"]}</td>'
            '</tr>'
        )
    return '\n'.join(linhas)


def gerar_pagina(dados, caminho_html):
    """Lê o modelo txt e grava a página com os dados da classificação."""
    media = f'{dados["media_pontos"]:.2f}'.replace('.', ',')

    modelo = open('modelo_pagina.txt')
    html = modelo.read()
    modelo.close()

    html = html.replace('@@PARTIDAS@@', str(dados['total_partidas']))
    html = html.replace('@@CLUBES@@', str(dados['total_times']))
    html = html.replace('@@MEDIA@@', media)
    html = html.replace('@@LINHAS@@', montar_linhas(dados))
    html = html.replace('@@NOTA@@', NOTA_EMPATES)

    arquivo = open(caminho_html, 'w')
    arquivo.write(html)
    arquivo.close()
    return caminho_html


# ===========================================================================
# 4) MENU E PROGRAMA PRINCIPAL
# ===========================================================================

PLANILHA_EXEMPLO = 'planilha/projeto4bi.xlsx'
ARQUIVO_HTML = 'classificacao.html'
ARQUIVO_PDF = 'relatorio.pdf'


def escolher_planilha():
    caminho = input(f'Planilha (.xlsx) [Enter = {PLANILHA_EXEMPLO}]: ').strip()
    caminho = caminho.replace('"', '')
    if caminho == '':
        caminho = PLANILHA_EXEMPLO
    return caminho


def gerar_pagina_web(tabela):
    try:
        gerar_pagina(tabela, ARQUIVO_HTML)
        print(f'Página web criada: {ARQUIVO_HTML}')
    except Exception as erro:
        print(f'Erro ao gerar a página: {erro}')


def gerar_pdf(tabela):
    print('Gerando o relatório em PDF...')
    try:
        layout_07_minimalista(tabela, ARQUIVO_PDF)
        print(f'PDF criado: {ARQUIVO_PDF}')
    except Exception as erro:
        print(f'Erro ao gerar o PDF: {erro}')


def mostrar_menu():
    print()
    print('=' * 64)
    print(' CAMPEONATO BRASILEIRO SÉRIE A 2026 — PROJETO POOI 4º BIMESTRE')
    print('=' * 64)
    print(' 1 - Gerar a página web')
    print(' 2 - Gerar o relatório em PDF')
    print(' 3 - Gerar tudo (página web + PDF)')
    print(' 4 - Mostrar a classificação no terminal')
    print(' 0 - Sair')
    print('=' * 64)


def modo_interativo():
    caminho = escolher_planilha()
    try:
        tabela = ler_planilha(caminho)
    except Exception as erro:
        print(f'Erro ao ler a planilha: {erro}')
        return

    if tabela == {}:
        return

    print(f'\nTimes encontrados: {tabela["total_times"]}')
    imprimir_classificacao(tabela)

    while True:
        mostrar_menu()
        opcao = input('Escolha uma opção: ').strip()
        if opcao == '0':
            print('Até a próxima rodada!')
            break
        if opcao == '1':
            gerar_pagina_web(tabela)
        elif opcao == '2':
            gerar_pdf(tabela)
        elif opcao == '3':
            gerar_pdf(tabela)
            gerar_pagina_web(tabela)
        elif opcao == '4':
            imprimir_classificacao(tabela)
        else:
            print('Opção inválida. Digite 0, 1, 2, 3 ou 4.')


modo_interativo()
