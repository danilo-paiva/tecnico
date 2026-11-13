"""Menu de terminal do sistema de turmas e boletins (projeto 07).

Este arquivo só cuida de ENTRADA e SAÍDA: mostra o menu, pergunta os
dados e imprime (ou exporta) o resultado. As regras ficam em regras.py
e todas as consultas ao MongoDB ficam em repositorio.py.

Executar a partir desta pasta:
    python main.py
"""

import csv
from pathlib import Path

from regras import (ErroDeNegocio, agrupar_por_situacao, converter_nota,
                    formatar_media, media_ponderada, normalizar_codigo,
                    situacao, validar_bimestre)
from repositorio import (boletim, criar_aluno, criar_turma, lancar_nota,
                         listar_turmas, preparar_indices)

MENU = """
[1] Cadastrar turma
[2] Cadastrar aluno
[3] Lançar / atualizar nota
[4] Boletim da turma
[5] Salvar boletim em CSV
[0] Sair"""


def linha(tamanho: int = 78) -> None:
    """Separador visual — deixa o relatório legível no terminal."""
    print('-' * tamanho)


def escolher_turma(rotulo: str = 'Código da turma') -> str:
    """Mostra as turmas existentes antes de pedir o código.

    Evita a situação clássica de errar o código e receber "turma inexistente".
    """
    turmas = listar_turmas()
    if turmas:
        print(f"Turmas cadastradas: {', '.join(turmas)}")
    return input(f'{rotulo}: ')


# ---------------------------------------------------------------------------
# Relatório
# ---------------------------------------------------------------------------

def montar_relatorio(codigo_turma: str) -> list[dict]:
    """Transforma os documentos do banco nas linhas do relatório.

    É a fonte única do boletim: a tela ([4]) e o CSV ([5]) usam esta função,
    então o arquivo exportado nunca fica diferente do que foi mostrado.

    De onde vem cada média:
      * 'media'           -> $avg calculado pelo MongoDB (média simples);
      * 'media_ponderada' -> regra da escola, calculada em Python.
    """
    linhas = []
    for aluno in boletim(codigo_turma):
        # ordena por bimestre só para exibir os valores na ordem certa
        notas = sorted(aluno['notas'], key=lambda nota: nota['bimestre'])
        media = aluno['media']          # $avg do pipeline
        ponderada = media_ponderada(notas)
        linhas.append({
            'matricula': aluno['matricula'],
            'nome': aluno['nome'],
            'notas': notas,
            'media': media,
            'media_ponderada': ponderada,
            # a situação usa a média ponderada, que é a média oficial
            'situacao': situacao(ponderada),
        })
    return linhas


def texto_das_notas(notas, com_bimestre: bool = False) -> str:
    """Monta o texto das notas.

    Na tela:   '8.0 7.5'          (o cabeçalho já diz que a ordem é B1..B4)
    No CSV:    'B1 8.0; B2 7.5'   (o arquivo precisa ser autoexplicativo)
    """
    if com_bimestre:
        return '; '.join(f"B{n['bimestre']} {n['valor']:.1f}" for n in notas)
    return ' '.join(f"{nota['valor']:.1f}" for nota in notas)


def imprimir_boletim(codigo_turma: str) -> None:
    """Desafios 1 e 2: média ponderada e resumo por situação."""
    linhas = montar_relatorio(codigo_turma)
    codigo = normalizar_codigo(codigo_turma)

    linha()
    print(f'BOLETIM DA TURMA {codigo}')
    linha()
    if not linhas:
        print('Nenhum aluno cadastrado nesta turma.')
        return

    # Cabeçalho com larguras fixas (< = alinhado à esquerda, > = à direita).
    print(f"{'MATRÍCULA':<11}{'ALUNO':<18}{'NOTAS B1..B4':<20}"
          f"{'MÉDIA':>6}{'POND.':>7}   SITUAÇÃO")
    for item in linhas:
        print(f"{item['matricula']:<11}{item['nome'][:17]:<18}"
              f"{texto_das_notas(item['notas'])[:19]:<20}"
              f"{formatar_media(item['media']):>6}"
              f"{formatar_media(item['media_ponderada']):>7}   {item['situacao']}")

    # Desafio 2: quem está em cada situação.
    linha()
    for grupo, nomes in agrupar_por_situacao(linhas).items():
        print(f'{grupo}: {", ".join(nomes)}')

    # Média da turma pela ponderada, contando só quem tem nota.
    ponderadas = [i['media_ponderada'] for i in linhas if i['media_ponderada'] is not None]
    media_turma = sum(ponderadas) / len(ponderadas) if ponderadas else None
    print(f'\nAlunos: {len(linhas)} | com nota: {len(ponderadas)} | '
          f'média ponderada da turma: {formatar_media(media_turma)}')


