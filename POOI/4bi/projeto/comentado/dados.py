# -*- coding: utf-8 -*-
"""
VERSÃO COMENTADA — dados.py
Etapas: abrir o Excel com pandas, localizar o cabeçalho, ler as linhas,
calcular pontos, ordenar e distribuir as posições (empates dividem a posição).
"""


import pandas as pd
import openpyxl  # garante que o motor de leitura do .xlsx está instalado

import clubes


# ---------------------------------------------------------------------------
# funções auxiliares
# ---------------------------------------------------------------------------

# Normaliza o texto para comparar 'Vitórias' com 'vitorias', por exemplo.
def sem_acento(texto):
    """Troca letras acentuadas por letras simples (para comparar cabeçalhos)."""
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


# Evita tratar None, string vazia ou NaN como nome de time.
def vazio(valor):
    """True quando a célula está vazia (None ou NaN)."""
    if valor is None:
        return True
    texto = str(valor).strip()
    if texto == '' or texto.lower() == 'nan':
        return True
    return False


# Converte texto ou número do Excel em inteiro, aceitando vírgula.
def numero(valor):
    """Converte a célula para inteiro. Aceita 30, 30.0, '30' e '30,0'."""
    if vazio(valor):
        return 0
    texto = str(valor).strip().replace(' ', '').replace(',', '.')
    try:
        return int(float(texto))
    except ValueError:
        return 0


# Procura uma data no formato dd/mm/aaaa dentro do título.
def pegar_data(texto):
    """Procura uma data dd/mm/aaaa dentro de um texto."""
    for palavra in str(texto).split():
        palavra = palavra.strip('.,;:()[]')
        if len(palavra) == 10 and palavra[2] == '/' and palavra[5] == '/':
            if palavra[0:2].isdigit() and palavra[3:5].isdigit() and palavra[6:10].isdigit():
                return palavra
    return ''


# Procura o número depois da palavra 'rodada'.
def pegar_rodada(texto):
    """Procura 'rodada 30' dentro de um texto."""
    texto = sem_acento(str(texto)).lower()
    posicao = texto.find('rodada')
    if posicao == -1:
        return 0
    i = posicao + 6
    while i < len(texto) and not texto[i].isdigit():
        i += 1
    numero = ''
    while i < len(texto) and texto[i].isdigit():
        numero += texto[i]
        i += 1
    if numero != '':
        return int(numero)
    return 0


# ---------------------------------------------------------------------------
# leitura da planilha
# ---------------------------------------------------------------------------

