"""Regras de negócio — funções puras, sem MongoDB.

Por que separar? Porque assim a lógica pode ser testada sem subir o servidor: este
arquivo não importa o PyMongo, então dá para rodar as validações no terminal em
milissegundos, mesmo com o banco desligado.

Contrato usado no projeto:
  * entrada inválida -> levanta ErroDeNegocio (com mensagem pronta para a tela)
  * quem chama (main.py) -> captura o erro e só imprime a mensagem
"""

# Constantes do domínio no topo: em vez de "números mágicos" espalhados
# pelo código, a regra fica escrita uma única vez.
BIMESTRES = (1, 2, 3, 4)
NOTA_MAXIMA = 10.0
MEDIA_APROVACAO = 6.0
MEDIA_RECUPERACAO = 4.0

# Rótulos de situação: usados na coluna do boletim e no resumo por grupo.
APROVADO = 'Aprovado'
RECUPERACAO = 'Recuperação'
REPROVADO = 'Reprovado'
SEM_NOTAS = 'sem notas'

# Ordem em que os grupos aparecem no resumo do boletim.
ORDEM_SITUACOES = (APROVADO, RECUPERACAO, REPROVADO, SEM_NOTAS)

# Pesos por bimestre do boletim: o 4º bimestre pesa mais, como na regra da
# escola. Se o critério mudar, muda-se só aqui — as consultas não mudam.
PESOS = {1: 1, 2: 2, 3: 3, 4: 4}


class ErroDeNegocio(Exception):
    """Erro esperado do domínio (nota inválida, turma inexistente, ...).

    Não é um bug do programa: é uma situação prevista que a tela deve
    mostrar ao usuário em linguagem comum.
    """


def exigir_texto(valor, campo: str) -> str:
    """Devolve o texto sem espaços nas pontas; erro se estiver vazio."""
    texto = str(valor or '').strip()
    if not texto:
        raise ErroDeNegocio(f'Preencha o campo "{campo}".')
    return texto


def normalizar_codigo(codigo) -> str:
    """Padroniza o código da turma: ' ads-a ' -> 'ADS-A'.

    Sem isso, 'ads-a' e 'ADS-A' virariam duas turmas diferentes e o
    relatório não encontraria os alunos.
    """
    return exigir_texto(codigo, 'código da turma').upper()


def validar_bimestre(texto) -> int:
    """Converte o bimestre digitado e confere se está entre 1 e 4."""
    try:
        bimestre = int(str(texto).strip())
    except (TypeError, ValueError):
        raise ErroDeNegocio('Bimestre inválido: digite 1, 2, 3 ou 4.') from None
    if bimestre not in BIMESTRES:
        raise ErroDeNegocio('Bimestre fora do intervalo: use 1, 2, 3 ou 4.')
    return bimestre


def converter_nota(texto) -> float:
    """Converte a nota digitada (aceita vírgula) e valida o intervalo 0 a 10.

    '7,5' -> 7.5   |   '7.5' -> 7.5   |   '11' -> erro
    """
    try:
        valor = float(str(texto).replace(',', '.').strip())
    except (TypeError, ValueError):
        raise ErroDeNegocio('Nota inválida: digite um número de 0 a 10.') from None
    if not 0.0 <= valor <= NOTA_MAXIMA:  # também barra nan e infinito
        raise ErroDeNegocio('Nota fora do intervalo: use de 0 a 10.')
    return round(valor, 2)


def formatar_media(media) -> str:
    """Mostra a média com duas casas ou '--' quando o aluno não tem notas.

    O MongoDB devolve None para média de aluno sem nota (o $avg não divide
    por zero: ele simplesmente não calcula), então tratamos esse caso aqui.
    """
    return '--' if media is None else f'{media:.2f}'


def situacao(media) -> str:
    """Classifica o aluno pela média (regra do boletim da escola).

    Fica aqui, e não dentro da consulta, porque é regra da instituição:
    mudar o mínimo de aprovação não deve exigir mexer no pipeline.
    """
    if media is None:
        return SEM_NOTAS
    if media >= MEDIA_APROVACAO:
        return APROVADO
    if media >= MEDIA_RECUPERACAO:
        return RECUPERACAO
    return REPROVADO


def media_ponderada(notas) -> float | None:
    """Desafio 1: média com pesos por bimestre (notas = lista de documentos).

        ponderada = soma(valor * peso) / soma(pesos das notas que existem)

    Aluno sem notas -> None. Quem tem só duas notas é dividido apenas pela
    soma dos pesos dessas duas, então quem ainda não lançou o 4º bimestre
    não é prejudicado.
    """
    if not notas:
        return None
    soma_valores = 0.0
    soma_pesos = 0
    for nota in notas:
        peso = PESOS.get(nota['bimestre'], 1)  # bimestre fora da tabela vale 1
        soma_valores += nota['valor'] * peso
        soma_pesos += peso
    if soma_pesos == 0:
        return None
    return round(soma_valores / soma_pesos, 2)


def agrupar_por_situacao(linhas) -> dict:
    """Desafio 2: separa as linhas do relatório em {situação: [nomes]}.

    Cada linha precisa ter 'nome' e 'media_ponderada'. A situação de cada
    aluno é decidida por situacao(), a mesma regra usada na coluna da tela.
    """
    grupos = {}
    for linha in linhas:
        nome_do_grupo = situacao(linha['media_ponderada'])
        grupos.setdefault(nome_do_grupo, []).append(linha['nome'])
    # devolve na ordem oficial (Aprovado, Recuperação, ...), não na ordem
    # em que os alunos apareceram: o resumo fica estável a cada consulta
    return {nome: grupos[nome] for nome in ORDEM_SITUACOES if nome in grupos}
