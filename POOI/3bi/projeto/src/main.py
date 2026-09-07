import mysql.connector


VERDE = '\033[92m'    # codigo ANSI: texto verde
VERMELHO = '\033[91m'  # codigo ANSI: texto vermelho
PADRAO = '\033[0m'     # codigo ANSI: reseta para a cor padrao do terminal

mensagem = ''   # guarda a mensagem (sucesso ou erro) para mostrar no menu


def abrebanco():
    try:
        global conexao
        conexao = mysql.connector.Connect(
            host='localhost',
            database='pooi3bi',
            user='root',
            password=''
        )

        if conexao.is_connected():
            print('Banco de dados ABERTO: Conexao realizada com banco')
            return 1
        else:
            print('Banco de dados FECHADO: NAO OCORREU Conexao com banco')
            return 0

    except Exception as erro:
        print(f'Erro : {erro}')
        return 0


def ler_inteiro(texto):
    # le um numero inteiro; repete ate o usuario digitar certo
    while True:
        try:
            return int(input(texto))
        except ValueError:
            print('ERRO: Digite um numero inteiro!')


# ====== TURMAS ======
def cadastrarTurma():
    global mensagem
    turma = ler_inteiro("Digite o numero da turma:\n> ")
    curso = input('Digite o nome do curso:\n> ').upper()

    try:
        comandosql = conexao.cursor()
        comandosql.execute(f'call sp_cadastrar_turma({turma}, "{curso}") ;')
        conexao.commit()
        mensagem = f'{VERDE}Turma cadastrada com sucesso!{PADRAO}'
    except Exception as erro:
        mensagem = f'{VERMELHO}Erro : {erro}{PADRAO}'
    finally:
        comandosql.close()


def alterarTurma():
    global mensagem
    turma = ler_inteiro("Digite o numero da turma a alterar:\n> ")
    curso = input('Digite o NOVO nome do curso:\n> ').upper()

    try:
        comandosql = conexao.cursor()
        comandosql.execute(f'call sp_alterar_turma({turma}, "{curso}") ;')
        conexao.commit()
        mensagem = f'{VERDE}Turma alterada com sucesso!{PADRAO}'
    except Exception as erro:
        mensagem = f'{VERMELHO}Erro : {erro}{PADRAO}'
    finally:
        comandosql.close()


def excluirTurma():
    global mensagem
    turma = ler_inteiro("Digite o numero da turma a excluir:\n> ")
    confirmar = input(f'Tem certeza que deseja excluir a turma {turma}? (S/N):\n> ')

    if confirmar.upper() == 'S':
        try:
            comandosql = conexao.cursor()
            comandosql.execute(f'call sp_excluir_turma({turma}) ;')
            conexao.commit()
            mensagem = f'{VERDE}Turma excluida com sucesso!{PADRAO}'
        except Exception as erro:
            mensagem = f'{VERMELHO}Erro : {erro}{PADRAO}'
        finally:
            comandosql.close()
    else:
        mensagem = f'{VERMELHO}Exclusao cancelada.{PADRAO}'


def consultarTurmas():
    global mensagem
    try:
        comandosql = conexao.cursor()
        comandosql.execute('select * from turmas order by id_turma ;')
        resultado = comandosql.fetchall()

        if not resultado:
            print('Nenhuma turma cadastrada.')
        else:
            print('\n----- TURMAS -----')
            for id_turma, curso in resultado:
                print(f'{id_turma} - {curso}')

    except Exception as erro:
        mensagem = f'{VERMELHO}Erro : {erro}{PADRAO}'
    finally:
        comandosql.close()


# ====== ALUNOS ======
def cadastrarAluno():
    global mensagem
    matricula = ler_inteiro("Digite a matricula do aluno:\n> ")
    nome = input('Digite o nome do aluno:\n> ')
    turma = ler_inteiro("Digite o numero da turma:\n> ")

    try:
        comandosql = conexao.cursor()
        comandosql.execute(f'call sp_cadastrar_aluno({matricula}, "{nome}", {turma}) ;')
        conexao.commit()
        mensagem = f'{VERDE}Aluno cadastrado com sucesso!{PADRAO}'
    except Exception as erro:
        mensagem = f'{VERMELHO}Erro : {erro}{PADRAO}'
    finally:
        comandosql.close()


def alterarAluno():
    global mensagem
    matricula = ler_inteiro("Digite a matricula do aluno a alterar:\n> ")
    nome = input('Digite o NOVO nome do aluno:\n> ')
    turma = ler_inteiro("Digite a NOVA turma:\n> ")

    try:
        comandosql = conexao.cursor()
        comandosql.execute(f'call sp_alterar_aluno({matricula}, "{nome}", {turma}) ;')
        conexao.commit()
        mensagem = f'{VERDE}Aluno alterado com sucesso!{PADRAO}'
    except Exception as erro:
        mensagem = f'{VERMELHO}Erro : {erro}{PADRAO}'
    finally:
        comandosql.close()


