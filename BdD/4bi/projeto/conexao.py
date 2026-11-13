"""Conexão com o MongoDB — no formato do material de aula.

O material mostra assim:

    import pymongo
    cliente = pymongo.MongoClient("mongodb://localhost:27017/")
    meu_banco = cliente['banco_de_dados']

Aqui é o mesmo código, com duas diferenças:
  * a URL de conexão vem do arquivo .env (Atlas), e não escrita na linha —
    assim a senha do banco não entra no commit;
  * o banco é o 'laboratorio_python', o mesmo dos projetos da disciplina.

Teste de conexão (lista bancos e coleções, como no 4º passo do material):
    python conexao.py
"""

import os
from pathlib import Path

import pymongo
from dotenv import load_dotenv

# Carrega a MONGO_URI do .env que está nesta mesma pasta.
load_dotenv(Path(__file__).with_name('.env'))

# Objeto de conexão — o mesmo pymongo.MongoClient do material de aula.
cliente = pymongo.MongoClient(os.getenv('MONGO_URI', 'mongodb://localhost:27017/'))

# O banco não precisa existir antes: o MongoDB cria no primeiro insert.
meu_banco = cliente['laboratorio_python']


if __name__ == '__main__':
    print(cliente.list_database_names())
    print(meu_banco.list_collection_names())
