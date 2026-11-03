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
# 1) LEITURA DA PLANILHA E CÁLCULO
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

            times.append({
                'time': nome_time,
                'jogos': jogos,
                'vitorias': vitorias,
                'empates': empates,
                'derrotas': derrotas,
            })

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

def escapar(texto):
    """Evita que um nome de time quebre o HTML."""
    texto = str(texto)
    texto = texto.replace('&', '&amp;')
    texto = texto.replace('<', '&lt;')
    texto = texto.replace('>', '&gt;')
    texto = texto.replace('"', '&quot;')
    return texto


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

        posicao = escapar(time['posicao_texto'])
        if time['empatado']:
            posicao = posicao.replace('=', '<span class="empate">=</span>')

        linhas.append(
            f'<tr class="{classe}">'
            f'<td class="pos">{posicao}</td>'
            f'<td>{escapar(time["time"])}</td>'
            f'<td class="num">{time["jogos"]}</td>'
            f'<td class="num">{time["vitorias"]}</td>'
            f'<td class="num">{time["empates"]}</td>'
            f'<td class="num">{time["derrotas"]}</td>'
            f'<td class="num pts">{time["pontos"]}</td>'
            '</tr>'
        )
    return '\n'.join(linhas)


def gerar_pagina(dados, caminho_html):
    """Cria uma página simples, no mesmo estilo do PDF."""
    data = dados['data_consulta']
    if data == '':
        data = 'data não informada'
    fonte = dados['fonte']
    if fonte == '':
        fonte = 'Planilha da CBF'
    rodada = dados['rodada']
    if rodada == 0:
        rodada = '-'

    html = f'''<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="windows-1252">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Série A 2026 — Classificação</title>
<style>
  body {{ font-family: Arial, Helvetica, sans-serif; background: #FFFFFF; color: #111111;
         max-width: 860px; margin: 40px auto; padding: 0 22px; }}
  .cabecalho {{ border-bottom: 2px solid #111111; padding-bottom: 20px; }}
  .kicker {{ font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase; color: #777777; }}
  h1 {{ font-size: 72px; line-height: .9; margin: 10px 0 0; letter-spacing: -2px; }}
  h1 span {{ color: #BBBBBB; }}
  .sub {{ font-size: 14px; color: #777777; margin: 16px 0 0; }}
  .meta {{ display: flex; gap: 44px; margin: 24px 0 26px; }}
  .meta div {{ font-size: 10px; letter-spacing: 1.2px; text-transform: uppercase; color: #777777; }}
  .meta strong {{ display: block; font-size: 24px; letter-spacing: 0; color: #111111; margin-top: 3px; }}
  table {{ width: 100%; border-collapse: collapse; font-size: 15px; }}
  th {{ text-align: left; font-size: 10px; letter-spacing: 1.2px; text-transform: uppercase;
        color: #777777; padding: 10px 6px; border-bottom: 1px solid #111111; }}
  td {{ padding: 11px 6px; border-bottom: 1px solid #E5E5E5; }}
  .num {{ text-align: right; }}
  .pos {{ width: 54px; font-weight: bold; }}
  .pts {{ font-weight: bold; }}
  .g4 {{ background: #E5EBDC; }}
  .z4 {{ background: #F3DFD8; }}
  .g4 .pos {{ color: #2F4A32; }}
  .z4 .pos {{ color: #8E1B1B; }}
  .empate {{ color: #999999; font-weight: normal; }}
  .nota {{ font-size: 12px; line-height: 1.5; color: #777777; margin: 18px 0 0; max-width: 680px; }}
  .acoes {{ margin-top: 24px; }}
  .botao {{ display: inline-block; background: #111111; color: #FFFFFF; padding: 12px 18px;
            text-decoration: none; font-size: 13px; }}
  footer {{ margin-top: 34px; border-top: 1px solid #E5E5E5; padding-top: 14px;
            font-size: 12px; color: #777777; }}
  @media (max-width: 600px) {{
    body {{ margin: 24px auto; padding: 0 14px; }}
    h1 {{ font-size: 48px; }}
    .meta {{ gap: 22px; }}
    table {{ font-size: 13px; }}
    th, td {{ padding: 8px 3px; }}
    .pos {{ width: 40px; }}
  }}
</style>
</head>
<body>
  <header class="cabecalho">
    <div class="kicker">Campeonato Brasileiro · Série A</div>
    <h1>SÉRIE A <span>2026</span></h1>
    <p class="sub">Classificação após {dados['total_partidas']} partidas · dados de {escapar(data)}</p>
  </header>

  <div class="meta">
    <div>Rodada<strong>{rodada}</strong></div>
    <div>Clubes<strong>{dados['total_times']}</strong></div>
    <div>Média<strong>{f'{dados["media_pontos"]:.2f}'.replace('.', ',')}</strong></div>
  </div>

  <table>
    <caption class="kicker">Classificação do Campeonato Brasileiro Série A 2026</caption>
    <thead>
      <tr>
        <th>Pos</th>
        <th>Clube</th>
        <th class="num">J</th>
        <th class="num">V</th>
        <th class="num">E</th>
        <th class="num">D</th>
        <th class="num">Pts</th>
      </tr>
    </thead>
    <tbody>
{montar_linhas(dados)}
    </tbody>
  </table>

  <p class="nota">{escapar(NOTA_EMPATES)} G4: quatro primeiros. Z4: quatro últimos.</p>
  <div class="acoes">
    <a class="botao" href="relatorio.pdf" target="_blank" rel="noopener">Abrir relatório em PDF</a>
  </div>

  <footer>
    <p>Fonte: {escapar(fonte)} · dados de {escapar(data)} · planilha lida pelo Python.</p>
  </footer>
</body>
</html>
'''

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
