"""Acesso ao MongoDB — cada operação do sistema é uma função daqui.

Separar o banco (este arquivo) da tela (main.py) tem duas vantagens
práticas: as funções podem ser reaproveitadas por outra tela (web, por
exemplo) e dá para ler o arquivo e ver exatamente QUAIS consultas o
projeto faz.

Coleções do projeto (sufixo p07 = projeto 07, padrão da disciplina):

    turmas_p07  { codigo }
    alunos_p07  { matricula, nome, turma }
    notas_p07   { matricula, bimestre, valor }

Repare que as notas ficam em OUTRA coleção: é isso que dá sentido ao
estágio $lookup no relatório — o "JOIN" do MongoDB.
"""

from pymongo.errors import DuplicateKeyError

from conexao import banco
from regras import ErroDeNegocio, exigir_texto, normalizar_codigo

# As três coleções do projeto, resolvidas uma única vez.
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


def listar_alunos(codigo_turma=None) -> list[dict]:
    """Alunos em ordem alfabética; sem argumento, devolve todas as turmas."""
    filtro = {} if codigo_turma is None else {'turma': normalizar_codigo(codigo_turma)}
    return list(ALUNOS.find(filtro, {'_id': 0}).sort('nome', 1))


def lancar_nota(matricula, bimestre, valor) -> bool:
    """Grava a nota de um bimestre.

    Devolve True se a nota é nova e False se uma nota existente foi
    atualizada (é o que o relatório mostra para o usuário).

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
        #    'notas': 1 mantém o array que o $lookup criou — é com ele que o
        #    main.py mostra as notas por bimestre e calcula a ponderada.
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
    # poder contar os alunos e calcular a média da turma no main.py.
    return list(ALUNOS.aggregate(pipeline))
