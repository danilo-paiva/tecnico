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

# Importa o pandas, usado para abrir o arquivo .xlsx.
import pandas as pd
# Importa o canvas do reportlab, usado para desenhar o PDF.
from reportlab.pdfgen import canvas


# ===========================================================================
# 1) LEITURA DA PLANILHA E CÁLCULO
# ===========================================================================

# FUNÇÃO AUXILIAR: verifica se uma célula está vazia.
def vazio(valor):
    """True quando a célula está vazia."""
    # Converte o valor para texto e remove espaços das pontas.
    texto = str(valor).strip()
    # String vazia conta como célula vazia.
    if texto == '':
        return True
    # O pandas representa células vazias como NaN.
    if texto.upper() == 'NAN':
        return True
    if texto.upper() == 'NONE':
        return True
    return False


# FUNÇÃO AUXILIAR: converte a célula em número inteiro.
def numero(valor):
    """Converte a célula em inteiro, aceitando 30, 30.0, '30' e '30,0'."""
    # Se a célula está vazia, o número é zero.
    if vazio(valor):
        return 0
    # Aceita 30, 30.0, "30" e "30,0".
    texto = str(valor).strip().replace(' ', '').replace(',', '.')
    # Tenta converter; se não for número, devolve zero.
    try:
        return int(float(texto))
    except Exception:
        return 0


# FUNÇÃO PRINCIPAL DA LEITURA: percorre as abas do Excel.
def ler_planilha(caminho):
    """Lê o .xlsx e devolve os dados da classificação."""
    # Tenta abrir o arquivo e avisa o usuário se houver erro.
    try:
        # ExcelFile permite escolher a aba antes de ler.
        arquivo_excel = pd.ExcelFile(caminho)
    except Exception as erro:
        print(f'Não foi possível abrir a planilha: {erro}')
        return {}

    # Percorre todas as abas até encontrar a tabela.
    for nome_aba in arquivo_excel.sheet_names:
        # header=None mantém as linhas de título acima da tabela.
        tabela = arquivo_excel.parse(nome_aba, header=None)

        # -1 significa que o cabeçalho ainda não foi encontrado.
        linha_cabecalho = -1
        # Percorre as linhas procurando as palavras Time e Jogos.
        for i in range(len(tabela)):
            # Guarda os valores da linha atual.
            valores = []
            for j in range(len(tabela.columns)):
                valores.append(tabela.iloc[i, j])

            # Guarda os textos em maiúsculas para comparar.
            normalizados = []
            for valor in valores:
                if not vazio(valor):
                    normalizados.append(str(valor).upper().strip())

            # Marcadores para saber se a linha tem as cinco colunas da tabela.
            tem_time = False
            tem_jogos = False
            tem_vit = False
            tem_emp = False
            tem_der = False
            # Confere cada célula da linha.
            for valor in normalizados:
                if 'TIME' in valor:
                    tem_time = True
                if 'JOGOS' in valor:
                    tem_jogos = True
                if 'VIT' in valor:
                    tem_vit = True
                if 'EMP' in valor:
                    tem_emp = True
                if 'DER' in valor:
                    tem_der = True

            # Só aceita a linha como cabeçalho se tiver as cinco colunas.
            if tem_time and tem_jogos and tem_vit and tem_emp and tem_der:
                linha_cabecalho = i
                break

        # Se a aba não tem a tabela, tenta a próxima.
        if linha_cabecalho == -1:
            continue

        # Descobre em qual coluna está cada informação.
        colunas = {'time': -1, 'jogos': -1, 'vitorias': -1, 'empates': -1, 'derrotas': -1}
        # Percorre as colunas do cabeçalho.
        for i in range(len(tabela.columns)):
            valor = tabela.iloc[linha_cabecalho, i]
            nome = str(valor).upper().strip()
            # Compara por trecho para aceitar Time, TIME ou Nome do Time.
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

        # Sem a coluna Time não é possível montar a classificação.
        if colunas['time'] == -1:
            continue
        # As colunas Vitórias, Empates e Derrotas são obrigatórias.
        if colunas['vitorias'] == -1 or colunas['empates'] == -1 or colunas['derrotas'] == -1:
            print('A planilha precisa das colunas Vitórias, Empates e Derrotas.')
            continue

        # Cada linha de time será guardada como um dicionário.
        times = []
        # Conta linhas vazias para saber quando a tabela terminou.
        linhas_vazias = 0
        # Começa na primeira linha depois do cabeçalho.
        for i in range(linha_cabecalho + 1, len(tabela)):
            # Lê somente a coluna do nome do time.
            nome_time = tabela.iloc[i, colunas['time']]

            # Linha sem nome de time é ignorada.
            if vazio(nome_time):
                linhas_vazias += 1
                # Três linhas vazias seguidas indicam o fim da tabela.
                if linhas_vazias >= 3:
                    break
                continue
            linhas_vazias = 0

            # Remove espaços do nome antes de guardar.
            nome_time = str(nome_time).strip()

            # Lê somente a coluna Jogos.
            valor_jogos = tabela.iloc[i, colunas['jogos']]
            # Se Jogos estiver vazio, a tabela terminou.
            if vazio(valor_jogos):
                break
            # Converte o número de jogos para inteiro.
            jogos = numero(valor_jogos)

            # Lê somente Vitórias, Empates e Derrotas.
            vitorias = numero(tabela.iloc[i, colunas['vitorias']])
            empates = numero(tabela.iloc[i, colunas['empates']])
            derrotas = numero(tabela.iloc[i, colunas['derrotas']])

            # Cria o dicionário do time com os cinco dados lidos.
            times.append({
                'time': nome_time,
                'jogos': jogos,
                'vitorias': vitorias,
                'empates': empates,
                'derrotas': derrotas,
            })

        # Se a aba não produziu nenhum time, tenta outra.
        if len(times) == 0:
            continue

        # Entrega a lista para a função que calcula a classificação.
        dados = {'times': times}
        return classificar(dados)

    # Mensagem final quando nenhuma aba tem a tabela esperada.
    print('A planilha não tem uma tabela com as colunas Time e Jogos.')
    return {}


