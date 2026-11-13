"""Conexão com o MongoDB — o único arquivo que sabe COMO conectar.

Todo o resto do projeto importa o objeto `banco` daqui:

    from conexao import banco
    banco['turmas_p07'].find_one(...)

A string de conexão fica no arquivo .env (que NÃO vai para o GitHub,
veja o .gitignore), então o mesmo código funciona em servidor local
e no Atlas:

    local:  MONGO_URI=mongodb://localhost:27017/
    Atlas:  MONGO_URI=mongodb+srv://usuario:senha@cluster.mongodb.net/

Teste rápido da conexão (sem abrir o menu):
    python conexao.py
"""

import os
from pathlib import Path

from dotenv import load_dotenv
from pymongo import MongoClient
from pymongo.errors import (ConfigurationError, OperationFailure,
                            ServerSelectionTimeoutError)

# with_name('.env') aponta para o .env que está na MESMA pasta deste
# arquivo, então funciona mesmo que você execute de outra pasta.
load_dotenv(Path(__file__).with_name('.env'))

URI = os.getenv('MONGO_URI', 'mongodb://localhost:27017/')

# serverSelectionTimeoutMS=5000: espera no máximo 5 segundos pelo servidor.
# Sem esse parâmetro o PyMongo pode ficar ~30s tentando conectar antes de
# reclamar — em sala de aula, 5 segundos já é tempo suficiente para avisar.
cliente = MongoClient(URI, serverSelectionTimeoutMS=5000)


def uri_sem_senha(uri: str) -> str:
    """Esconde usuário e senha antes de mostrar a URI em uma mensagem.

    Importante quando o projeto usa Atlas: nunca imprimir a senha na tela
    (nem em print, nem em print de erro, nem em commit).
    """
    if '@' not in uri:
        return uri
    protocolo = uri.split('://')[0]
    return f"{protocolo}://***:***@{uri.split('@')[-1]}"


try:
    # 'ping' é o teste mais leve que existe: confirma que o servidor
    # respondeu (e, no Atlas, que usuário e senha foram aceitos).
    cliente.admin.command('ping')
except ServerSelectionTimeoutError:
    # Falha de REDE: servidor parado, URI errada, DNS ou IP bloqueado.
    raise SystemExit(
        '\n[erro] Não consegui falar com o MongoDB.\n'
        '  1) O MongoDB Community Server está instalado e iniciado?\n'
        '     Windows: Win+R -> services.msc -> serviço "MongoDB Server" -> Iniciar\n'
        '  2) Existe um arquivo .env nesta pasta com a linha\n'
        '     MONGO_URI=mongodb+srv://usuario:senha@cluster.mongodb.net/ ?\n'
        '  3) Atlas: o seu IP está liberado em Network Access?\n'
        f'  URI em uso: {uri_sem_senha(URI)}\n'
    ) from None
except OperationFailure as erro:
    # Falha de AUTENTICAÇÃO: chegamos ao servidor, mas usuário/senha
    # não conferem. No Atlas, "bad auth" quase sempre é senha errada.
    raise SystemExit(
        '\n[erro] O servidor respondeu, mas recusou o usuário/senha.\n'
        f'  Detalhe do MongoDB: {erro}'
        '\n  Atlas: Database Access -> usuário -> Edit -> Edit Password.\n'
        '  Se a senha tiver caracteres especiais (@ : / ? # % &), codifique-os\n'
        '  na URI (ex.: @ vira %40) — ou use uma senha só com letras e números.\n'
        '  Confira também se o usuário existe nesse cluster.\n'
        f'  URI em uso: {uri_sem_senha(URI)}\n'
    ) from None
except ConfigurationError:
    # URI malformada (faltou letra, sobrou espaço, esquema errado...).
    raise SystemExit(
        '\n[erro] A MONGO_URI do arquivo .env está malformada.\n'
        '  Copie a string em Atlas -> Connect -> Drivers -> Python.\n'
        f'  URI em uso: {uri_sem_senha(URI)}\n'
    ) from None

# Mesmo banco didático dos 10 projetos da disciplina; as coleções são
# separadas por projeto com o sufixo p07 (turmas_p07, alunos_p07, notas_p07).
banco = cliente['laboratorio_python']


if __name__ == '__main__':
    print(f'Conectado a: {banco.name}')
    print(f'URI: {uri_sem_senha(URI)}')
