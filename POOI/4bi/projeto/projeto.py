# -*- coding: utf-8 -*-
"""
Projeto POOI — 4º bimestre
Classificação do Campeonato Brasileiro Série A 2026

Arquivo único do projeto:
- lê a planilha .xlsx com pandas;
- calcula a classificação (3/1/0);
- gera a página classificacao.html;
- gera o PDF relatorio.pdf com reportlab.

Execute:
    python projeto.py
"""

import pandas as pd
from reportlab.pdfgen import canvas


# ===========================================================================
# 1) CLUBES
# ===========================================================================

CORES_TIMES = {
    'Athletico-PR':      ('#C4122E', '#111111', 'Athletico-PR'),
    'Atlético-MG':       ('#1A1A1A', '#FFFFFF', 'Atlético-MG'),
    'Bahia':             ('#0057A0', '#E30613', 'Bahia'),
    'Botafogo':          ('#1A1A1A', '#FFFFFF', 'Botafogo'),
    'Chapecoense':       ('#006B3F', '#FFFFFF', 'Chapecoense'),
    'Corinthians':       ('#1A1A1A', '#FFFFFF', 'Corinthians'),
    'Coritiba':          ('#005C3B', '#FFFFFF', 'Coritiba'),
    'Cruzeiro':          ('#003DA5', '#FFFFFF', 'Cruzeiro'),
    'Flamengo':          ('#C8102E', '#111111', 'Flamengo'),
    'Fluminense':        ('#7A0C2E', '#00613F', 'Fluminense'),
    'Grêmio':            ('#0D80BF', '#111111', 'Grêmio'),
    'Internacional':     ('#C8102E', '#FFFFFF', 'Internacional'),
    'Mirassol':          ('#B58900', '#006B3F', 'Mirassol'),
    'Palmeiras':         ('#006437', '#FFFFFF', 'Palmeiras'),
    'Red Bull Bragantino': ('#D71920', '#FFFFFF', 'Bragantino'),
    'Remo':              ('#002776', '#FFFFFF', 'Remo'),
    'Santos':            ('#1A1A1A', '#E8E8E8', 'Santos'),
    'São Paulo':         ('#C8102E', '#111111', 'São Paulo'),
    'Vasco':             ('#1A1A1A', '#FFFFFF', 'Vasco'),
    'Vitória':           ('#C8102E', '#111111', 'Vitória'),
}

# Cores usadas quando o time não está no dicionário acima.
PALETA_RESERVA = [
    '#1D4ED8', '#0E7C7B', '#B45309', '#7E22CE', '#BE123C',
    '#0F766E', '#4338CA', '#A16207', '#9D174D', '#155E75',
    '#4D7C0F', '#7C2D12', '#1E3A8A', '#86198F', '#065F46',
    '#92400E', '#312E81', '#9F1239', '#115E59', '#3F3F46',
]


def identidade(nome, indice=0):
    """Devolve (cor principal, cor secundária, apelido) de um clube."""
    if nome in CORES_TIMES:
        return CORES_TIMES[nome]
    cor = PALETA_RESERVA[indice % len(PALETA_RESERVA)]
    return (cor, '#FFFFFF', nome)





# ===========================================================================
# 2) LEITURA DA PLANILHA E CÁLCULO
# ===========================================================================

def sem_acento(texto):
    """Remove acentos para comparar cabeçalhos e nomes."""
    texto = str(texto)
    trocas = [
        ('á', 'a'), ('à', 'a'), ('â', 'a'), ('ã', 'a'), ('ä', 'a'),
        ('é', 'e'), ('è', 'e'), ('ê', 'e'), ('ë', 'e'),
        ('í', 'i'), ('ì', 'i'), ('î', 'i'), ('ï', 'i'),
        ('ó', 'o'), ('ò', 'o'), ('ô', 'o'), ('õ', 'o'), ('ö', 'o'),
        ('ú', 'u'), ('ù', 'u'), ('û', 'u'), ('ü', 'u'),
        ('ç', 'c'), ('ñ', 'n'),
        ('Á', 'A'), ('À', 'A'), ('Â', 'A'), ('Ã', 'A'), ('Ä', 'A'),
        ('É', 'E'), ('È', 'E'), ('Ê', 'E'), ('Ë', 'E'),
        ('Í', 'I'), ('Ì', 'I'), ('Î', 'I'), ('Ï', 'I'),
        ('Ó', 'O'), ('Ò', 'O'), ('Ô', 'O'), ('Õ', 'O'), ('Ö', 'O'),
        ('Ú', 'U'), ('Ù', 'U'), ('Û', 'U'), ('Ü', 'U'),
        ('Ç', 'C'), ('Ñ', 'N'),
    ]
    for antigo, novo in trocas:
        texto = texto.replace(antigo, novo)
    return texto


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


