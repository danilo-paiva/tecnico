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

Os valores "~esperados" do enunciado são aproximados; alguns não batem com as
próprias fórmulas dele (ex.: o combustível de 25.823,28 kg do Terra → Marte).
Este projeto aplica exatamente as fórmulas do enunciado.