# FUNÇÃO DE CÁLCULO: pontos, ordem e posições.
def classificar(dados):
    """Calcula pontos, ordena e define a posição de cada time."""
    times = dados['times']

    # Percorre todos os times para calcular os pontos.
    for time in times:
        # Vitória vale 3 e empate vale 1; derrota não soma.
        time['pontos'] = time['vitorias'] * 3 + time['empates']
        # Calcula o aproveitamento em porcentagem.
        if time['jogos'] > 0:
            time['aproveitamento'] = time['pontos'] / (time['jogos'] * 3) * 100
        else:
            time['aproveitamento'] = 0

    # A lista será montada já na ordem correta.
    # Ordena por pontos (maior primeiro) e, no empate, pelo nome.
    tabela_ordenada = pd.DataFrame(times)
    tabela_ordenada = tabela_ordenada.sort_values(
        by=['pontos', 'time'],
        ascending=[False, True]
    )
    ordenados = tabela_ordenada.to_dict('records')



    # Depois da ordem, define a posição de cada time.
    for i in range(len(ordenados)):
        time = ordenados[i]
        # Mesma pontuação do anterior: repete a posição.
        if i > 0 and time['pontos'] == ordenados[i - 1]['pontos']:
            time['posicao'] = ordenados[i - 1]['posicao']
        else:
            time['posicao'] = i + 1
        time['indice'] = i
        # G4 tem prioridade; com menos de 8 times, o Z4 não invade o G4.
        time['g4'] = i < 4
        time['z4'] = i >= len(ordenados) - 4 and i >= 4

    # Descobre quais times estão empatados.
    for i in range(len(ordenados)):
        time = ordenados[i]
        # Começa considerando que o time não está empatado.
        empatado = False
        if i > 0 and time['pontos'] == ordenados[i - 1]['pontos']:
            empatado = True
        if i < len(ordenados) - 1 and time['pontos'] == ordenados[i + 1]['pontos']:
            empatado = True
        time['empatado'] = empatado

        # Acrescenta = ao texto da posição quando há empate.
        if empatado:
            time['posicao_texto'] = str(time['posicao']) + 'º='
        else:
            time['posicao_texto'] = str(time['posicao']) + 'º'

    # Acumula jogos e pontos para os metadados.
    total_jogos = 0
    total_pontos = 0
    # Soma os valores de todos os times.
    for time in ordenados:
        total_jogos += time['jogos']
        total_pontos += time['pontos']

    # Guarda a lista ordenada no lugar da lista original.
    dados['times'] = ordenados
    dados['total_times'] = len(ordenados)
    dados['total_partidas'] = total_jogos // 2
    dados['total_pontos'] = total_pontos

    # Evita divisão por zero no cálculo da média.
    if dados['total_partidas'] > 0:
        dados['media_pontos'] = total_pontos / dados['total_partidas']
    else:
        dados['media_pontos'] = 0

    # Guarda o primeiro e o último para o resumo do terminal.
    dados['lider'] = ordenados[0]
    dados['lanterna'] = ordenados[-1]

    # Descobre o maior número de jogos.
    jogos_max = 0
    for time in ordenados:
        if time['jogos'] > jogos_max:
            jogos_max = time['jogos']
    dados['jogos_max'] = jogos_max

    # Lista os times que jogaram menos partidas.
    times_menos_jogos = []
    for time in ordenados:
        if time['jogos'] < jogos_max:
            times_menos_jogos.append(time)
    dados['times_menos_jogos'] = times_menos_jogos

    return dados