def excluirAluno():
    global mensagem
    matricula = ler_inteiro("Digite a matricula do aluno a excluir:\n> ")
    confirmar = input(f'Tem certeza que deseja excluir a matricula {matricula}? (S/N):\n> ')

    if confirmar.upper() == 'S':
        try:
            comandosql = conexao.cursor()
            comandosql.execute(f'call sp_excluir_aluno({matricula}) ;')
            conexao.commit()
            mensagem = f'{VERDE}Aluno excluido com sucesso!{PADRAO}'
        except Exception as erro:
            mensagem = f'{VERMELHO}Erro : {erro}{PADRAO}'
        finally:
            comandosql.close()
    else:
        mensagem = f'{VERMELHO}Exclusao cancelada.{PADRAO}'


def consultarAlunos():
    global mensagem
    try:
        comandosql = conexao.cursor()
        comandosql.execute('''
            select a.matricula, a.nome, a.id_turma, t.curso
            from alunos a
            inner join turmas t on a.id_turma = t.id_turma
            order by a.matricula ;
        ''')
        resultado = comandosql.fetchall()

        if not resultado:
            print('Nenhum aluno cadastrado.')
        else:
            print('\n----- ALUNOS -----')
            for matricula, nome, id_turma, curso in resultado:
                print(f'{matricula} - {nome} | Turma {id_turma} ({curso})')

    except Exception as erro:
        mensagem = f'{VERMELHO}Erro : {erro}{PADRAO}'
    finally:
        comandosql.close()


# ====== CONSULTAS AVANCADAS ======
def consultarTurmaDoAluno():
    global mensagem
    # dada a matricula de um aluno, mostra em qual turma ele esta
    matricula = ler_inteiro("Digite a matricula do aluno:\n> ")

    try:
        comandosql = conexao.cursor()
        comandosql.execute(f'''
            select a.matricula, a.nome, t.id_turma, t.curso
            from alunos a
            inner join turmas t on a.id_turma = t.id_turma
            where a.matricula = {matricula} ;
        ''')
        aluno = comandosql.fetchone()

        if aluno is None:
            print('Aluno nao encontrado.')
        else:
            mat, nome, id_turma, curso = aluno
            print(f'\n{mat} - {nome} esta na Turma {id_turma} ({curso})')

    except Exception as erro:
        mensagem = f'{VERMELHO}Erro : {erro}{PADRAO}'
    finally:
        comandosql.close()


def consultarAlunosDaTurma():
    global mensagem
    # dado o id de uma turma, mostra todos os alunos dela
    turma = ler_inteiro("Digite o numero da turma:\n> ")

    try:
        comandosql = conexao.cursor()
        comandosql.execute(f'''
            select a.matricula, a.nome
            from alunos a
            inner join turmas t on a.id_turma = t.id_turma
            where t.id_turma = {turma}
            order by a.nome ;
        ''')
        resultado = comandosql.fetchall()

        if not resultado:
            print('Nenhum aluno nessa turma (ou turma inexistente).')
        else:
            print(f'\n----- ALUNOS DA TURMA {turma} -----')
            for matricula, nome in resultado:
                print(f'{matricula} - {nome}')

    except Exception as erro:
        mensagem = f'{VERMELHO}Erro : {erro}{PADRAO}'
    finally:
        comandosql.close()


# ====== MENU ======
if abrebanco() == 1:
    while True:
        print('''
===== SISTEMA TURMAS E ALUNOS =====
1  - Cadastrar turma
2  - Alterar turma
3  - Excluir turma
4  - Consultar turmas
5  - Cadastrar aluno
6  - Alterar aluno
7  - Excluir aluno
8  - Consultar alunos
9  - Consultar turma de um aluno
10 - Consultar alunos de uma turma
0  - Sair
''')

        if mensagem != '':
            print(f'>>> {mensagem}')
            mensagem = ''      # limpa a mensagem depois de mostrar

        opcao = ler_inteiro('Escolha uma opcao:\n> ')

        if opcao == 1:
            cadastrarTurma()
        elif opcao == 2:
            alterarTurma()
        elif opcao == 3:
            excluirTurma()
        elif opcao == 4:
            consultarTurmas()
        elif opcao == 5:
            cadastrarAluno()
        elif opcao == 6:
            alterarAluno()
        elif opcao == 7:
            excluirAluno()
        elif opcao == 8:
            consultarAlunos()
        elif opcao == 9:
            consultarTurmaDoAluno()
        elif opcao == 10:
            consultarAlunosDaTurma()
        elif opcao == 0:
            print('Encerrando o sistema...')
            break
        else:
            mensagem = f'{VERMELHO}Opcao invalida!{PADRAO}'

    conexao.close()
    print('Banco de dados FECHADO.')
else:
    print('FIM DO PROGRAMA! Algum problema na conexao com banco de dados.')
