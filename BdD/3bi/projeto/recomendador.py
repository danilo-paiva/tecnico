"""
Projeto 5 — Recomendador Semântico de Artigos com Atlas Vector Search
3º Bimestre — Banco de Dados

Como funciona:
  1. Cada artigo é transformado em um VETOR de números (embedding) que
     representa o seu "significado".
  2. O vetor é salvo junto do documento do artigo no MongoDB.
  3. Para recomendar, transformamos a busca do usuário em vetor também
     e usamos o operador $vectorSearch (similaridade de cosseno) para
     achar os artigos mais parecidos.

Uso:
  python recomendador.py popular              # insere os artigos de exemplo
  python recomendador.py buscar "redes neurais"
  python recomendador.py buscar "docker kubernetes" --local
"""

import os
import sys

# Garante acentos corretos no terminal do Windows
if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")

import numpy as np
from pymongo import MongoClient
from pymongo.server_api import ServerApi
from dotenv import load_dotenv
from sentence_transformers import SentenceTransformer

load_dotenv()
MONGO_URI = os.getenv("MONGO_URI", "mongodb://localhost:27017")

DB = "projetos_python"
COLECAO = "artigos"
MODELO = SentenceTransformer("all-MiniLM-L6-v2")  # gera vetores de 384 dimensões


# ---------------------------------------------------------------- conexão
def conectar():
    client = MongoClient(MONGO_URI, server_api=ServerApi("1"))
    client.admin.command("ping")
    print("✅ Conectado ao MongoDB!")
    return client[DB][COLECAO]


# ---------------------------------------------------------------- embeddings
def gerar_embedding(texto: str) -> list[float]:
    """Transforma um texto em um vetor de 384 números."""
    return MODELO.encode(texto).tolist()


# ---------------------------------------------------------------- popular
ARTIGOS = [
    {"titulo": "Introdução às Redes Neurais",
     "texto": "Redes neurais são modelos inspirados no cérebro humano, formados por camadas de neurônios artificiais que aprendem padrões a partir de dados."},
    {"titulo": "Aprendizado Profundo (Deep Learning)",
     "texto": "Deep learning usa redes neurais profundas com muitas camadas para resolver tarefas complexas como visão computacional e Processamento de Linguagem Natural."},
    {"titulo": "LLMs e Modelos de Linguagem",
     "texto": "Grandes modelos de linguagem como GPT são treinados com bilhões de palavras para gerar texto, responder perguntas e conversar naturalmente."},
    {"titulo": "Bancos NoSQL Orientados a Documentos",
     "texto": "Bancos de dados NoSQL como MongoDB armazenam dados em documentos JSON flexíveis, sem esquema fixo, ideais para aplicações modernas."},
    {"titulo": "Índices Geoespaciais 2dsphere no MongoDB",
     "texto": "O MongoDB suporta índices geoespaciais 2dsphere para consultas de proximidade com coordenadas GeoJSON, úteis em apps de delivery e mapas."},
    {"titulo": "Índices TTL para Dados que Expiram",
     "texto": "Índices TTL removem automaticamente documentos antigos do MongoDB, perfeitos para sessões, tokens e links temporários."},
    {"titulo": "Containers Docker para Desenvolvedores",
     "texto": "Docker empacota aplicações em containers leves e portáveis, garantindo que rodem igual em qualquer ambiente de desenvolvimento ou produção."},
    {"titulo": "Orquestração com Kubernetes",
     "texto": "Kubernetes orquestra containers em escala, gerenciando deploys, escalonamento automático e recuperação de falhas em clusters."},
    {"titulo": "APIs REST com FastAPI",
     "texto": "FastAPI é um framework Python moderno para criar APIs REST rápidas, com validação de dados via Pydantic e documentação automática."},
    {"titulo": "Busca Vetorial e Similaridade de Cosseno",
     "texto": "A busca vetorial compara embeddings usando similaridade de cosseno, permitindo encontrar documentos semanticamente parecidos."},
    {"titulo": "Arquiteturas RAG (Retrieval Augmented Generation)",
     "texto": "RAG combina busca vetorial com LLMs: documentos relevantes são recuperados do banco e usados como contexto para gerar respostas precisas."},
    {"titulo": "IoT e Coleções Time Series",
     "texto": "Coleções time series do MongoDB otimizam o armazenamento de telemetria IoT, como leituras de sensores de temperatura e energia."},
]


def popular(colecao):
    colecao.delete_many({})
    docs = []
    for a in ARTIGOS:
        docs.append({**a, "embedding": gerar_embedding(a["titulo"] + " " + a["texto"])})
    colecao.insert_many(docs)
    print(f"✅ {len(docs)} artigos inseridos com seus embeddings.")


# ---------------------------------------------------------------- busca (Atlas)
def buscar_atlas(colecao, consulta: str, limite: int = 3):
    """Usa o estágio $vectorSearch do MongoDB Atlas (requires índice vetorial)."""
    vet = gerar_embedding(consulta)
    pipeline = [
        {
            "$vectorSearch": {
                "index": "idx_vetorial",
                "path": "embedding",
                "queryVector": vet,
                "numCandidates": 50,
                "limit": limite,
            }
        },
        {"$project": {"titulo": 1, "texto": 1, "score": {"$meta": "vectorSearchScore"}}},
    ]
    return list(colecao.aggregate(pipeline))


# ---------------------------------------------------------------- busca (local, sem Atlas)
def buscar_local(colecao, consulta: str, limite: int = 3):
    """Fallback: calcula a similaridade de cosseno em Python."""
    vet_q = np.array(gerar_embedding(consulta))
    resultados = []
    for doc in colecao.find({}, {"titulo": 1, "texto": 1, "embedding": 1}):
        vet_doc = np.array(doc["embedding"])
        sim = float(np.dot(vet_q, vet_doc) / (np.linalg.norm(vet_q) * np.linalg.norm(vet_doc)))
        resultados.append({**doc, "score": round(sim, 4), "_id": doc["_id"]})
    resultados.sort(key=lambda r: r["score"], reverse=True)
    return resultados[:limite]


# ---------------------------------------------------------------- main
if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(__doc__)
        sys.exit(1)

    comando = sys.argv[1]
    colecao = conectar()

    if comando == "popular":
        popular(colecao)

    elif comando == "buscar":
        if len(sys.argv) < 3:
            print("Uso: python recomendador.py buscar \"texto da busca\"")
            sys.exit(1)
        consulta = sys.argv[2]
        usar_local = "--local" in sys.argv
        print(f"\n🔎 Buscando artigos parecidos com: “{consulta}”\n")
        resultados = buscar_local(colecao, consulta) if usar_local else buscar_atlas(colecao, consulta)
        if not resultados:
            print("Nenhum resultado. (No Atlas, rode 'popular' antes e crie o índice vetorial.)")
        for i, r in enumerate(resultados, 1):
            print(f"{i}. [{r.get('score', '?')}] {r['titulo']}")
            print(f"   {r['texto'][:90]}...")

    else:
        print(f"Comando desconhecido: {comando}")