# FUNÇÃO DE APOIO: mostra a tabela no terminal.
def imprimir_classificacao(dados):
    """Mostra a classificação no terminal."""
    print()
    print()
    print('POS  TIME                   J    V    E    D   PTS   APROV.')
    print('-' * 66)
    # Uma linha de texto para cada time.
    for time in dados['times']:
        # Marca G para o G4 e Z para o Z4.
        marca = ' '
        if time['g4']:
            marca = 'G'
        if time['z4']:
            marca = 'Z'
        # Mostra posição, time, jogos, vitórias, empates, derrotas e pontos.
        print(
            f"{time['posicao']:>3}º {marca} "
            f"{time['time'][:20]:<20} "
            f"{time['jogos']:>3} {time['vitorias']:>4} {time['empates']:>4} "
            f"{time['derrotas']:>4} {time['pontos']:>5} "
            f"{time['aproveitamento']:>6.1f}%"
        )
    # Fecha a tabela e mostra líder e lanterna.
    print('-' * 66)
    print(f"Líder: {dados['lider']['time']} ({dados['lider']['pontos']} pts)")
    print(f"Lanterna: {dados['lanterna']['time']} ({dados['lanterna']['pontos']} pts)")
    print()



# ===========================================================================
# 2) RELATÓRIO PDF
# ===========================================================================

# ATALHO DO PDF: fonte, cor e texto em uma chamada.
def texto(pdf, x, y, conteudo, tamanho=9, fonte='Helvetica', cor='#111111'):
    """Escreve um texto na posição (x, y)."""
    # Define a fonte e o tamanho do texto.
    pdf.setFont(fonte, tamanho)
    # Define a cor do texto.
    pdf.setFillColor(cor)
    # Desenha o texto na posição (x, y).
    pdf.drawString(x, y, str(conteudo))


# DESENHA UMA LINHA usando sublinhados, sem rect/line.
def linha(pdf, x, y, largura=490, cor='#D9D9D9'):
    """Desenha uma linha usando uma sequência de traços (sem rect/line)."""
    pdf.setFont('Helvetica', 6)
    pdf.setFillColor(cor)
    # Calcula quantos caracteres cabem na largura pedida.
    quantidade = int(largura / 3.34)
    pdf.drawString(x, y, '_' * quantidade)


# Texto único da regra de empate, usado no PDF e no HTML.
NOTA_EMPATES = 'Times com a mesma pontuação ocupam a mesma colocação, sem os critérios oficiais de desempate da CBF.'


