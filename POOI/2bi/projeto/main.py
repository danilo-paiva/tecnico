# Inicialização das variáveis
opcao = ''
nomes = []
pontos =[]
times =[]

print("+-----------------------------------+")
print("          Cadastro de Times")
print("+-----------------------------------+")

# 1. Cadastro de Times
while(opcao != 'N'):
    nome = input(f"Digite o nome do {len(nomes) + 1}° time:\n")
    while nome == "":
        print("Nome não pode ser vazio")
        nome = input(f"Digite o nome do {len(nomes) + 1}° time novamente:\n")
    nomes.append(nome)
    
    opcao = input("Deseja cadastrar mais um time? (s/N) \n").upper()
    while opcao not in ("S", "N"):
        print("Opção Inválida!")
        opcao = input("Deseja cadastrar mais um time? (s/N) \n").upper()

# 2. Coleta de pontos e estruturação
for time in range(len(nomes)):
    print("+-------------------------------------+")
    print(f"  Pontuação: {nomes[time]}")
    print("+-------------------------------------+")
    ponto_partida =[]
    total = 0
    
    for partida in range(5):
        ponto = int(input(f"Digite a pontuação da {partida + 1}° partida (0, 1 ou 3):\n"))
        while ponto not in (0, 1, 3):
            print("Valor da Pontuação Inválido! (0, 1 ou 3)")
            ponto = int(input(f"Digite a pontuação da {partida + 1}° partida:\n"))
        ponto_partida.append(ponto)
        total += ponto
        
    pontos.append(tuple(ponto_partida))
    times.append({
        "nome": nomes[time],
        "pontos_partidas": pontos[time],
        "total": total
    })

# 3. Processamento de resultados
campeao = {"nome": "", "total": -1}
vice = {"nome": "", "total": -1}
menos_pontos = {"nome": "", "total": 999}
mais_vitorias = {"nome": "", "vitorias": -1}

print("\n" + "="*45)
print("             RESULTADO FINAL")
print("="*45)

for time in times:
    # Contar vitórias manualmente
    vitorias = 0
    for p in time["pontos_partidas"]:
        if p == 3:
            vitorias += 1
    
    # Calcular aproveitamento
    aproveitamento = (time["total"] / 15) * 100
    
    # Exibir dados do time
    print(f"Time: {time['nome']:<10} | Vitórias: {vitorias} | Total: {time['total']} pts | Aproveitamento: {aproveitamento:.1f}%")
    
    # Lógica para Campeão e Vice
    if time["total"] > campeao["total"]:
        vice = campeao
        campeao = time
    elif time["total"] > vice["total"]:
        vice = time
        
    # Lógica para menos pontos
    if time["total"] < menos_pontos["total"]:
        menos_pontos = time
        
    # Lógica para mais vitórias
    if vitorias > mais_vitorias["vitorias"]:
        mais_vitorias = {"nome": time["nome"], "vitorias": vitorias}

# 4. Exibição das estatísticas finais
print("-" * 45)
print(f"Campeão: {campeao['nome']} ({campeao['total']} pontos)")
print(f"Vice-Campeão: {vice['nome']} ({vice['total']} pontos)")
print(f"Time com mais vitórias: {mais_vitorias['nome']} ({mais_vitorias['vitorias']} vitórias)")
print(f"Time com menos pontos: {menos_pontos['nome']} ({menos_pontos['total']} pontos)")
print("=" * 45)