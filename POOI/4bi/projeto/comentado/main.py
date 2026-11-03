# -*- coding: utf-8 -*-
"""
VERSÃO COMENTADA — Projeto POOI 4º bimestre
Classificação do Campeonato Brasileiro Série A 2026

Esta versão faz o mesmo que o main.py, mas explica cada etapa.
Os arquivos são gravados na própria pasta do programa, como nos exemplos
da apostila (sem criar ou manipular pastas).

Para executar, dentro desta pasta:
    python main.py
"""

# Importa as funções dos outros arquivos do projeto.
import dados
import pagina_web
import relatorios

# Caminho da planilha de exemplo. É um caminho relativo: a pasta "planilha"
# está dentro da pasta do programa.
PLANILHA_EXEMPLO = '../planilha/projeto4bi.xlsx'

# Nome do arquivo HTML que será criado na pasta do programa.
ARQUIVO_HTML = 'classificacao.html'


def escolher_planilha():
    """Pergunta o caminho da planilha.

    Enter sem digitar nada usa a planilha de exemplo.
    strip() remove espaços e replace() remove aspas de caminhos arrastados.
    """
    caminho = input(f'Planilha (.xlsx) [Enter = {PLANILHA_EXEMPLO}]: ').strip()
    caminho = caminho.replace('"', '')
    if caminho == '':
        caminho = PLANILHA_EXEMPLO
    return caminho


def gerar_pagina_web(tabela):
    """Gera o arquivo classificacao.html na pasta do programa.

    A função gerar_pagina() está em pagina_web.py. Ela recebe os dados
    calculados e o nome do arquivo HTML que será criado.
    """
    try:
        pagina_web.gerar_pagina(tabela, ARQUIVO_HTML)
        print(f'Página web criada: {ARQUIVO_HTML}')
    except Exception as erro:
        print(f'Erro ao gerar a página: {erro}')


def gerar_pdf(tabela):
    """Gera o relatório 07 em PDF na pasta do programa.

    gerar_todos() está em relatorios.py e devolve a lista de arquivos
    criados. O reportlab grava o PDF com o método save().
    """
    print('Gerando o relatório em PDF...')
    try:
        arquivos = relatorios.gerar_todos(tabela)
        print(f'{len(arquivos)} relatório(s) gerado(s).')
    except Exception as erro:
        print(f'Erro ao gerar o PDF: {erro}')


def mostrar_menu():
    """Mostra as opções do programa."""
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
    """Lê a planilha, mostra a classificação e abre o menu."""

    # 1) Pede o caminho da planilha.
    caminho = escolher_planilha()

    # 2) Lê o Excel e calcula a classificação.
    # O try/except mostra uma mensagem amigável se o arquivo estiver errado.
    try:
        tabela = dados.ler_planilha(caminho)
    except Exception as erro:
        print(f'Erro ao ler a planilha: {erro}')
        return

    if tabela == {}:
        return

    # 3) Mostra um resumo e a tabela no terminal.
    print(f'\nPlanilha lida: {tabela["arquivo"]}')
    print(f'Aba: {tabela["aba"]}  ·  times encontrados: {tabela["total_times"]}')
    dados.imprimir_classificacao(tabela)

    # 4) Repete o menu até o usuário escolher 0.
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
            # Gera o PDF primeiro; o HTML só cria o link para ele.
            gerar_pdf(tabela)
            gerar_pagina_web(tabela)

        elif opcao == '4':
            dados.imprimir_classificacao(tabela)

        else:
            print('Opção inválida. Digite 0, 1, 2, 3 ou 4.')


modo_interativo()