# FUNÇÃO QUE DESENHA O PDF INTEIRO.
def layout_07_minimalista(dados, caminho):
    """Desenha o layout 07 inteiro, de cima para baixo."""
    # Cria uma página A4 padrão do reportlab.
    pdf = canvas.Canvas(caminho)
    # Define o título interno do PDF.
    pdf.setTitle('Minimalista Suíço — Série A 2026')

    # Cores usadas no desenho.
    tinta = '#111111'
    cinza = '#8A8A8A'
    verde = '#2F7D4A'
    vermelho = '#B3261E'
    vitoria = '#1F9D55'
    empate = '#E8A33D'
    derrota = '#C0392B'

    # Cabeçalho: competição e ano.
    texto(pdf, 50, 812, 'CAMPEONATO BRASILEIRO  ·  SÉRIE A', 6.5, 'Helvetica-Bold', cinza)
    texto(pdf, 48, 768, 'SÉRIE A', 40, 'Helvetica-Bold', tinta)
    texto(pdf, 48, 726, '2026', 40, 'Helvetica', '#B9B9B9')

    # Subtítulo com o total de partidas.
    texto(pdf, 50, 704, f'Classificação após {dados["total_partidas"]} partidas', 7.5, 'Helvetica', cinza)

    # Metadados: clubes e média.
    metadados = [
        ('CLUBES', str(dados['total_times'])),
        ('MÉDIA', f'{dados["media_pontos"]:.2f}'.replace('.', ',')),
    ]
    # O y começa no alto e diminui a cada metadado.
    y = 800
    for rotulo, valor in metadados:
        texto(pdf, 470, y, rotulo, 5.5, 'Helvetica-Bold', cinza)
        texto(pdf, 470, y - 16, valor, 17, 'Helvetica-Bold', tinta)
        y -= 36

    # Linha horizontal abaixo do cabeçalho.
    linha(pdf, 50, 690, 490, tinta)

    # Cabeçalho da tabela.
    y_header = 670
    texto(pdf, 50, y_header, 'POS', 6, 'Helvetica-Bold', cinza)
    texto(pdf, 84, y_header, 'CLUBE', 6, 'Helvetica-Bold', cinza)
    texto(pdf, 210, y_header, 'DISTRIBUIÇÃO DOS JOGOS', 5.5, 'Helvetica-Bold', cinza)
    texto(pdf, 430, y_header, 'J', 6, 'Helvetica-Bold', cinza)
    texto(pdf, 462, y_header, 'V', 6, 'Helvetica-Bold', cinza)
    texto(pdf, 494, y_header, 'E', 6, 'Helvetica-Bold', cinza)
    texto(pdf, 516, y_header, 'D', 6, 'Helvetica-Bold', cinza)
    texto(pdf, 545, y_header, 'PTS', 6, 'Helvetica-Bold', cinza)

    # Posição da primeira linha da tabela.
    topo = 660
    # Divide o espaço disponível pelo número de times.
    altura = 500 / len(dados['times'])
    # Ajusta o tamanho do nome ao espaço da linha.
    tamanho_nome = 9.2 * altura / 25.2
    if tamanho_nome < 6:
        tamanho_nome = 6
    if tamanho_nome > 9.2:
        tamanho_nome = 9.2
    # Desenha uma linha para cada time.
    for time in dados['times']:
        # Calcula o y do topo e da base da linha.
        y_topo = topo - time['indice'] * altura
        y_base = y_topo - 16.5

        # G4 em verde; Z4 em vermelho; os demais em preto.
        if time['g4']:
            cor_posicao = verde
            pdf.setFillColor('#E5EBDC')
            pdf.rect(48, y_base - 5, 499, 19, fill=1, stroke=0)
        elif time['z4']:
            cor_posicao = vermelho
            pdf.setFillColor('#F3DFD8')
            pdf.rect(48, y_base - 5, 499, 19, fill=1, stroke=0)
        else:
            cor_posicao = tinta

        # Posição e nome do time.
        texto(pdf, 50, y_base, time['posicao_texto'], 9.5, 'Helvetica-Bold', cor_posicao)
        texto(pdf, 84, y_base, time['time'][:22], tamanho_nome, 'Helvetica', tinta)

        # Início da faixa de distribuição dos jogos.
        x = 210
        # Um ponto colorido para cada jogo.
        for i in range(time['jogos']):
            # Primeiros jogos: vitória; depois empate; o resto derrota.
            if i < time['vitorias']:
                cor = vitoria
            elif i < time['vitorias'] + time['empates']:
                cor = empate
            else:
                cor = derrota
            texto(pdf, x, y_base + 1.5, '·', 8, 'Helvetica-Bold', cor)
            x += 180 / dados['jogos_max']

        # Colunas J, V, E, D e pontos.
        texto(pdf, 430, y_base, time['jogos'], 9, 'Helvetica', '#555555')
        texto(pdf, 462, y_base, time['vitorias'], 9, 'Helvetica', '#555555')
        texto(pdf, 494, y_base, time['empates'], 9, 'Helvetica', '#555555')
        texto(pdf, 516, y_base, time['derrotas'], 9, 'Helvetica', '#555555')
        texto(pdf, 545, y_base, time['pontos'], 12, 'Helvetica-Bold', tinta)

    # Linha de fechamento da tabela.
    linha(pdf, 50, 660 - (len(dados['times']) - 1) * altura - 22.5, 490, tinta)

    # Nota sobre empates.
    texto(pdf, 50, 100, NOTA_EMPATES, 6.5, 'Helvetica', cinza)

    # Se houver times com menos jogos, lista os nomes.
    if len(dados['times_menos_jogos']) > 0:
        nomes = []
        for time in dados['times_menos_jogos']:
            nomes.append(time['time'])
        texto(pdf, 50, 90, 'Com menos jogos: ' + ', '.join(nomes) + '.', 6.5, 'Helvetica', cinza)

    # Rodapé com número de página e legenda das zonas.
    texto(pdf, 470, 118, 'Página 1 de 1', 7, 'Helvetica', tinta)
    texto(pdf, 470, 100, 'G4: 4 primeiros  ·  Z4: 4 últimos', 6.5, 'Helvetica', cinza)

    # Legenda dos pontos coloridos.
    texto(pdf, 430, 88, '·', 8, 'Helvetica-Bold', vitoria)
    texto(pdf, 438, 88, 'vitória', 5.5, 'Helvetica', cinza)
    texto(pdf, 473, 88, '·', 8, 'Helvetica-Bold', empate)
    texto(pdf, 481, 88, 'empate', 5.5, 'Helvetica', cinza)
    texto(pdf, 516, 88, '·', 8, 'Helvetica-Bold', derrota)
    texto(pdf, 524, 88, 'derrota', 5.5, 'Helvetica', cinza)
    texto(pdf, 430, 77, 'cada ponto = um jogo', 5.5, 'Helvetica', cinza)

    # Grava o arquivo PDF.
    pdf.save()
    return caminho



