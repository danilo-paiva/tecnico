# -*- coding: utf-8 -*-

import dados
import pagina_web
import relatorios

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
        pagina_web.gerar_pagina(tabela, ARQUIVO_HTML)
        print(f'Página web criada: {ARQUIVO_HTML}')
    except Exception as erro:
        print(f'Erro ao gerar a página: {erro}')


def gerar_pdf(tabela):
    print('Gerando o relatório em PDF...')
    try:
        arquivos = relatorios.gerar_todos(tabela)
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
        tabela = dados.ler_planilha(caminho)
    except Exception as erro:
        print(f'Erro ao ler a planilha: {erro}')
        return

    if tabela == {}:
        return

    print(f'\nPlanilha lida: {tabela["arquivo"]}')
    print(f'Aba: {tabela["aba"]}  ·  times encontrados: {tabela["total_times"]}')
    dados.imprimir_classificacao(tabela)

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
            dados.imprimir_classificacao(tabela)
        else:
            print('Opção inválida. Digite 0, 1, 2, 3 ou 4.')


modo_interativo()
