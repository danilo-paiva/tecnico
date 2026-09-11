# Projeto 5 — Recomendador Semântico de Artigos com Atlas Vector Search

Recomendador que encontra artigos **parecidos por significado** (não por palavra-chave).

## Como funciona

1. Cada artigo é convertido em um **vetor de 384 números** (embedding) pelo modelo
   `all-MiniLM-L6-v2` (biblioteca `sentence-transformers`).
2. O vetor é salvo **dentro do próprio documento** do artigo no MongoDB (`campo embedding`).
3. Na busca, o texto digitado também vira vetor e o MongoDB Atlas usa o estágio
   **`$vectorSearch`** com **similaridade de cosseno** para achar os artigos mais
   semanticamente parecidos.

```
sim(u, v) = (u · v) / (|u| · |v|)
```

## Instalação

```bash
pip install -r requirements.txt
cp .env.example .env      # edite o .env e coloque a sua MONGO_URI do Atlas
```

## Uso

```bash
# 1. Insere os 12 artigos de exemplo (com embeddings)
python recomendador.py popular

# 2. Busca semântica via Atlas Vector Search (precisa do índice vetorial)
python recomendador.py buscar "redes neurais"

# 2b. Modo local (funciona sem Atlas/índice — calcula cosseno em Python)
python recomendador.py buscar "docker kubernetes" --local
```

## Criando o índice vetorial no Atlas (obrigatório p/ $vectorSearch)

1. Abra o cluster no **MongoDB Atlas** → aba **Search** → **Create Search Index**.
2. Escolha **Vector Search** e configure:

```json
{
  "fields": [
    {
      "type": "vector",
      "path": "embedding",
      "numDimensions": 384,
      "similarity": "cosine"
    }
  ]
}
```

3. Nomeie o índice como **`idx_vetorial`** (mesmo nome usado no `recomendador.py`).
4. Rode `python recomendador.py popular` **antes** de criar o índice.