# ===========================================================================
# 3) PÁGINA WEB
# ===========================================================================

# MONTA O HTML: uma linha <tr> para cada time.
def montar_linhas(dados):
    """Monta o corpo da tabela, com uma linha por time."""
    # Guarda cada linha HTML.
    linhas = []
    # Percorre os times na ordem da classificação.
    for time in dados['times']:
        # Classes CSS de zona: g4 e z4.
        classes = []
        if time['g4']:
            classes.append('g4')
        if time['z4']:
            classes.append('z4')
        classe = ' '.join(classes)

        # Texto da posição, com = quando há empate.
        posicao = time['posicao_texto']
        if time['empatado']:
            posicao = posicao.replace('=', '<span class="empate">=</span>')

        # Cria as células da linha.
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


# MONTA A PÁGINA: lê o modelo txt e troca os marcadores.
def gerar_pagina(dados, caminho_html):
    """Lê o modelo txt e grava a página com os dados da classificação."""
    # Formata a média com vírgula.
    media = f'{dados["media_pontos"]:.2f}'.replace('.', ',')

    # Abre o arquivo texto que contém o HTML.
    modelo = open('modelo_pagina.txt', 'r')
    html = modelo.read()
    modelo.close()

    # Troca cada marcador pelo valor correspondente.
    html = html.replace('@@PARTIDAS@@', str(dados['total_partidas']))
    html = html.replace('@@CLUBES@@', str(dados['total_times']))
    html = html.replace('@@MEDIA@@', media)
    html = html.replace('@@LINHAS@@', montar_linhas(dados))
    html = html.replace('@@NOTA@@', NOTA_EMPATES)

    # Grava o HTML final na pasta do programa.
    arquivo = open(caminho_html, 'w')
    arquivo.write(html)
    arquivo.close()
    return caminho_html