def pegar_data(texto):
    """Procura uma data dd/mm/aaaa dentro de um texto."""
    palavras = str(texto).split()
    for palavra in palavras:
        palavra = palavra.replace('.', '').replace(',', '').replace(';', '').replace(':', '').replace('(', '').replace(')', '').replace('[', '').replace(']', '')
        if len(palavra) == 10 and palavra[2] == '/' and palavra[5] == '/':
            if palavra[0:2].isnumeric() and palavra[3:5].isnumeric() and palavra[6:10].isnumeric():
                return palavra
    return ''


def pegar_rodada(texto):
    """Procura 'rodada 30' dentro de um texto."""
    texto = sem_acento(str(texto)).upper()
    posicao = texto.find('RODADA')
    if posicao == -1:
        return 0
    i = posicao + 6
    while i < len(texto) and not texto[i].isnumeric():
        i += 1
    numero_texto = ''
    while i < len(texto) and texto[i].isnumeric():
        numero_texto += texto[i]
        i += 1
    if numero_texto != '':
        return int(numero_texto)
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
                    normalizados.append(sem_acento(valor).upper().strip())

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
            nome = sem_acento(valor).upper().strip()
            if 'TIME' in nome and colunas['time'] == -1:
                colunas['time'] = i
            if 'JOGOS' in nome and colunas['jogos'] == -1:
                colunas['jogos'] = i
            if 'VITORIA' in nome and colunas['vitorias'] == -1:
                colunas['vitorias'] = i
            if 'EMPATE' in nome and colunas['empates'] == -1:
                colunas['empates'] = i
            if 'DERROTA' in nome and colunas['derrotas'] == -1:
                colunas['derrotas'] = i

        if colunas['time'] == -1:
            continue
        if colunas['vitorias'] == -1 or colunas['empates'] == -1 or colunas['derrotas'] == -1:
            print('A planilha precisa das colunas Vitórias, Empates e Derrotas.')
            continue

        textos_acima = []
        for i in range(linha_cabecalho):
            for j in range(len(tabela.columns)):
                valor = tabela.iloc[i, j]
                if not vazio(valor):
                    textos_acima.append(str(valor).strip())

        if len(textos_acima) > 0:
            titulo = textos_acima[0]
        else:
            titulo = 'CAMPEONATO BRASILEIRO'

        consulta = ''
        for texto in textos_acima:
            if 'DADOS' in sem_acento(texto).upper():
                consulta = texto
                break

        times = []
        indice_cor = 0
        linhas_vazias = 0
        for i in range(linha_cabecalho + 1, len(tabela)):
            nome_time = tabela.iloc[i, colunas['time']]

            if vazio(nome_time):
                linhas_vazias += 1
                if linhas_vazias >= 3:
                    break
                continue
            linhas_vazias = 0

            # A linha de fonte/observação pode estar em qualquer coluna.
            linha_tem_rotulo = False
            for j in range(len(tabela.columns)):
                texto_linha = sem_acento(tabela.iloc[i, j]).upper()
                if 'FONTE' in texto_linha:
                    linha_tem_rotulo = True
                if 'OBSERVACAO' in texto_linha:
                    linha_tem_rotulo = True
            if linha_tem_rotulo:
                break

            nome_time = str(nome_time).strip()

            jogos = 0
            vitorias = 0
            empates = 0
            derrotas = 0
            if colunas['jogos'] != -1:
                jogos = numero(tabela.iloc[i, colunas['jogos']])
            if colunas['vitorias'] != -1:
                vitorias = numero(tabela.iloc[i, colunas['vitorias']])
            if colunas['empates'] != -1:
                empates = numero(tabela.iloc[i, colunas['empates']])
            if colunas['derrotas'] != -1:
                derrotas = numero(tabela.iloc[i, colunas['derrotas']])

            if jogos == 0:
                jogos = vitorias + empates + derrotas

            cor, cor_secundaria, apelido = identidade(nome_time, indice_cor)
            times.append({
                'time': nome_time,
                'apelido': apelido,
                'jogos': jogos,
                'vitorias': vitorias,
                'empates': empates,
                'derrotas': derrotas,
                'cor': cor,
                'cor_secundaria': cor_secundaria,
            })
            indice_cor += 1

        if len(times) == 0:
            continue

        fonte = ''
        for i in range(linha_cabecalho + 1, len(tabela)):
            for j in range(len(tabela.columns)):
                valor = tabela.iloc[i, j]
                if 'FONTE' in sem_acento(valor).upper():
                    for k in range(j + 1, len(tabela.columns)):
                        proximo = tabela.iloc[i, k]
                        if not vazio(proximo):
                            fonte = str(proximo).strip()
                            break
                    break
            if fonte != '':
                break

        data_consulta = pegar_data(consulta)
        if data_consulta == '':
            data_consulta = pegar_data(titulo)

        dados = {
            'arquivo': caminho,
            'aba': nome_aba,
            'titulo': titulo,
            'consulta': consulta,
            'data_consulta': data_consulta,
            'rodada': pegar_rodada(consulta),
            'fonte': fonte,
            'times': times,
        }
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
                if sem_acento(time['time']).upper() < sem_acento(atual['time']).upper():
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
    print(dados['titulo'])
    if dados['consulta']:
        print(dados['consulta'])
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
# 3) RELATÓRIO PDF
# ===========================================================================