# Esta é a função principal da leitura do arquivo Excel.
def ler_planilha(caminho):
    """
    Lê o .xlsx e devolve um dicionário com título, consulta, fonte e a lista
    de times. A planilha pode ter linhas de título antes da tabela, como a
    planilha exemplo da CBF.
    """
    try:
        arquivo_excel = pd.ExcelFile(caminho)
    except Exception as erro:
        raise Exception(f'Não foi possível abrir a planilha: {erro}')

    # Percorre todas as abas; a primeira que tiver Time + Jogos será usada.
    for nome_aba in arquivo_excel.sheet_names:
        tabela = arquivo_excel.parse(nome_aba, header=None)

        # Procura a linha de cabeçalho: precisa ter Time e Jogos juntos.
        linha_cabecalho = -1
        for i in range(len(tabela)):
            valores = tabela.iloc[i].tolist()
            normalizados = [sem_acento(v).lower().strip() for v in valores if not vazio(v)]
            tem_time = False
            tem_jogos = False
            for valor in normalizados:
                if 'time' in valor:
                    tem_time = True
                if 'jogos' in valor:
                    tem_jogos = True
            if tem_time and tem_jogos:
                linha_cabecalho = i
                break

        if linha_cabecalho == -1:
            continue  # esta aba não é a classificação; tenta a próxima

        # O cabeçalho pode estar em qualquer coluna; por isso guardamos os índices.
        # Descobre em qual coluna está cada informação.
        colunas = {'time': -1, 'jogos': -1, 'vitorias': -1, 'empates': -1, 'derrotas': -1}
        cabecalho = tabela.iloc[linha_cabecalho].tolist()
        for i, valor in enumerate(cabecalho):
            nome = sem_acento(valor).lower().strip()
            if 'time' in nome and colunas['time'] == -1:
                colunas['time'] = i
            if 'jogos' in nome and colunas['jogos'] == -1:
                colunas['jogos'] = i
            if 'vitoria' in nome and colunas['vitorias'] == -1:
                colunas['vitorias'] = i
            if 'empate' in nome and colunas['empates'] == -1:
                colunas['empates'] = i
            if 'derrota' in nome and colunas['derrotas'] == -1:
                colunas['derrotas'] = i

        if colunas['time'] == -1:
            continue

        # Textos de apoio (título, data da consulta, fonte).
        textos_acima = []
        for i in range(linha_cabecalho):
            for valor in tabela.iloc[i].tolist():
                if not vazio(valor):
                    textos_acima.append(str(valor).strip())

        titulo = textos_acima[0] if len(textos_acima) > 0 else 'CAMPEONATO BRASILEIRO'
        consulta = ''
        for texto in textos_acima[1:]:
            if 'dados' in sem_acento(texto).lower() or pegar_data(texto) != '':
                consulta = texto
                break

        # Cada linha da tabela vira um dicionário dentro da lista 'times'.
        # Lê as linhas de dados até a primeira linha sem nome de time.
        times = []
        indice_cor = 0
        linhas_vazias = 0
        for i in range(linha_cabecalho + 1, len(tabela)):
            linha = tabela.iloc[i].tolist()
            nome_time = linha[colunas['time']] if colunas['time'] < len(linha) else None
            if vazio(nome_time):
                linhas_vazias += 1
                if linhas_vazias >= 3:
                    break
                continue
            linhas_vazias = 0
            nome_time = str(nome_time).strip()
            if 'fonte' in sem_acento(nome_time).lower():
                break
            if 'observacao' in sem_acento(nome_time).lower():
                break

            jogos = numero(linha[colunas['jogos']]) if colunas['jogos'] != -1 else 0
            vitorias = numero(linha[colunas['vitorias']]) if colunas['vitorias'] != -1 else 0
            empates = numero(linha[colunas['empates']]) if colunas['empates'] != -1 else 0
            derrotas = numero(linha[colunas['derrotas']]) if colunas['derrotas'] != -1 else 0
            if jogos == 0:
                jogos = vitorias + empates + derrotas

            cor, cor_secundaria, apelido = clubes.identidade(nome_time, indice_cor)
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

        # Fonte (linha depois da tabela, se existir).
        fonte = ''
        for i in range(linha_cabecalho + 1, len(tabela)):
            valores = [str(v).strip() for v in tabela.iloc[i].tolist() if not vazio(v)]
            if len(valores) > 0 and 'fonte' in sem_acento(valores[0]).lower():
                if len(valores) > 1:
                    fonte = valores[1]
                break

        dados = {
            'arquivo': caminho,
            'aba': nome_aba,
            'titulo': titulo,
            'consulta': consulta,
            'data_consulta': pegar_data(consulta) or pegar_data(titulo),
            'rodada': pegar_rodada(consulta),
            'fonte': fonte,
            'times': times,
        }
        return classificar(dados)

    raise Exception('A planilha não tem uma tabela de classificação com as colunas Time e Jogos.')


# ---------------------------------------------------------------------------
# cálculos
# ---------------------------------------------------------------------------

