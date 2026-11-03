# -*- coding: utf-8 -*-
"""
VERSÃO COMENTADA — clubes.py
Guarda as cores e apelidos dos clubes. Se um time não estiver no dicionário,
a função identidade() escolhe uma cor da paleta reserva. Nenhuma biblioteca
externa é usada neste arquivo.
"""

# nome do time (como vem na planilha) -> (cor principal, cor secundária, apelido curto)
# Dicionário: nome do time -> (cor principal, cor secundária, apelido).
CORES_TIMES = {
    'Athletico-PR':      ('#C4122E', '#111111', 'Athletico-PR'),
    'Atlético-MG':       ('#1A1A1A', '#FFFFFF', 'Atlético-MG'),
    'Bahia':             ('#0057A0', '#E30613', 'Bahia'),
    'Botafogo':          ('#1A1A1A', '#FFFFFF', 'Botafogo'),
    'Chapecoense':       ('#006B3F', '#FFFFFF', 'Chapecoense'),
    'Corinthians':       ('#1A1A1A', '#FFFFFF', 'Corinthians'),
    'Coritiba':          ('#005C3B', '#FFFFFF', 'Coritiba'),
    'Cruzeiro':          ('#003DA5', '#FFFFFF', 'Cruzeiro'),
    'Flamengo':          ('#C8102E', '#111111', 'Flamengo'),
    'Fluminense':        ('#7A0C2E', '#00613F', 'Fluminense'),
    'Grêmio':            ('#0D80BF', '#111111', 'Grêmio'),
    'Internacional':     ('#C8102E', '#FFFFFF', 'Internacional'),
    'Mirassol':          ('#B58900', '#006B3F', 'Mirassol'),
    'Palmeiras':         ('#006437', '#FFFFFF', 'Palmeiras'),
    'Red Bull Bragantino': ('#D71920', '#FFFFFF', 'Bragantino'),
    'Remo':              ('#002776', '#FFFFFF', 'Remo'),
    'Santos':            ('#1A1A1A', '#E8E8E8', 'Santos'),
    'São Paulo':         ('#C8102E', '#111111', 'São Paulo'),
    'Vasco':             ('#1A1A1A', '#FFFFFF', 'Vasco'),
    'Vitória':           ('#C8102E', '#111111', 'Vitória'),
}

# Cores usadas quando o time não está no dicionário acima.
# Se o professor usar outro time, ele recebe uma destas cores.
PALETA_RESERVA = [
    '#1D4ED8', '#0E7C7B', '#B45309', '#7E22CE', '#BE123C',
    '#0F766E', '#4338CA', '#A16207', '#9D174D', '#155E75',
    '#4D7C0F', '#7C2D12', '#1E3A8A', '#86198F', '#065F46',
    '#92400E', '#312E81', '#9F1239', '#115E59', '#3F3F46',
]


# Devolve as três informações visuais de um clube.
def identidade(nome, indice=0):
    """Devolve (cor principal, cor secundária, apelido) de um clube."""
    if nome in CORES_TIMES:
        return CORES_TIMES[nome]
    cor = PALETA_RESERVA[indice % len(PALETA_RESERVA)]
    return (cor, '#FFFFFF', nome)


# Calcula o brilho da cor para escolher texto preto ou branco.
def cor_de_fundo(cor):
    """Escolhe preto ou branco para o texto ficar legível sobre a cor."""
    cor = cor.lstrip('#')
    r = int(cor[0:2], 16) / 255
    g = int(cor[2:4], 16) / 255
    b = int(cor[4:6], 16) / 255
    brilho = 0.2126 * r + 0.7152 * g + 0.0722 * b
    if brilho > 0.62:
        return '#111111'
    return '#FFFFFF'