CATALOGO = [
    {
        'numero': '07',
        'arquivo': 'relatorio.pdf',
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



# ===========================================================================
# 4) PÁGINA WEB
# ===========================================================================

def escapar(texto):
    """Evita que um nome de time quebre o HTML."""
    texto = str(texto)
    texto = texto.replace('&', '&amp;')
    texto = texto.replace('<', '&lt;')
    texto = texto.replace('>', '&gt;')
    texto = texto.replace('"', '&quot;')
    return texto


def numero_br(valor, casas=1):
    """Formata 71.05 como 71,1 (padrão brasileiro)."""
    if casas == 2:
        return f'{valor:.2f}'.replace('.', ',')
    if casas == 1:
        return f'{valor:.1f}'.replace('.', ',')
    return f'{valor:.0f}'.replace('.', ',')


def montar_linhas(dados):
    """Monta o <tbody> da classificação."""
    linhas = []
    for time in dados['times']:
        classes = []
        if time['g4']:
            classes.append('g4')
        if time['z4']:
            classes.append('z4')
        if time['empatado']:
            classes.append('empatado')
        classe = ' '.join(classes)

        posicao = escapar(time['posicao_texto'])
        if time['empatado']:
            posicao = posicao.replace('=', '<span class="sinal-empate" title="posição dividida">=</span>')

        linhas.append(
            f'<tr class="{classe}">'
            f'<td class="pos">{posicao}</td>'
            f'<td class="clube"><span class="chip" style="background:{time["cor"]}"></span>'
            f'<span>{escapar(time["time"])}</span>'
            f'<span class="mini-stats">{time["jogos"]}J · {time["vitorias"]}V · {time["empates"]}E · '
            f'{time["derrotas"]}D · {numero_br(time["aproveitamento"], 1)}%</span></td>'
            f'<td>{time["jogos"]}</td>'
            f'<td>{time["vitorias"]}</td>'
            f'<td>{time["empates"]}</td>'
            f'<td>{time["derrotas"]}</td>'
            f'<td class="pts">{time["pontos"]}</td>'
            f'<td class="aproveitamento">{numero_br(time["aproveitamento"], 1)}%</td>'
            '</tr>'
        )
    return '\n'.join(linhas)


def montar_galeria():
    """Monta o cartão do relatório."""
    cartoes = []
    for item in CATALOGO:
        visual = (
            f'<span class="sem-miniatura" style="background:{item["cor"]};'
            f'color:{item["destaque"]}">{item["numero"]}</span>'
        )
        cartoes.append(
            f'<article class="relatorio" style="--cor:{item["cor"]};--destaque:{item["destaque"]}">'
            f'<a class="thumb" href="{item["arquivo"]}" target="_blank" rel="noopener" '
            f'aria-label="Abrir o relatório {escapar(item["titulo"])} em PDF">{visual}</a>'
            f'<div class="rel-info">'
            f'<span class="rel-num">{item["numero"]}</span>'
            f'<h3>{escapar(item["titulo"])}</h3>'
            f'<p>{escapar(item["descricao"])}</p>'
            f'<a class="rel-abrir" href="{item["arquivo"]}" target="_blank" rel="noopener">'
            f'Abrir PDF</a>'
            '</div>'
            '</article>'
        )
    return '\n'.join(cartoes)


def gerar_pagina(dados, caminho_html):
    """Cria o arquivo .html da classificação e devolve o caminho."""
    lider = dados['lider']
    if len(dados['times']) > 1:
        segundo = dados['times'][1]
        diferenca = lider['pontos'] - segundo['pontos']
    else:
        segundo = lider
        diferenca = 0
    titulo = 'Série A 2026 — Classificação'
    data = dados['data_consulta']
    if data == '':
        data = 'data não informada'
    fonte = dados['fonte']
    if fonte == '':
        fonte = 'planilha da CBF'
    rodada = dados['rodada']
    if rodada == 0:
        rodada = '-'

    if diferenca != 1:
        sufixo = 's'
    else:
        sufixo = ''

    html = f'''<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="windows-1252">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{titulo}</title>
<style>
:root {{
  --pitch: #062A21;
  --pitch-2: #0A3A2C;
  --pitch-3: #041F19;
  --chalk: #F4EFDF;
  --chalk-2: #E6DEC8;
  --canary: #F5D547;
  --ink: #132019;
  --muted: #9BB3A9;
  --g4: #1E8E5A;
  --z4: #C43D2F;
  --linha: rgba(244, 239, 223, .16);
  --display: "Bahnschrift Condensed", "DIN Condensed", "Arial Narrow", Impact, sans-serif;
  --texto: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif;
}}

* {{ box-sizing: border-box; }}

html {{ scroll-behavior: smooth; }}

body {{
  margin: 0;
  background:
    repeating-linear-gradient(90deg, rgba(255,255,255,.022) 0 90px, transparent 90px 180px),
    linear-gradient(180deg, var(--pitch) 0%, var(--pitch-2) 62%, var(--pitch-3) 100%);
  color: var(--chalk);
  font-family: var(--texto);
  font-size: 16px;
  line-height: 1.55;
  -webkit-font-smoothing: antialiased;
}}

a {{ color: var(--canary); }}
a:focus-visible, button:focus-visible {{
  outline: 3px solid var(--canary);
  outline-offset: 4px;
}}

.page {{ overflow: hidden; }}

.container {{
  width: min(1180px, calc(100% - 48px));
  margin: 0 auto;
}}

/* ---------- barra superior ---------- */
.topbar {{
  border-bottom: 1px solid var(--linha);
  padding: 18px 0;
  font-size: 11px;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: var(--muted);
}}
.topbar .container {{
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}}
.marca {{ display: flex; align-items: center; gap: 10px; color: var(--chalk); }}
.marca strong {{
  display: inline-grid;
  place-items: center;
  width: 30px; height: 30px;
  background: var(--canary);
  color: var(--ink);
  font-family: var(--display);
  font-size: 16px;
  letter-spacing: 0;
}}
.topbar .data {{ color: var(--chalk); }}

/* ---------- hero ---------- */
.hero {{
  position: relative;
  padding: 72px 0 64px;
  display: grid;
  grid-template-columns: 1.35fr .65fr;
  gap: 56px;
  align-items: end;
}}
.hero::before {{
  content: "";
  position: absolute;
  z-index: 0;
  right: -160px;
  top: -170px;
  width: 720px;
  height: 720px;
  border: 1px solid rgba(244, 239, 223, .14);
  border-radius: 50%;
  pointer-events: none;
}}
.hero::after {{
  content: "";
  position: absolute;
  z-index: 0;
  right: 336px;
  top: 8px;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--canary);
  opacity: .9;
}}
.hero-copy, .placar {{ position: relative; z-index: 1; }}

.kicker {{
  margin: 0 0 22px;
  color: var(--canary);
  font-family: var(--display);
  font-size: 15px;
  letter-spacing: .16em;
  text-transform: uppercase;
}}

h1 {{
  margin: 0;
  font-family: var(--display);
  font-size: clamp(70px, 11vw, 138px);
  line-height: .82;
  letter-spacing: -.01em;
  text-transform: uppercase;
  font-weight: 700;
}}
h1 .linha-2 {{
  display: block;
  color: transparent;
  -webkit-text-stroke: 2px var(--canary);
  text-stroke: 2px var(--canary);
}}
@supports not (-webkit-text-stroke: 1px black) {{
  h1 .linha-2 {{ color: var(--canary); }}
}}

.hero-texto {{
  max-width: 56ch;
  margin: 28px 0 0;
  color: #D6DECD;
  font-size: 16px;
}}
.hero-texto strong {{ color: var(--chalk); font-weight: 600; }}

.hero-numeros {{
  display: flex;
  flex-wrap: wrap;
  gap: 0;
  margin: 30px 0 0;
  border-top: 1px solid var(--linha);
  border-bottom: 1px solid var(--linha);
}}
.hero-numeros div {{
  padding: 12px 22px 12px 0;
  margin-right: 22px;
  border-right: 1px solid var(--linha);
}}
.hero-numeros div:last-child {{ border-right: 0; margin-right: 0; }}
.hero-numeros dt {{
  margin: 0;
  font-family: var(--display);
  font-size: 12px;
  letter-spacing: .13em;
  text-transform: uppercase;
  color: var(--muted);
}}
.hero-numeros dd {{
  margin: 2px 0 0;
  font-family: var(--display);
  font-size: 30px;
  line-height: 1;
  color: var(--chalk);
  font-variant-numeric: tabular-nums;
}}

.acoes {{ display: flex; flex-wrap: wrap; gap: 12px; margin-top: 30px; }}
.botao {{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 50px;
  padding: 0 24px;
  border: 1px solid var(--canary);
  border-radius: 2px;
  font-family: var(--display);
  font-size: 17px;
  letter-spacing: .08em;
  text-transform: uppercase;
  text-decoration: none;
  transition: transform .18s ease, background .18s ease, color .18s ease;
}}
.botao-principal {{ background: var(--canary); color: var(--ink); }}
.botao-principal:hover {{ transform: translateY(-2px); background: #FFE874; }}
.botao-vazio {{ color: var(--chalk); border-color: rgba(244,239,223,.45); }}
.botao-vazio:hover {{ transform: translateY(-2px); border-color: var(--chalk); }}

/* ---------- placar ---------- */
.placar {{
  background: #031712;
  border: 1px solid rgba(245, 213, 71, .55);
  box-shadow: inset 0 0 90px rgba(0, 0, 0, .55), 0 30px 60px rgba(0, 0, 0, .28);
  padding: 0 0 20px;
}}
.placar-topo {{
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding: 13px 20px;
  background: var(--canary);
  color: var(--ink);
  font-family: var(--display);
  font-size: 12px;
  letter-spacing: .16em;
  text-transform: uppercase;
}}
.placar-linha {{
  display: grid;
  grid-template-columns: 28px 1fr auto;
  align-items: center;
  gap: 10px;
  padding: 24px 20px;
  border-bottom: 1px dashed rgba(244,239,223,.2);
}}
.placar-linha:last-of-type {{ border-bottom: 0; }}
.placar-rank {{
  font-family: var(--display);
  font-size: 18px;
  color: var(--muted);
}}
.placar-time {{
  display: flex;
  align-items: center;
  gap: 10px;
  font-family: var(--display);
  font-size: 26px;
  letter-spacing: .02em;
}}
.placar-time i {{
  width: 12px; height: 12px;
  border: 1px solid rgba(244,239,223,.5);
  flex: none;
}}
.placar-pontos {{
  font-family: var(--display);
  font-size: 72px;
  line-height: .85;
  color: var(--canary);
  font-variant-numeric: tabular-nums;
}}
.placar-pontos small {{
  font-size: 12px;
  letter-spacing: .12em;
  color: var(--muted);
  margin-left: 4px;
}}
.placar-rodape {{
  margin: 6px 18px 0;
  padding-top: 12px;
  border-top: 1px solid rgba(245,213,71,.25);
  display: flex;
  justify-content: space-between;
  gap: 12px;
  color: var(--muted);
  font-size: 11px;
  letter-spacing: .08em;
  text-transform: uppercase;
}}
.placar-rodape strong {{ color: var(--chalk); }}

/* ---------- seções ---------- */
.secao {{ padding: 34px 0 0; }}
.secao-topo {{
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  margin-bottom: 18px;
}}
.secao-topo h2 {{
  margin: 0;
  font-family: var(--display);
  font-size: clamp(34px, 5vw, 54px);
  line-height: .95;
  text-transform: uppercase;
  letter-spacing: .01em;
}}
.secao-topo p {{
  margin: 6px 0 0;
  max-width: 52ch;
  color: var(--muted);
  font-size: 14px;
}}
.legenda {{
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: center;
  color: var(--muted);
  font-size: 11px;
  letter-spacing: .08em;
  text-transform: uppercase;
  white-space: nowrap;
}}
.legenda span {{ display: inline-flex; align-items: center; gap: 7px; }}
.legenda i {{ width: 12px; height: 12px; display: inline-block; }}
.legenda .g4 {{ background: var(--canary); }}
.legenda .z4 {{ background: var(--z4); }}

/* ---------- tabela ---------- */
.painel {{
  background: var(--chalk);
  color: var(--ink);
  border-top: 8px solid var(--canary);
  box-shadow: 0 34px 70px rgba(0, 0, 0, .38);
}}
.tabela-scroll {{ overflow-x: auto; }}
table {{
  width: 100%;
  min-width: 760px;
  border-collapse: collapse;
  font-variant-numeric: tabular-nums;
}}
caption {{
  position: absolute;
  width: 1px; height: 1px;
  overflow: hidden;
  clip: rect(0 0 0 0);
  white-space: nowrap;
}}
thead th {{
  padding: 14px 12px;
  background: #0B3B2E;
  color: var(--chalk-2);
  font-family: var(--display);
  font-size: 12px;
  font-weight: 400;
  letter-spacing: .15em;
  text-transform: uppercase;
  text-align: right;
}}
thead th:nth-child(1), thead th:nth-child(2) {{ text-align: left; }}
thead th:nth-child(1) {{ width: 78px; }}
thead th:nth-child(2) {{ width: auto; }}

tbody td {{
  padding: 13px 12px;
  border-bottom: 1px solid rgba(19, 32, 25, .12);
  text-align: right;
  font-size: 15px;
  color: #2A3A31;
}}
tbody td.pos, tbody td.clube {{ text-align: left; }}
tbody tr {{ transition: background .15s ease; }}
tbody tr:hover {{ background: #EDE6D2; }}
tbody td.pos {{
  font-family: var(--display);
  font-size: 22px;
  line-height: 1;
  color: var(--ink);
  padding-left: 22px;
  white-space: nowrap;
}}
tbody td.clube {{
  font-weight: 600;
  font-size: 16px;
  color: var(--ink);
  white-space: nowrap;
}}
.chip {{
  display: inline-block;
  width: 10px; height: 10px;
  margin-right: 10px;
  border: 1px solid rgba(19, 32, 25, .38);
  vertical-align: 1px;
}}
tbody td.pts {{
  font-family: var(--display);
  font-size: 28px;
  line-height: 1;
  color: var(--ink);
  width: 90px;
}}
tbody td.aproveitamento {{ color: #6A776E; font-size: 13px; }}
.mini-stats {{ display: none; }}
tbody tr.g4 {{ background: #E7F0DF; box-shadow: inset 6px 0 0 var(--canary); }}
tbody tr.g4:hover {{ background: #DEEBD3; }}
tbody tr.z4 {{ background: #F8E3DE; box-shadow: inset 6px 0 0 var(--z4); }}
tbody tr.z4:hover {{ background: #F3D7D0; }}
tbody tr.g4 td.pos {{ padding-left: 26px; }}
tbody tr.z4 td.pos {{ padding-left: 26px; }}
.sinal-empate {{
  font-family: var(--texto);
  font-size: 17px;
  font-weight: 700;
  line-height: 1;
  color: #8A5A00;
  vertical-align: .45em;
  margin-left: 2px;
}}
.legenda .sinal-legenda {{
  font-family: var(--texto);
  font-weight: 700;
  color: var(--canary);
  font-size: 15px;
}}

.tabela-pe {{
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  padding: 18px 22px;
  background: #EFE8D5;
  border-top: 1px solid rgba(19,32,25,.14);
  font-size: 12px;
  color: #5C6A60;
}}
.tabela-pe .botao {{
  min-height: 42px;
  font-size: 14px;
  padding: 0 18px;
}}
.botao-escuro {{
  background: var(--ink);
  border-color: var(--ink);
  color: var(--chalk);
}}
.botao-escuro:hover {{ background: #0B3B2E; border-color: #0B3B2E; transform: translateY(-2px); }}

/* ---------- galeria ---------- */
.relatorios {{ padding: 84px 0 30px; }}
.grade-relatorios {{
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 26px;
  margin-top: 30px;
}}
.relatorio {{
  border-top: 3px solid var(--destaque);
  background: rgba(3, 23, 18, .55);
  border-left: 1px solid var(--linha);
  border-right: 1px solid var(--linha);
  border-bottom: 1px solid var(--linha);
  display: flex;
  flex-direction: column;
  transition: transform .18s ease, border-color .18s ease;
}}
.relatorio:hover {{ transform: translateY(-3px); border-color: rgba(245,213,71,.5); }}
.relatorio:last-child {{
  grid-column: 1 / -1;
  display: grid;
  grid-template-columns: 1.05fr .95fr;
}}
.relatorio:last-child .thumb {{ height: 360px; }}
.relatorio:last-child .rel-info {{
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 28px 36px;
}}
.relatorio:last-child .rel-info h3 {{ font-size: 32px; }}
.relatorio:last-child .rel-info p {{ max-width: 38ch; font-size: 15px; }}
.relatorio:last-child .rel-abrir {{ align-self: flex-start; }}
.thumb {{
  display: flex;
  align-items: center;
  justify-content: center;
  height: 300px;
  padding: 16px;
  background: var(--cor);
  overflow: hidden;
}}
.thumb img {{
  max-width: 100%;
  max-height: 100%;
  width: auto;
  height: auto;
  object-fit: contain;
  box-shadow: 0 10px 24px rgba(0,0,0,.28);
  background: #FFF;
}}
.sem-miniatura {{
  font-family: var(--display);
  font-size: 84px;
  line-height: 1;
}}
.rel-info {{ padding: 18px 20px 20px; }}
.rel-num {{
  display: block;
  font-family: var(--display);
  font-size: 13px;
  letter-spacing: .16em;
  color: var(--canary);
}}
.rel-info h3 {{
  margin: 5px 0 6px;
  font-family: var(--display);
  font-size: 25px;
  line-height: 1.05;
  font-weight: 600;
}}
.rel-info p {{
  margin: 0 0 14px;
  color: var(--muted);
  font-size: 13.5px;
  line-height: 1.5;
}}
.rel-abrir {{
  color: var(--canary);
  font-size: 12px;
  letter-spacing: .1em;
  text-transform: uppercase;
  text-decoration: none;
  border-bottom: 1px solid rgba(245,213,71,.55);
  padding-bottom: 2px;
}}
.rel-abrir:hover {{ border-color: var(--canary); }}

/* ---------- rodapé ---------- */
footer {{
  margin-top: 70px;
  border-top: 1px solid var(--linha);
  padding: 30px 0 44px;
  color: var(--muted);
  font-size: 12px;
}}
footer .container {{
  display: grid;
  grid-template-columns: 1.4fr .6fr;
  gap: 30px;
}}
footer strong {{ color: var(--chalk); font-weight: 600; }}
footer p {{ margin: 0 0 8px; max-width: 62ch; }}
footer .assinatura {{ text-align: right; }}

/* ---------- animação única de entrada ---------- */
@keyframes subir {{
  from {{ transform: translateY(14px); }}
  to {{ transform: translateY(0); }}
}}
.hero-copy {{ animation: subir .6s ease both; }}
.placar {{ animation: subir .6s .1s ease both; }}
@media (prefers-reduced-motion: reduce) {{
  html {{ scroll-behavior: auto; }}
  .hero-copy, .placar {{ animation: none; }}
  .botao, .relatorio {{ transition: none; }}
}}

/* ---------- responsivo ---------- */
@media (max-width: 1000px) {{
  .hero {{ grid-template-columns: 1fr; gap: 36px; padding-top: 54px; }}
  .placar {{ max-width: 460px; }}
  .grade-relatorios {{ grid-template-columns: repeat(2, 1fr); }}
  .relatorio:last-child {{ grid-column: 1 / -1; display: grid; grid-template-columns: 1fr 1fr; }}
  .relatorio:last-child .thumb {{ height: 300px; }}
  .relatorio:last-child .rel-info {{ display: block; padding: 18px 20px 20px; }}
  .relatorio:last-child .rel-info h3 {{ font-size: 25px; }}
  .relatorio:last-child .rel-info p {{ font-size: 13.5px; }}
  footer .container {{ grid-template-columns: 1fr; }}
  footer .assinatura {{ text-align: left; }}
}}
@media (max-width: 820px) {{
  table {{ min-width: 0; }}
  .tabela-scroll {{ overflow-x: visible; }}
  thead th:nth-child(3), thead th:nth-child(4), thead th:nth-child(5),
  thead th:nth-child(6), thead th:nth-child(8),
  tbody td:nth-child(3), tbody td:nth-child(4), tbody td:nth-child(5),
  tbody td:nth-child(6), tbody td:nth-child(8) {{ display: none; }}
  thead th:nth-child(1) {{ width: 64px; }}
  tbody td {{ padding: 11px 10px; }}
  tbody td.pos {{ padding-left: 22px; }}
  tbody tr.g4 td.pos, tbody tr.z4 td.pos {{ padding-left: 26px; }}
  tbody td.clube {{ white-space: normal; }}
  .mini-stats {{
    display: block;
    margin: 3px 0 0 20px;
    color: #6A776E;
    font-size: 10.5px;
    font-weight: 400;
    letter-spacing: .01em;
    white-space: nowrap;
  }}
}}
@media (max-width: 680px) {{
  .container {{ width: calc(100% - 32px); }}
  .topbar {{ padding: 12px 0; }}
  .topbar .container {{ flex-wrap: wrap; gap: 8px; font-size: 10px; }}
  .topbar .container > div:nth-child(2) {{ display: none; }}
  .hero {{ padding: 40px 0 44px; }}
  h1 {{ font-size: clamp(64px, 19vw, 100px); }}
  .hero-numeros div {{ padding-right: 14px; margin-right: 14px; }}
  .grade-relatorios {{ grid-template-columns: 1fr; }}
  .relatorio:last-child {{ grid-column: auto; display: flex; }}
  .relatorio:last-child .thumb {{ height: 260px; }}
  .relatorio:last-child .rel-info {{ display: block; padding: 18px 20px 20px; }}
  .relatorio:last-child .rel-info h3 {{ font-size: 25px; }}
  .relatorio:last-child .rel-info p {{ font-size: 13.5px; }}
  .thumb {{ height: 260px; }}
  .secao-topo {{ flex-direction: column; align-items: flex-start; gap: 12px; }}
  .tabela-pe {{ flex-direction: column; align-items: flex-start; }}
  .placar-pontos {{ font-size: 52px; }}
}}

@media print {{
  body {{ background: #FFF; color: #111; }}
  .topbar, .hero .acoes, .relatorios, footer, .tabela-pe .botao {{ display: none; }}
  .hero {{ padding: 10px 0; }}
  h1 {{ color: #111; font-size: 48px; }}
  h1 .linha-2 {{ color: #111; -webkit-text-stroke: 0; }}
  .hero-texto, .hero-numeros dd {{ color: #111; }}
  .placar {{ border-color: #111; box-shadow: none; background: #FFF; }}
  .placar-pontos {{ color: #111; }}
  .painel {{ box-shadow: none; border-top-color: #111; }}
}}
</style>
</head>
<body>
<div class="page">
  <header class="topbar">
    <div class="container">
      <div class="marca"><strong>P</strong><span>POOI · 4º bimestre</span></div>
      <div>Projeto de Programação Orientada a Objetos</div>
      <div class="data">Dados de {escapar(data)}</div>
    </div>
  </header>

  <main class="container">
    <section class="hero">
      <div class="hero-copy">
        <p class="kicker">Campeonato Brasileiro · Série A 2026</p>
        <h1>Série A<span class="linha-2">2026</span></h1>
        <p class="hero-texto">
          A classificação completa depois de <strong>{rodada} rodadas</strong>.
          O programa lê a planilha da CBF, calcula <strong>3 pontos por vitória e 1 por empate</strong>
          e ordena os clubes da maior para a menor pontuação.
        </p>
        <dl class="hero-numeros">
          <div><dt>Clubes</dt><dd>{dados['total_times']}</dd></div>
          <div><dt>Partidas</dt><dd>{dados['total_partidas']}</dd></div>
          <div><dt>Pontos</dt><dd>{dados['total_pontos']}</dd></div>
          <div><dt>Por jogo</dt><dd>{numero_br(dados['media_pontos'], 2)}</dd></div>
        </dl>
        <div class="acoes">
          <a class="botao botao-principal" href="relatorio.pdf" target="_blank" rel="noopener">Abrir classificação em PDF</a>
          <a class="botao botao-vazio" href="#relatorios">Ver o relatório</a>
        </div>
      </div>

      <aside class="placar" aria-label="Placar da liderança">
        <div class="placar-topo"><span>Placar da liderança</span><span>R{rodada}</span></div>
        <div class="placar-linha">
          <span class="placar-rank">1</span>
          <span class="placar-time"><i style="background:{lider['cor']}"></i>{escapar(lider['time'])}</span>
          <span class="placar-pontos">{lider['pontos']}<small>PTS</small></span>
        </div>
        <div class="placar-linha">
          <span class="placar-rank">2</span>
          <span class="placar-time"><i style="background:{segundo['cor']}"></i>{escapar(segundo['time'])}</span>
          <span class="placar-pontos">{segundo['pontos']}<small>PTS</small></span>
        </div>
        <div class="placar-rodape">
          <span><strong>{diferenca} ponto{sufixo}</strong> de diferença</span>
          <span>{numero_br(lider['aproveitamento'], 0)}% de aproveitamento</span>
        </div>
      </aside>
    </section>

    <section class="secao" id="tabela">
      <div class="secao-topo">
        <div>
          <h2>Classificação</h2>
          <p>Ordenada por pontos. Clubes com a mesma pontuação ocupam a mesma posição, sem os critérios oficiais de desempate.</p>
        </div>
        <div class="legenda">
          <span><i class="g4"></i>G4 · quatro primeiros</span>
          <span><i class="z4"></i>Z4 · quatro últimos</span>
          <span><b class="sinal-legenda">=</b> posição dividida</span>
        </div>
      </div>

      <div class="painel">
        <div class="tabela-scroll">
          <table>
            <caption>Classificação do Campeonato Brasileiro Série A 2026 após {rodada} rodadas</caption>
            <thead>
              <tr>
                <th scope="col">Pos</th>
                <th scope="col">Clube</th>
                <th scope="col">J</th>
                <th scope="col">V</th>
                <th scope="col">E</th>
                <th scope="col">D</th>
                <th scope="col">Pts</th>
                <th scope="col">Aproveitamento</th>
              </tr>
            </thead>
            <tbody>
{montar_linhas(dados)}
            </tbody>
          </table>
        </div>
        <div class="tabela-pe">
          <span>{dados['total_times']} clubes · {dados['total_partidas']} partidas · {dados['total_pontos']} pontos · fonte: {escapar(fonte)}</span>
          <a class="botao botao-escuro" href="relatorio.pdf" target="_blank" rel="noopener">Abrir tabela em PDF</a>
        </div>
      </div>
    </section>

    <section class="relatorios" id="relatorios">
      <div class="secao-topo">
        <div>
          <h2>Um relatório, uma identidade</h2>
          <p>A classificação em uma única peça editorial. Cada PDF é gerado pelo programa Python com o reportlab — o botão apenas abre o arquivo pronto.</p>
        </div>
      </div>
      <div class="grade-relatorios">
{montar_galeria()}
      </div>
    </section>
  </main>

  <footer>
    <div class="container">
      <div>
        <p><strong>Como a posição é calculada:</strong> {escapar(rel_nota())} A zona de rebaixamento reúne as quatro últimas linhas da tabela.</p>
        <p>Fonte: {escapar(fonte)} · dados de {escapar(data)} · planilha lida pelo Python.</p>
      </div>
      <div class="assinatura">
        <p>POOI · 4º bimestre · 2026</p>
      </div>
    </div>
  </footer>
</div>
</body>
</html>
'''

    arquivo = open(caminho_html, 'w')
    arquivo.write(html)
    arquivo.close()
    return caminho_html


def rel_nota():
    """Mantém o texto da regra em um único lugar."""
    return ('cada vitória vale 3 pontos e cada empate vale 1. Times com a mesma pontuação '
            'ocupam a mesma colocação, sem os critérios oficiais de desempate da CBF.')



# ===========================================================================
# 5) MENU E PROGRAMA PRINCIPAL
# ===========================================================================

PLANILHA_EXEMPLO = 'planilha/projeto4bi.xlsx'
ARQUIVO_HTML = 'classificacao.html'


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
        arquivos = gerar_todos(tabela)
        print(f'{len(arquivos)} relatório(s) gerado(s).')
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

    print(f'\nPlanilha lida: {tabela["arquivo"]}')
    print(f'Aba: {tabela["aba"]}  ·  times encontrados: {tabela["total_times"]}')
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
