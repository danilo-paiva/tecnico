
print('='*50)
print(' '*8, end ='')
print('SISTEMA DE ANÁLISE DE DESEMPENHO')
print('='*50)

quantidadeAlunos = int(input('Digite a quantidade de alunos: '))
while quantidadeAlunos <=0:
    quantidadeAlunos = int(input('Número igual de alunos ou menor que zero \n'
                                 'Digite novamente a quantidade de alunos: '))
print()

quantidadeDisciplinas = int(input('Digite a quantidade de disciplinas: '))
while quantidadeDisciplinas <=0:
    quantidadeDisciplinas = int(input('Número igual de disciplinas ou menor que zero \n'
                                      'Digite novamente a quantidade de disciplinas: '))
print()

somaMedias = 0
menorMedia = 10
maiorMedia = 0

primeiroMedia = 0
ultimoMedia = 0
mediasIndividuais = 0.0

for aluno in range(0,quantidadeAlunos):

    # besterol so pra ficar bonitinho
    print('-'*50)
    print(' '*17, end ='')
    print(f'Notas Aluno {aluno+1}')
    print('-' * 50)

    somaNotas = 0
    for disciplina in range(0,quantidadeDisciplinas):

        nota = int(input(f'Digite a nota final da disciplina {disciplina+1}: '))

        while nota<0 or nota>10:
            nota = int(input(f'Nota não pode ser negativa ou maior que 10 \nDigite novamente a nota final da disciplina {disciplina+1}: '))
        somaNotas += nota

    mediaIndividual = somaNotas/quantidadeDisciplinas
    somaMedias += mediaIndividual
    
    if mediaIndividual > maiorMedia:
        maiorMedia = mediaIndividual
        
    if mediaIndividual < menorMedia:
        menorMedia = mediaIndividual

    if aluno == 0:
        primeiroMedia = mediaIndividual
    if aluno == quantidadeAlunos-1:
        ultimoMedia = mediaIndividual

    print()

mediaTurma = somaMedias/quantidadeAlunos
desempenhoTurma = ""
if mediaTurma < 5 or menorMedia < 3:
    desempenhoTurma = "insuficiente"
elif ultimoMedia > primeiroMedia and mediaTurma>=7:
    desempenhoTurma = "consistente e positivo"
elif ultimoMedia < primeiroMedia and mediaTurma<5:
    desempenhoTurma = "em queda"
elif ultimoMedia == primeiroMedia:
    desempenhoTurma = "equilibrado"
else:
    desempenhoTurma = "irregular"

print()
print('-'*50)
print(' ' * 8, end='')
print('RESULTADOS E ESTATÍSTICAS DA TURMA')
print('-'*50)

print(f'Media Turma: {mediaTurma:.1f} \n'
      f'Maior media individual: {maiorMedia:.2f} \n'
      f'Menor media individual: {menorMedia:.2f} \n'
      f'Diagnostico da turma: desempenho {desempenhoTurma}')