def exportar_csv(codigo_turma: str) -> None:
    """Desafio 3: grava o boletim em CSV para abrir no Excel.

    Dois detalhes que fazem diferença no Windows:
      * encoding utf-8-sig -> o Excel mostra "ç" e "ã" corretamente;
      * delimitador ';'    -> é o separador de colunas do Excel em pt-BR.
    """
    linhas = montar_relatorio(codigo_turma)
    if not linhas:
        print('Nada para exportar: a turma não tem alunos.')
        return

    codigo = normalizar_codigo(codigo_turma)
    destino = Path(__file__).with_name(f'boletim_{codigo}.csv')
    with destino.open('w', newline='', encoding='utf-8-sig') as arquivo:
        escritor = csv.writer(arquivo, delimiter=';')
        escritor.writerow(['matricula', 'nome', 'notas',
                           'media', 'media_ponderada', 'situacao'])
        for item in linhas:
            escritor.writerow([
                item['matricula'],
                item['nome'],
                texto_das_notas(item['notas'], com_bimestre=True),
                formatar_media(item['media']),
                formatar_media(item['media_ponderada']),
                item['situacao'],
            ])
    print(f'CSV gerado: {destino.name} ({len(linhas)} alunos)')


# ---------------------------------------------------------------------------
# Uma função por opção do menu. Assim o main() fica curto e cada ação pode
# ser lida (e testada) separadamente.
# ---------------------------------------------------------------------------

def opcao_criar_turma() -> None:
    codigo = criar_turma(input('Código da turma (ex.: ADS-A): '))
    print(f'Turma {codigo} criada.')


def opcao_criar_aluno() -> None:
    matricula = input('Matrícula: ')
    nome = input('Nome: ')
    codigo = escolher_turma()
    aluno = criar_aluno(matricula, nome, codigo)
    print(f"Aluno {aluno['nome']} cadastrado na turma {aluno['turma']}.")


def opcao_lancar_nota() -> None:
    matricula = input('Matrícula do aluno: ')
    # Validamos antes de ir ao banco: nota inválida nem chega no MongoDB.
    bimestre = validar_bimestre(input('Bimestre (1-4): '))
    valor = converter_nota(input('Nota (0-10, pode usar vírgula): '))
    nota_nova = lancar_nota(matricula, bimestre, valor)
    acao = 'registrada' if nota_nova else 'atualizada'
    print(f'Nota {valor:.2f} do {bimestre}º bimestre {acao}.')


def opcao_boletim() -> None:
    imprimir_boletim(escolher_turma())


def opcao_csv() -> None:
    exportar_csv(escolher_turma())


def main() -> None:
    """Lê a opção, executa a ação e volta ao menu até o usuário sair."""
    preparar_indices()  # idempotente: garante os índices a cada execução

    while True:
        print(MENU)
        opcao = input('Opção: ').strip()
        try:
            if opcao == '1':
                opcao_criar_turma()
            elif opcao == '2':
                opcao_criar_aluno()
            elif opcao == '3':
                opcao_lancar_nota()
            elif opcao == '4':
                opcao_boletim()
            elif opcao == '5':
                opcao_csv()
            elif opcao == '0':
                print('Até logo!')
                break
            else:
                print('Opção inválida: use um número de 0 a 5.')
        except ErroDeNegocio as erro:
            # Erro PREVISTO (nota inválida, turma inexistente, duplicado):
            # avisa e volta ao menu sem derrubar o programa.
            print(f'[!] {erro}')
        except KeyboardInterrupt:
            # Ctrl+C a qualquer momento encerra de forma limpa.
            print('\nEncerrado pelo usuário.')
            break


if __name__ == '__main__':
    main()
