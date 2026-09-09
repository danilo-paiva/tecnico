# Projeto do 3º Bimestre — PVB

**Calculadora de Trajetória Interplanetária e Consumo de Combustível — AeroSpaceX**

Aplicação desktop em **WPF (.NET 5, C#)** que calcula o **Δv (delta-v)**, a duração da viagem
e a estimativa de consumo de combustível de missões interplanetárias usando a manobra de
**Transferência de Hohmann** e a **Equação do Foguete de Tsiolkovsky**.

O código foi escrito **estritamente com as técnicas do material de aulas**.

## Estrutura

```
PVB/3bi/projeto/
├── projeto.sln
├── Projeto_PVB_3Bim_26.md   (enunciado)
├── img/
└── projeto/
    ├── projeto.csproj
    ├── App.xaml / App.xaml.cs
    ├── MainWindow.xaml      (interface — Label, TextBox, Button)
    └── MainWindow.xaml.cs   (lógica de cálculo e formatação)
```

## Como executar

```bash
dotnet build projeto/projeto.csproj
dotnet run --project projeto/projeto.csproj
```

Ou abrir `projeto.sln` no Visual Studio e pressionar F5.

## Interface

Somente objetos permitidos no enunciado (caixas de texto multilinha, botões de comando e labels):

- **Entrada** (caixa de texto multilinha, um valor por linha, **vírgula decimal** — ex.: `149,6`):
  1. Distância do corpo de origem ao Sol (r₁, em milhões de km)
  2. Distância do corpo de destino ao Sol (r₂, em milhões de km)
  3. Massa da nave (m, em kg)
  4. Impulso específico do motor (Isp, em segundos)
- **Ações:** botão **"Calcular Trajetória"** e botão **"Limpar Campos"**.
- **Saída** (labels): tempo de viagem (dias e meses), Δv₁, Δv₂, Δv total (km/s),
  razão de massa e combustível necessário (kg e toneladas).

> **Importante:** digitar os valores com **vírgula** decimal (`149,6`). Com ponto
> (`149.6`) o Windows pt-BR interpreta como 1496 — comportamento do `double.TryParse`
> em cultura pt-BR. A interface avisa isso ao usuário.

## Lógica implementada (`MainWindow.xaml.cs`)

Constante gravitacional do Sol: **μ = 1.327 × 10¹¹ km³/s²** (distâncias convertidas de
milhões de km para km).

1. Semi-eixo maior: `a = (r1 + r2) / 2`
2. Tempo de transferência: `T = π · √(a³ / μ)` → dias = T / 86.400; meses = dias / 30
3. Delta-v: `Δv1 = √(μ/r1)·(√(2·r2/(r1+r2)) − 1)`, `Δv2 = √(μ/r2)·(1 − √(2·r1/(r1+r2)))`, `Δvtotal = |Δv1| + |Δv2|`
4. Tsiolkovsky: `m_combustivel = m · (e^(Δv·1000 / (Isp·9,81)) − 1)`

## Correspondência com as aulas

| Técnica no código | Aula |
| --- | --- |
| TextBox multilinha (`AcceptsReturn="True"`), leitura linha a linha | 03 / 04 |
| Labels de identificação/saída, botões com evento `Click` | 04 |
| `Math.PI`, `Math.Sqrt`, `Math.Pow`, `Math.Abs` | 05 |
| `ToString("0.000")`, `String.Format`, `{0:F1}`, `{0:G}`, `"0,0.00"` (milhar) | 06 |
| Conversão de String com `double.TryParse` (padrão `out`) | 07 (padrão do `DateTime.TryParse`) |

Adaptações mínimas exigidas pelo enunciado (documentadas em comentários no código):

- `e^x` calculado como `Math.Pow(2.71828182845905, x)` (a aula 05 não lista `Math.Exp`);
- Δv com `ToString("0.000")` — equivalente ao `ToString("N3")` pedido no enunciado,
  porém com o especificador numérico do formato visto na aula 06.

## Formatação de saída (aula 06)

| Saída | Técnica | Exemplo (pt-BR) |
| --- | --- | --- |
| Tempo de viagem | `String.Format("{0:0} dias (~{1:F1} meses)")` | `259 dias (~8,6 meses)` |
| Δv (km/s) | `ToString("0.000")` + unidade | `5,591 km/s` |
| Razão de massa | `ToString("0.0000")` | `6,2872` |
| Combustível (kg / t) | `String.Format("{0:0,0.00} kg ({1:G} t)")` — milhar + especificador genérico com unidade customizada | `26.436,07 kg (26,44 t)` |

## Cenários de validação (valores calculados por este projeto)

O enunciado apresenta resultados com "~" (aproximados). Os valores abaixo são os calculados
pelas fórmulas exatas do enunciado:

| Missão | r₁ | r₂ | m (kg) | Isp (s) | Tempo | Δv total | Combustível |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Terra → Marte | 149,6 | 227,9 | 5000 | 310 | 259 dias (~8,6 meses) | 5,591 km/s | 26.436,07 kg (26,44 t) |
| Terra → Vênus | 149,6 | 108,2 | 3200 | 320 | 146 dias (~4,9 meses) | 5,203 km/s | 13.588,35 kg (13,59 t) |
| Terra → Júpiter | 149,6 | 778,5 | 1200 | 350 | 998 dias (~33,3 meses) | 14,436 km/s | 79.176,72 kg (79,18 t) |
| Terra → Plutão | 149,6 | 5906,4 | 500 | 3000 | 16632 dias (~554,4 meses) | 15,499 km/s | 346,63 kg (0,35 t) |
| Vênus → Marte | 108,2 | 227,9 | 2500 | 310 | 217 dias (~7,2 meses) | 10,530 km/s | 77.249,83 kg (77,25 t) |

> **Observação:** o cenário Terra → Júpiter do enunciado (14,437 km/s; 79.208,61 kg)
> praticamente coincide com o calculado, confirmando a implementação. Alguns dos demais
> valores "esperados" do enunciado são aproximados/incosistentes entre si (ex.: o
> combustível de 25.823,28 kg não é compatível com o próprio Δv de 5,592 km/s informado
> para Terra → Marte), por isso o projeto aplica exatamente as fórmulas definidas no
> enunciado.
