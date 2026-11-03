# -*- coding: utf-8 -*-
"""
Leitura da planilha da CBF e cálculo da classificação.

Conteúdo usado: pandas/openpyxl (apostila 40), listas, dicionários, funções,
laços, if/else, try/except e f-strings.
Regra: vitória = 3, empate = 1, derrota = 0.
Empates na pontuação ocupam a mesma posição.
"""

import pandas as pd

import clubes


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
