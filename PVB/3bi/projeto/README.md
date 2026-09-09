# Projeto do 3º Bimestre — PVB

Calculadora de Trajetória Interplanetária (AeroSpaceX) — WPF (.NET 5, C#).
Calcula Δv, tempo de viagem e combustível de missões usando a Transferência de Hohmann
e a Equação de Tsiolkovsky, conforme o enunciado (`Projeto_PVB_3Bim_26.md`).

## Como rodar

Abrir `projeto.sln` no Visual Studio 2019 e apertar **F5**.

## Como usar

Digitar os 4 valores **um por linha, com vírgula decimal** e clicar em "Calcular Trajetória":

```
149,6   <- r1: distancia do corpo de origem ao Sol (milhoes de km)
227,9   <- r2: distancia do corpo de destino ao Sol (milhoes de km)
5000    <- massa da nave (kg)
310     <- impulso especifico do motor Isp (s)
```

> Com ponto (`149.6`) o Windows pt-BR interpreta como 1496.

## Cálculos (μ = 1.327 x 10¹¹ km³/s²)

1. `a = (r1 + r2) / 2`
2. `T = π · √(a³ / μ)` → dias (T / 86400) e meses (dias / 30)
3. `Δv1 = √(μ/r1)·(√(2·r2/(r1+r2)) − 1)`; `Δv2 = √(μ/r2)·(1 − √(2·r1/(r1+r2)))`; `Δv = |Δv1| + |Δv2|`
4. `combustível = m · (e^(Δv·1000 / (Isp·9,81)) − 1)`

## Resultados (Terra → Marte)

```
Tempo de viagem: 258 dias (~8,6 meses)
Δv total: 5,591 km/s
Razão de massa: 6,2872
Combustível necessário: 26.436,07 kg (26,44 t)
```


## Testes (cenários do enunciado)

Missão principal — Terra → Marte:

```
149,6 / 227,9 / 5000 / 310
Tempo de viagem: 259 dias (~8,6 meses)
Δv total: 5,591 km/s
Razão de massa: 6,2872
Combustível necessário: 26.436,07 kg (26,44 t)
```

Cenários de teste:

| Missão | Entrada (r1 / r2 / m / Isp) | Tempo | Δv total | Combustível |
| --- | --- | --- | --- | --- |
| Terra → Vênus | 149,6 / 108,2 / 3200 / 320 | 146 dias (~4,9 meses) | 5,203 km/s | 13.588,35 kg (13,59 t) |
| Terra → Júpiter | 149,6 / 778,5 / 1200 / 350 | 998 dias (~33,3 meses) | 14,436 km/s | 79.176,72 kg (79,18 t) |
| Terra → Plutão | 149,6 / 5906,4 / 500 / 3000 | 16632 dias (~554,4 meses) | 15,499 km/s | 346,63 kg (0,35 t) |
| Vênus → Marte | 108,2 / 227,9 / 2500 / 310 | 217 dias (~7,2 meses) | 10,530 km/s | 77.249,83 kg (77,25 t) |

Os valores "~esperados" do enunciado são aproximados; alguns não batem com as
próprias fórmulas dele (ex.: o combustível de 25.823,28 kg do Terra → Marte).
Este projeto aplica exatamente as fórmulas do enunciado.