# ===========================================================================
# 4) MENU E PROGRAMA PRINCIPAL
# ===========================================================================

# Caminhos padrão do projeto.
PLANILHA_EXEMPLO = 'planilha/projeto4bi.xlsx'
ARQUIVO_HTML = 'classificacao.html'
ARQUIVO_PDF = 'relatorio.pdf'


# Pergunta qual planilha será lida.
def escolher_planilha():
    # Enter vazio usa a planilha de exemplo.
    caminho = input(f'Planilha (.xlsx) [Enter = {PLANILHA_EXEMPLO}]: ').strip()
    caminho = caminho.replace('"', '')
    # Sem caminho digitado, usa o exemplo.
    if caminho == '':
        caminho = PLANILHA_EXEMPLO
    return caminho


# Gera o PDF e a página; se o PDF falhar, não cria o HTML.
def gerar_pagina_web(tabela):
    if gerar_pdf(tabela) == False:
        return
    try:
        gerar_pagina(tabela, ARQUIVO_HTML)
        print(f'Página web criada: {ARQUIVO_HTML}')
    except Exception as erro:
        print(f'Erro ao gerar a página: {erro}')


# Gera o PDF e trata erro de gravação.
def gerar_pdf(tabela):
    print('Gerando o relatório em PDF...')
    try:
        layout_07_minimalista(tabela, ARQUIVO_PDF)
        print(f'PDF criado: {ARQUIVO_PDF}')
        return True
    except Exception as erro:
        print(f'Erro ao gerar o PDF: {erro}')
        return False


# Mostra as opções do menu.
def mostrar_menu():
    print()
    print('=' * 64)
    print(' CAMPEONATO BRASILEIRO SÉRIE A 2026 — PROJETO POOI 4º BIMESTRE')
    print('=' * 64)
    print(' 1 - Gerar a página web e o PDF')
    print(' 2 - Mostrar a classificação no terminal')
    print(' 0 - Sair')
    print('=' * 64)


# PROGRAMA PRINCIPAL: lê a planilha e abre o menu.
def modo_interativo():
    # Pergunta o caminho da planilha.
    caminho = escolher_planilha()
    # Tenta ler o arquivo e avisa se houver erro.
    try:
        tabela = ler_planilha(caminho)
    except Exception as erro:
        print(f'Erro ao ler a planilha: {erro}')
        return

    # Se a leitura falhou, encerra sem continuar.
    if tabela == {}:
        return

    # Mostra quantos times foram encontrados.
    print(f'\nTimes encontrados: {tabela["total_times"]}')
    imprimir_classificacao(tabela)

    # Fica no menu até o usuário digitar 0.
    while True:
        mostrar_menu()
        # Lê a opção digitada.
        opcao = input('Escolha uma opção: ').strip()
        # Opção 0 encerra o programa.
        if opcao == '0':
            print('Até a próxima rodada!')
            break
        # Opção 1 gera o PDF e depois a página; o link do HTML nunca fica quebrado.
        if opcao == '1':
            gerar_pagina_web(tabela)
        # Opção 2 mostra a classificação no terminal.
        elif opcao == '2':
            imprimir_classificacao(tabela)
        else:
            print('Opção inválida. Digite 0, 1 ou 2.')


# Inicia o programa quando o arquivo é executado.
modo_interativo()
