# Projeto do 3º Bimestre

**Apresentação e Postagem na Sala Google**

**Disciplina:** Programação Visual Básica

---

## Problema

**Objetos permitidos no projeto** (Caixas texto, Botões de comandos, Labels), Manipulação de strings, Datas e números, nenhum outro tipo de objeto deverá ser inserido no projeto, sem estar no material de aulas.

### Desafio: Calculadora de Trajetória Interplanetária e Consumo de Combustível

Você foi contratado pela agência espacial **AeroSpaceX** para desenvolver uma interface desktop em **WPF**. O sistema deve calcular o **delta-v** (Δv), a duração da viagem e a estimativa de consumo de combustível para missões interplanetárias utilizando a manobra de **Transferência de Hohmann**.

> Formulário deve ser confeccionado usando caixas de texto multilinhas para entrada e Labels para saída e identificação dos dados.
>
> A unidade **t** é o símbolo oficial do tonelada (especificamente a tonelada métrica).

---

## Requisitos da Aplicação

### 1. Interface Gráfica (WPF)

Crie uma janela contendo:

**Entradas de Dados (TextBox/ComboBox):**

- Distância do corpo de origem ao Sol (*r₁*, em milhões de km)
- Distância do corpo de destino ao Sol (*r₂*, em milhões de km)
- Massa da espaçonave (*m*, em kg)
- Impulso Específico do motor (*I<sub>sp</sub>*, em segundos)

**Ações (Button):**

- Botão **"Calcular Trajetória"**
- Botão **"Limpar Campos"**

**Exibição dos Resultados:**

- Tempo de viagem estimado (em dias e meses)
- Mudança de velocidade total (Δ*v*, em km/s)
- Razão de massa e combustível necessário (em kg e toneladas)

### 2. Lógica Matemática

A constante gravitacional do Sol (μ) considerada deve ser **1.327 × 10¹¹ km³/s²**.

1. **Semi-eixo maior da órbita de transferência (*a*):**

   $$
   a = \frac{r_1 + r_2}{2}
   $$

2. **Tempo de viagem (*T<sub>trans</sub>*, em segundos):**

   $$
   T_{trans} = \pi \times \sqrt{\frac{a^3}{\mu}}
   $$

   *(Converta o resultado para dias dividindo por 86.400)*

3. **Delta-V total (Δ*v* em km/s):**

   

   $$
   \Delta v_1 = \sqrt{\frac{\mu}{r_1}} \times \left( \sqrt{\frac{2 \cdot r_2}{r_1 + r_2}} - 1 \right)
   $$

   $$
   \Delta v_2 = \sqrt{\frac{\mu}{r_2}} \times \left( 1 - \sqrt{\frac{2 \cdot r_1}{r_1 + r_2}} \right)
   $$

   $$
   \Delta v_{total} = |\Delta v_1| + |\Delta v_2|
   $$

4. **Equação do Foguete de Tsiolkovsky (Massa de Combustível):**

   $$
   m_{combustivel} = m \times \left( e^{\frac{\Delta v_{total} \times 1000}{I_{sp} \times 9.81}} - 1 \right)
   $$

### 3. Formatação de Strings ( `ToString` e `String.Format` )

Exiba os resultados aplicando estritamente os seguintes formatos:

- **Tempo de Viagem:** Formate utilizando `String.Format` mostrando dias inteiros e meses aproximados (considerando 30 dias/mês).
  - Exemplo: `"128 dias (~4.3 meses)"` (meses com 1 casa decimal).
- **Delta-V:** Exiba usando `ToString("N3")` acrescido da unidade.
  - Exemplo: `"5.592 km/s"`.
- **Combustível Necessário:**
  - Exiba em quilogramas no formato de notação científica ou separador de milhar usando `ToString("N2")`.
  - Exiba também em toneladas formatado via `String.Format` com especificador genérico contendo símbolo de moeda ou unidade customizada.
  - Exemplo: `"42.150,75 kg (42,15 t)"`.

---

## Dados para Teste de Validação

**Missão Terra -> Marte:**

- *r₁*: `149.6` (Milhões de km)
- *r₂*: `227.9` (Milhões de km)
- Massa da Nave: `5000` kg
- *I<sub>sp</sub>*: `310` s

**Resultado Esperado:**

- Tempo: `~258 dias (~8.6 meses)`
- Delta-V: `~5.592 km/s`
- Combustível: `~25.823,28 kg (25,82 t)`

### Cenários de Teste

#### 1. Missão Terra → Vênus (Planeta Interno)

- *r₁* (Origem - Terra): `149.6` (Milhões de km)
- *r₂* (Destino - Vênus): `108.2` (Milhões de km)
- Massa da Nave: `3200` kg
- *I<sub>sp</sub>* (Motor Químico Eficiente): `320` s
- Resultados Esperados:
  - Tempo: `~146 dias (~4.9 meses)`
  - Delta-V: `~5.228 km/s`
  - Combustível: `~13.433,08 kg (13,43 t)`

#### 2. Missão Terra → Júpiter (Gigante Gasoso)

- *r₁* (Origem - Terra): `149.6` (Milhões de km)
- *r₂* (Destino - Júpiter): `778.5` (Milhões de km)
- Massa da Nave: `1200` kg
- *I<sub>sp</sub>* (Propulsão Avançada): `350` s
- Resultados Esperados:
  - Tempo: `~998 dias (~33.3 meses)`
  - Delta-V: `~14.437 km/s`
  - Combustível: `~79.208,61 kg (79,21 t)`

#### 3. Missão Terra → Plutão (Espaço Profundo)

- *r₁* (Origem - Terra): `149.6` (Milhões de km)
- *r₂* (Destino - Plutão): `5906.4` (Milhões de km)
- Massa da Nave: `500` kg
- *I<sub>sp</sub>* (Propulsão Iônica / Plasma de Alto Impulso): `3000` s
- Resultados Esperados:
  - Tempo: `~16609 dias (~553.6 meses)`
  - Delta-V: `~15.823 km/s`
  - Combustível: `~353,24 kg (0,35 t)` *(Demonstra o impacto de um I<sub>sp</sub> elevado na Equação de Tsiolkovsky)*

#### 4. Missão Vênus → Marte (Transferência Interplanetária Secundária)

- *r₁* (Origem - Vênus): `108.2` (Milhões de km)
- *r₂* (Destino - Marte): `227.9` (Milhões de km)
- Massa da Nave: `2500` kg
- *I<sub>sp</sub>* (Motor Químico): `310` s
- Resultados Esperados:
  - Tempo: `~224 dias (~7.5 meses)`
  - Delta-V: `~9.083 km/s`
  - Combustível: `~47.164,52 kg (47,16 t)`
