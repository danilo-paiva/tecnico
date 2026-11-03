# -*- coding: utf-8 -*-
# VERSÃO COMENTADA PARA ESTUDO — o código principal é o da pasta anterior.
"""
Identidade visual dos 20 clubes da Série A.

As cores são usadas apenas para deixar os relatórios com "cara de futebol".
Se o professor trocar a planilha por outra com clubes diferentes, o programa
continua funcionando: clubes desconhecidos recebem uma cor da PALETA_RESERVA.
"""

# nome do time (como vem na planilha) -> (cor principal, cor secundária, apelido curto)
# DICIONÁRIO: nome do time -> (cor principal, cor secundária, apelido).
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
# Cores de reserva para times desconhecidos.
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


