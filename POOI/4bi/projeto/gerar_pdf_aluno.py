import pandas as pd
from reportlab.pdfgen import canvas
from reportlab.lib.colors import HexColor, white

df = pd.read_excel("planilha/projeto4bi.xlsx", sheet_name="Brasileirão 2026", header=2)
times = df.iloc[1:21, 0:5].copy()
times["Calculo de Pontos"] = (
    pd.to_numeric(times.iloc[:, 2], errors="coerce").fillna(0) * 3
    + pd.to_numeric(times.iloc[:, 3], errors="coerce").fillna(0)
)

nomes = []
lista_pontos = []
for c in range(len(times)):
    nomes.append(times.iloc[c, 0])
    lista_pontos.append(times.iloc[c, 5])

for c in range(len(lista_pontos)):
    for i in range(c + 1, len(lista_pontos)):
        trocar = False
        if lista_pontos[c] < lista_pontos[i]:
            trocar = True
        elif lista_pontos[c] == lista_pontos[i] and str(nomes[c]).lower() > str(nomes[i]).lower():
            trocar = True
        if trocar:
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

pdf = canvas.Canvas("arquivocampeonato.pdf")
roxo_escuro = HexColor("#2B1B4D")
roxo_medio = HexColor("#5B3A8E")
dourado = HexColor("#D4A017")
cinza_claro = HexColor("#F2F0F7")
cinza_texto = HexColor("#4A4A4A")
verde_g4 = HexColor("#E5EBDC")
vermelho_z4 = HexColor("#F3DFD8")

pdf.setFillColor(roxo_escuro)
pdf.rect(0, 770, 612, 72, fill=1, stroke=0)
pdf.setFillColor(dourado)
pdf.rect(0, 762, 612, 8, fill=1, stroke=0)
pdf.setFillColor(white)
pdf.setTitle("Campeonato Brasileirão 2026")
pdf.setFont("Helvetica-Bold", 20)
pdf.drawCentredString(306, 805, "BRASILEIRÃO 2026")
pdf.setFont("Helvetica", 11)
pdf.drawCentredString(306, 785, "Classificação do Campeonato")

pdf.setFillColor(roxo_medio)
pdf.rect(25, 725, 562, 25, fill=1, stroke=0)
pdf.setFillColor(white)
pdf.setFont("Helvetica-Bold", 10)
for x, texto in [(35, "Pos."), (80, "Time"), (255, "Jogos"), (315, "Vitórias"), (385, "Empates"), (460, "Derrotas"), (530, "Pontos")]:
    pdf.drawString(x, 733, texto)

y = 705
for i in range(len(nomes)):
    for j in range(len(times)):
        if nomes[i] == times.iloc[j, 0]:
            if i < 4:
                pdf.setFillColor(verde_g4)
            elif i >= len(nomes) - 4:
                pdf.setFillColor(vermelho_z4)
            elif i % 2 == 0:
                pdf.setFillColor(cinza_claro)
            else:
                pdf.setFillColor(white)
            pdf.rect(25, y - 6, 562, 20, fill=1, stroke=0)
            pdf.setFillColor(cinza_texto)
            pdf.setFont("Helvetica", 10)
            pdf.drawString(35, y, str(colocacao[i]))
            pdf.drawString(80, y, str(nomes[i]))
            pdf.drawString(255, y, str(times.iloc[j, 1]))
            pdf.drawString(315, y, str(times.iloc[j, 2]))
            pdf.drawString(385, y, str(times.iloc[j, 3]))
            pdf.drawString(460, y, str(times.iloc[j, 4]))
            pdf.setFillColor(roxo_escuro)
            pdf.setFont("Helvetica-Bold", 10)
            pdf.drawString(530, y, str(lista_pontos[i]))
    y -= 20

pdf.setFillColor(dourado)
pdf.rect(0, 40, 612, 6, fill=1, stroke=0)
pdf.setFillColor(cinza_texto)
pdf.setFont("Helvetica", 9)
pdf.drawCentredString(306, 25, "Relatório gerado automaticamente")
pdf.save()
print("PDF gerado")