# Calcula pontos, ordena e atribui a mesma posição para pontos iguais.
def classificar(dados):
    """Calcula pontos, ordena e define a posição de cada time (com empates)."""
    times = dados['times']

    for time in times:
        time['pontos'] = time['vitorias'] * 3 + time['empates'] * 1
        if time['jogos'] > 0:
            time['aproveitamento'] = time['pontos'] / (time['jogos'] * 3) * 100
        else:
            time['aproveitamento'] = 0
        time['pontos_perdidos'] = time['jogos'] * 3 - time['pontos']

    # Ordena por pontos. Dentro do empate, usamos vitórias e nome apenas para
    # a ORDEM DE EXIBIÇÃO; a posição continua sendo a mesma para os empatados.
    ordenados = sorted(
        times,
        key=lambda t: (-t['pontos'], -t['vitorias'], -t['empates'], sem_acento(t['time'])),
    )

    contagem_pontos = {}
    for time in ordenados:
        contagem_pontos[time['pontos']] = contagem_pontos.get(time['pontos'], 0) + 1

    for i, time in enumerate(ordenados):
        if i > 0 and time['pontos'] == ordenados[i - 1]['pontos']:
            time['posicao'] = ordenados[i - 1]['posicao']
        else:
            time['posicao'] = i + 1
        time['indice'] = i
        time['g4'] = i < 4
        time['z4'] = i >= len(ordenados) - 4
        time['empatado'] = contagem_pontos[time['pontos']] > 1
        time['posicao_texto'] = f"{time['posicao']}º" + ('=' if time['empatado'] else '')

    dados['times'] = ordenados
    dados['total_times'] = len(ordenados)
    dados['total_jogos_time'] = sum(t['jogos'] for t in ordenados)
    dados['total_partidas'] = dados['total_jogos_time'] // 2
    dados['total_pontos'] = sum(t['pontos'] for t in ordenados)
    dados['total_vitorias'] = sum(t['vitorias'] for t in ordenados)
    dados['total_empates'] = sum(t['empates'] for t in ordenados)
    dados['total_derrotas'] = sum(t['derrotas'] for t in ordenados)
    dados['total_pontos_perdidos'] = sum(t['pontos_perdidos'] for t in ordenados)

    if dados['total_partidas'] > 0:
        dados['media_pontos'] = dados['total_pontos'] / dados['total_partidas']
    else:
        dados['media_pontos'] = 0

    dados['lider'] = ordenados[0]
    dados['lanterna'] = ordenados[-1]
    dados['g4'] = ordenados[:4]
    dados['z4'] = ordenados[-4:]
    dados['mais_vitorias'] = max(ordenados, key=lambda t: (t['vitorias'], t['pontos']))
    dados['mais_empates'] = max(ordenados, key=lambda t: (t['empates'], t['pontos']))
    dados['menos_derrotas'] = min(ordenados, key=lambda t: (t['derrotas'], -t['pontos']))
    dados['mais_derrotas'] = max(ordenados, key=lambda t: (t['derrotas'], -t['pontos']))
    dados['maior_aproveitamento'] = max(ordenados, key=lambda t: t['aproveitamento'])
    dados['jogos_max'] = max(t['jogos'] for t in ordenados)
    dados['times_menos_jogos'] = [
        t for t in ordenados if t['jogos'] < dados['jogos_max']
    ]
    dados['empates_na_pontuacao'] = []
    for i in range(1, len(ordenados)):
        if ordenados[i]['pontos'] == ordenados[i - 1]['pontos']:
            if len(dados['empates_na_pontuacao']) == 0 or \
               dados['empates_na_pontuacao'][-1] != ordenados[i]['pontos']:
                dados['empates_na_pontuacao'].append(ordenados[i]['pontos'])

    return dados


# Mostra a tabela no terminal, sem gerar arquivo.
def imprimir_classificacao(dados):
    """Mostra a classificação no terminal (útil antes de gerar os PDFs)."""
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
    print(f"Líder: {dados['lider']['time']} ({dados['lider']['pontos']} pts)   "
          f"Lanterna: {dados['lanterna']['time']} ({dados['lanterna']['pontos']} pts)")
    print()


# Formata 1 como 1º, 2 como 2º etc. para exibir no terminal.
def texto_ordinal(posicao):
    """1 -> '1º'."""
    return f'{posicao}º'
