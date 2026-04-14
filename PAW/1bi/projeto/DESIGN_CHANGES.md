# Registro de Mudanças de Design - Projeto 1° Bimestre

Este documento detalha as melhorias de design e usabilidade implementadas no projeto para elevar a experiência visual e a consistência funcional.

## 🎨 Nova Linguagem Visual (Design System)

Foi implementada uma identidade visual unificada baseada em um conceito "Clean & Modern", substituindo o visual padrão do Bootstrap por componentes personalizados.

### 1. Paleta de Cores Temáticas
Para facilitar a navegação e a identificação de cada ferramenta, foi adotado um sistema de cores por categoria:
- **Saúde (IMC)**: Verde (`#2ecc71`) $\rightarrow$ Hover: `#58d68d`
- **Logística (Produtos)**: Azul (`#3498db`) $\rightarrow$ Hover: `#5dade2`
- **Educação (Notas)**: Amarelo (`#f1c40f`) $\rightarrow$ Hover: `#f3cf2f`
- **Finanças (Funcionário)**: Laranja (`#e67e22`) $\rightarrow$ Hover: `#f39c12`
- **Geometria (Triângulos)**: Roxo (`#9b59b6`) $\rightarrow$ Hover: `#af7ac5`

### 2. Componentes Refatorados

#### **Página Inicial (`pagInicial.html`)**
- **Hero Section**: Implementação de um cabeçalho com gradiente linear moderno.
- **Grid de Funcionalidades**: Substituição de colunas simples por **Cards Interativos**.
- **Interatividade**: Adição de efeito de translação (`translateY`) e sombra dinâmica ao passar o mouse sobre os cards.

#### **Páginas de Ferramentas (`IMC.html`, `Nota.html`, `Funcionario.html`, `Triangulo.html`)**
- **Layout de Formulário**: Migração para o modelo de **Form Cards** centralizados.
- **UX de Input**: Adição de ícones (emojis) nos campos de entrada para tornar a interface mais intuitiva.
- **Botões de Ação**: 
    - Implementação de botões `rounded-pill` (estilo pílula).
    - Correção do efeito de hover: agora utiliza um tom mais claro da cor original com borda sólida, eliminando o "flash" branco.
    - Adição de feedback tátil via `scale` no clique e hover.

#### **Página de Produtos (`Produto.html`)**
- **Catálogo Visual**: Transformação da lista de itens em um grid de cards com imagens centralizadas e preços em destaque.
- **Fluxo de Compra**: Botão de finalização de compra destacado com sombra e tamanho ampliado.

### 3. Melhorias Estruturais (UX)
- **Navegação**: Navbar agora é `sticky-top` (fixada no topo) em todas as páginas.
- **Rodapé**: Padronização de um footer escuro e elegante em todo o site.
- **Tipografia**: Ajustes de pesos de fonte (`fw-bold`, `fw-semibold`) para criar hierarquia visual clara.

## 🚀 Resultados
O site deixou de ter a aparência de "template básico" para se tornar uma aplicação com identidade visual própria, facilitando a usabilidade e tornando a interface muito mais atraente e profissional.
