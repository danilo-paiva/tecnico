"""Sistema de turmas e boletins (projeto 07 — MongoDB + PyMongo).

Este arquivo tem as duas partes que falam do MongoDB:
  1. as consultas (índices, cadastros, lançamento de nota e o boletim);
  2. o menu de terminal que usa essas consultas.

As regras puras (validação, médias, situação) ficam em regras.py e a
conexão com o Atlas/servidor local fica em conexao.py.

Executar a partir desta pasta:
    python main.py
"""

import csv
from pathlib import Path

from pymongo.errors import DuplicateKeyError

from conexao import banco
from regras import (ErroDeNegocio, agrupar_por_situacao, converter_nota,
                    exigir_texto, formatar_media, media_ponderada,
                    normalizar_codigo, situacao, validar_bimestre)

# ===========================================================================
# 1) MongoDB — coleções, índices e consultas
# ===========================================================================

# Coleções do projeto (sufixo p07 = projeto 07, padrão da disciplina):
#
#     turmas_p07  { codigo }
#     alunos_p07  { matricula, nome, turma }
#     notas_p07   { matricula, bimestre, valor }
#
# As notas ficam em OUTRA coleção: é isso que dá sentido ao estágio
# $lookup no relatório — o "JOIN" do MongoDB.
TURMAS = banco['turmas_p07']
ALUNOS = banco['alunos_p07']
NOTAS = banco['notas_p07']


def preparar_indices() -> None:
    """Garante os índices do projeto (create_index pode ser repetido).

    * codigo único  -> duas turmas não podem ter o mesmo código;
    * matricula única -> matrícula identifica um único aluno;
    * (matricula, bimestre) composto e único -> uma nota por aluno por
      bimestre. É esse índice que faz o upsert do lançamento de nota
      atualizar em vez de duplicar.
    """
    TURMAS.create_index('codigo', unique=True)
    ALUNOS.create_index('matricula', unique=True)
    NOTAS.create_index([('matricula', 1), ('bimestre', 1)], unique=True)


def turma_existe(codigo: str) -> bool:
    """Consulta barata: projeta só o _id, que já basta para saber se existe."""
    return TURMAS.find_one({'codigo': codigo}, {'_id': 1}) is not None


def listar_turmas() -> list[str]:
    """Códigos das turmas em ordem alfabética.

    O segundo argumento do find() é a PROJEÇÃO: {'_id': 0} tira o _id do
    resultado e {'codigo': 1} mantém apenas o campo código.
    """
    cursor = TURMAS.find({}, {'_id': 0, 'codigo': 1}).sort('codigo', 1)
    return [turma['codigo'] for turma in cursor]


def criar_turma(codigo) -> str:
    """Cadastra uma turma e devolve o código já normalizado."""
    codigo = normalizar_codigo(codigo)
    try:
        TURMAS.insert_one({'codigo': codigo})
    except DuplicateKeyError:
        # Quem barrou foi o índice único, não o Python: convertemos o erro
        # do driver em uma mensagem que o usuário entende.
        raise ErroDeNegocio(f'A turma {codigo} já existe.') from None
    return codigo


def criar_aluno(matricula, nome, codigo_turma) -> dict:
    """Cadastra um aluno em uma turma existente e devolve o documento."""
    matricula = exigir_texto(matricula, 'matrícula')
    nome = exigir_texto(nome, 'nome do aluno')
    codigo_turma = normalizar_codigo(codigo_turma)

    # Integridade "manual": o MongoDB não tem chave estrangeira, então o
    # código confere se a turma existe antes de gravar o vínculo.
    if not turma_existe(codigo_turma):
        raise ErroDeNegocio(
            f'A turma {codigo_turma} não existe. Cadastre a turma antes do aluno.')

    aluno = {'matricula': matricula, 'nome': nome, 'turma': codigo_turma}
    try:
        ALUNOS.insert_one(aluno)
    except DuplicateKeyError:
        raise ErroDeNegocio(f'A matrícula {matricula} já está cadastrada.') from None
    return aluno


def lancar_nota(matricula, bimestre, valor) -> bool:
    """Grava a nota de um bimestre.

    Devolve True se a nota é nova e False se uma nota existente foi
    atualizada (é o que a tela mostra para o usuário).

    O upsert=True diz: "se nenhum documento casar com o filtro, crie um".
    Como (matricula, bimestre) é único, lançar a mesma nota duas vezes
    atualiza o valor em vez de criar um documento duplicado.
    """
    matricula = exigir_texto(matricula, 'matrícula')
    if ALUNOS.find_one({'matricula': matricula}, {'_id': 1}) is None:
        raise ErroDeNegocio(
            f'Aluno {matricula} não encontrado. Cadastre o aluno antes da nota.')

    resultado = NOTAS.update_one(
        {'matricula': matricula, 'bimestre': bimestre},  # filtro (usa o índice composto)
        {'$set': {'valor': valor}},                      # o que muda
        upsert=True,                                     # cria se não existir
    )
    # upserted_id só vem preenchido quando o documento foi criado agora.
    return resultado.upserted_id is not None


def boletim(codigo_turma) -> list[dict]:
    """Relatório da turma: junta alunos + notas e calcula a média NO banco.

    Recebe o código da turma e devolve uma lista de documentos:
        {'matricula': '2026001', 'nome': 'Ana Silva', 'media': 7.75,
         'notas': [{'bimestre': 1, 'valor': 8.0}, ...]}
    """
    codigo = normalizar_codigo(codigo_turma)
    if not turma_existe(codigo):
        raise ErroDeNegocio(f'A turma {codigo} não existe.')

    pipeline = [
        # 1) $match: reduz o conjunto para os alunos da turma pedida.
        {'$match': {'turma': codigo}},

        # 2) $lookup: para cada aluno, busca em notas_p07 os documentos em
        #    que foreignField == localField, e guarda o array em 'notas'.
        #    É o equivalente ao JOIN do SQL, só que o resultado é uma lista
        #    dentro do próprio documento (modelo documental).
        {'$lookup': {
            'from': 'notas_p07',          # coleção de onde trazer
            'localField': 'matricula',    # campo no documento do aluno
            'foreignField': 'matricula',  # campo correspondente na nota
            'as': 'notas',                # nome do campo que recebe o array
        }},

        # 3) $project: monta a "forma" final do relatório.
        #    $avg recebe a lista de valores e devolve a média; se a lista
        #    estiver vazia o resultado é null (por isso 'sem notas').
        #    'notas': 1 mantém o array que o $lookup criou — é com ele que
        #    mostramos as notas por bimestre e calculamos a ponderada.
        {'$project': {
            '_id': 0,
            'matricula': 1,
            'nome': 1,
            'media': {'$avg': '$notas.valor'},
            'notas': 1,
        }},

        # 4) $sort: ordem alfabética do relatório.
        {'$sort': {'nome': 1}},
    ]

    # aggregate() devolve um cursor, então materializamos em lista para
    # poder contar os alunos e calcular a média da turma na tela.
    return list(ALUNOS.aggregate(pipeline))


# ===========================================================================
# 2) Tela — relatório (texto e CSV) e menu
# ===========================================================================

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


# --- Uma função por opção do menu: o main() fica curto ----------------------

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
