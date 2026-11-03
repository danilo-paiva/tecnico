import pandas as pd
from reportlab.pdfgen import canvas

CAMINHO = "planilha/projeto4bi.xlsx"

df = pd.read_excel(
    CAMINHO,
    sheet_name="Brasileirão 2026",
    header=2
)

times = df.iloc[1:21, 0:5].copy()
vitorias = pd.to_numeric(times.iloc[:, 2], errors="coerce").fillna(0)
empates = pd.to_numeric(times.iloc[:, 3], errors="coerce").fillna(0)
times["Calculo de Pontos"] = vitorias * 3 + empates

nomes = []
lista_pontos = []

for c in range(len(times)):
    nomes.append(times.iloc[c, 0])
    lista_pontos.append(times.iloc[c, 5])

for c in range(len(lista_pontos)):
    for i in range(c + 1, len(lista_pontos)):
        if (lista_pontos[c] < lista_pontos[i] or
                (lista_pontos[c] == lista_pontos[i] and nomes[c].lower() > nomes[i].lower())):
            aux = lista_pontos[c]
            lista_pontos[c] = lista_pontos[i]
            lista_pontos[i] = aux

            aux2 = nomes[c]
            nomes[c] = nomes[i]
            nomes[i] = aux2

colocacao = []
for c in range(len(lista_pontos)):
    if c == 0:
        colocacao.append(1)
    elif lista_pontos[c] == lista_pontos[c - 1]:
        colocacao.append(colocacao[c - 1])
    else:
        colocacao.append(c + 1)


def gerarpdf(nomes, lista_pontos, colocacao, times):
    try:
        pdf = canvas.Canvas("arquivocampeonato.pdf")
        pdf.setFillColor("black")
        pdf.setTitle("Campeonato Brasileirão 2026")
        pdf.setFont("Helvetica-Bold", 18)
        pdf.setFont("Helvetica", 11)
        pdf.drawString(30, 765, "Classificação do Campeonato Brasileirão 2026")

        pdf.setFont("Helvetica-Bold", 10)
        pdf.drawString(30, 740, "Pos.")
        pdf.drawString(75, 740, "Time")
        pdf.drawString(250, 740, "Jogos")
        pdf.drawString(310, 740, "Vitórias")
        pdf.drawString(380, 740, "Empates")
        pdf.drawString(460, 740, "Derrotas")
        pdf.drawString(525, 740, "Pontos")

        y = 720
        for i in range(len(nomes)):
            for j in range(len(times)):
                if nomes[i] == times.iloc[j, 0]:
                    pdf.drawString(30, y, str(colocacao[i]))
                    pdf.drawString(75, y, str(nomes[i]))
                    pdf.drawString(250, y, str(times.iloc[j, 1]))
                    pdf.drawString(310, y, str(times.iloc[j, 2]))
                    pdf.drawString(380, y, str(times.iloc[j, 3]))
                    pdf.drawString(460, y, str(times.iloc[j, 4]))
                    pdf.drawString(525, y, str(lista_pontos[i]))
            y = y - 20

        pdf.save()
        print("Arquivo arquivocampeonato.pdf gerado com sucesso.")
    except Exception as erro:
        print(f"Erro ao gerar o PDF: {erro}")


gerarpdf(nomes, lista_pontos, colocacao, times)
