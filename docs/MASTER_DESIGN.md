# 💎 DesbravaHub: Master Design System & Style Guide

Este documento serve como a **Fonte Única de Verdade (Single Source of Truth)** para o design do DesbravaHub. Todas as novas páginas, componentes e ajustes devem seguir rigorosamente estas diretrizes para garantir uma experiência premium, consistente e coesa.

---

## 🎨 Paleta de Cores (Design Tokens)

O sistema utiliza um esquema binário (Claro/Escuro) com toques de **Glassmorphism**.

### 🌑 Modo Escuro (Principal do App)
- **Background Principal:** `#1a1a2e` (Radial start to `#16213e`)
- **Background Lateral/Superiores:** `rgba(26, 26, 46, 0.95)` (com blur de 10px-12px)
- **Cards/Superfícies:** `rgba(255, 255, 255, 0.05)` (Glass Effect)
- **Bordas:** `rgba(255, 255, 255, 0.1)`

### ☀️ Modo Claro (Painel Administrativo)
- **Background Principal:** `#F3F4F6`
- **Sidebar/Cards:** `#FFFFFF`
- **Background Hover:** `#F8FAFC`
- **Bordas:** `#e2e8f0`

### ✨ Cores de Destaque (Acentos)
- **Cyan (Primário):** `#06b6d4` (Hover: `#0891b2`)
- **Emerald (Sucesso):** `#10b981`
- **Amber (Aviso):** `#f59e0b`
- **Red (Erro/Perigo):** `#ef4444`
- **Purple (Especial):** `#8b5cf6`

---

## Typography (Tipografia)

### Fontes Principais
1.  **Inter:** Usada para corpo de texto, navegação e interface administrativa.
    - Pesos: 300, 400, 500, 600, 700.
2.  **Outfit:** Usada para títulos de destaque, nomes de desbravadores e elementos de impacto (ex: avaliações).
    - Pesos: 700, 800.
3.  **JetBrains Mono:** Usada para dados técnicos, valores de XP, pontos e códigos.
    - Letramento: `-0.05em`.

### Escala de Texto
- **H1 (Hero):** `2.2rem`, peso 800 (Outfit).
- **H2 (Seção):** `1.25rem`, peso 700 (Outfit ou Inter).
- **H3 (Card):** `1.125rem`, peso 700 (Inter).
- **Body:** `0.875rem` ou `0.9rem`.
- **Meta/Small:** `0.75rem`.

---

## 🎨 Iconografia (Iconography)

Para garantir modernidade e traços finos (thin-lines), o DesbravaHub utiliza o **Iconify** como motor principal, permitindo acesso a um banco de dados massivo de **150.000+ ícones** de quase todas as bibliotecas existentes.

### Bibliotecas Recomendadas & Especiais
1.  **Aventura & Insígnias (Game Icons):** Ideal para troféus, escudos, e elementos de RPG/Aventura. (`game-icons:name`)
2.  **Animais & Objetos (Noto Emojis):** Emojis de alta definição para mascotes e itens realistas. (`noto:name`)
3.  **Lucide:** Ícones limpos, geométricos e modernos. (`lucide:name`)
4.  **Phosphor (Thin/Light):** Design minimalista com traços super finos. (`ph:name-thin`)
5.  **Solar:** Coleção ultra-moderna e estilizada. (`solar:name-linear`)
6.  **Tabler:** Perfeito para interfaces administrativas detalhadas. (`tabler:name`)

### Motor de Busca (Massive Search)
- O motor de busca está configurado para vasculhar **todo o ecossistema Iconify** sem restrições de prefixo.
- Digite o nome do objeto ou ação (em inglês) para resultados globais (ex: "Lion", "Camping", "Award").
- O seletor exibe o prefixo da biblioteca na base do ícone para facilitar a identificação da origem.

### Regras de Uso
- **Traço:** Para interfaces administrativas, prefira **Thin** ou **Light** (espessura de 1.5px a 2px).
- **Mascotes:** Sinta-se livre para usar ícones mais densos ou Emojis coloridos para dar personalidade às Unidades.
- **Cor:** Devem seguir as variáveis de acento (`--color-accent`) ou tons de cinza (`--color-text-sub`).
- **Tamanho:** 1.5rem (24px) para ações e 2rem (32px) para ícones de cabeçalho.

---

## 🔲 Bordas e Sombras

### Border Radius (Raios)
- **Pequeno (sm):** `0.375rem` (6px)
- **Médio (md):** `0.5rem` (8px)
- **Grande (lg):** `0.75rem` (12px)
- **Extra Grande (xl):** `1rem` (16px) - *Padrão para Cards normais*
- **2XL:** `1.5rem` (24px) - *Padrão para Cards de Avaliação/Premium*
- **Full:** `9999px`

### Shadows (Sombras Premium)
- **Shadow Card:** `0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 20px -5px rgba(0, 0, 0, 0.05)`
- **Shadow Glow (Cyan):** `0 10px 20px -5px rgba(6, 182, 212, 0.3)`
- **Shadow Card Hover:** `0 10px 30px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)`

---

## 🔘 Botões (Components)

### Botão Primário (Action)
- **Gradient:** `linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)`
- **Texto:** Branco, peso 600.
- **Radius:** `0.75rem` (lg).
- **Animação:** `translateY(-2px)` no hover com aumento suave da sombra.

### Botão de Sucesso/Emerald
- **Gradient:** `linear-gradient(135deg, #10b981 0%, #059669 100%)`

### Botão Secundário/Outline
- **Background:** Transparente ou `var(--bg-card)`.
- **Border:** `1px solid var(--border-color)`.

---

## 📐 Espaçamento (Paddings & Margins)

### Containers
- **Padding Horizontal Padrão:** `2rem` (32px) em desktop, `1rem` (16px) em mobile.
- **Gaps em Grids:** `1.5rem` (24px) é o padrão ouro para separação de cards.

### Dentro de Cards
- **Padding Interno:** `1.5rem` (24px) para cards padrão.
- **Padding Premium:** `2rem` (32px) para seções de destaque.

---

## 🧩 Elementos de UI Comuns

### Cards (Dashboard Card)
```css
{
    background: var(--bg-card);
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-card);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
```

### Tabelas
- **Header:** Background leve (`var(--bg-dark)`), texto uppercase, peso 600, font-size `0.8rem`.
- **Rows:** Hover com `translateY` sutil e uma borda lateral (`3px`) colorida no hover.

### Badges (Status)
- **Estilo:** `pill` (radius full), font-size `0.75rem`, background translúcido (opacity 0.1) com texto na cor pura.

---

## ⚡ Animações e Micro-interações

1.  **Slide Up:** Todos os cards devem entrar com um leve `translateY(20px)` e `opacity: 0` para `1`.
2.  **Transição Base:** `0.2s ease` para hovers simples.
3.  **Transição Bounce:** `0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)` para elementos interativos que "saltam".
4.  **Glassmorphism:** Uso de `backdrop-filter: blur(10px)` em cabeçalhos e menus flutuantes.

---

## 🎬 Cinema & Motion (Advanced Animations)

Para interações complexas que exigem física (Spring) ou sequenciamento avançado, o DesbravaHub utiliza o **Motion (Framer Motion Vanilla)** via Web Animations API (WAAPI).

### Biblioteca & CDN
- **Versão:** Motion 11+
- **CDN:** `https://cdn.jsdelivr.net/npm/motion@11.11.13/dist/motion.js`

### Princípios de Movimento
1.  **Física sobre Duração:** Use `type: "spring"` para modais e popups para um feeling orgânico.
2.  **Sequenciamento:** Use a função `animate` para orquestrar entradas de múltiplos elementos (Stagger).
3.  **Hardware Acceleration:** O Motion utiliza WAAPI por padrão, garantindo 60fps constantes.

### Quando Usar?
- **CSS Transitions:** Para hovers simples, mudanças de cor e opacidade básicas.
- **Motion JS:** Para modais complexos, animações de números (counters), arraste (drag) e entradas de página coordenadas.

```javascript
/* Exemplo de implementação Motion */
const { animate } = Motion;
animate(".dashboard-card", 
    { opacity: [0, 1], y: [20, 0] }, 
    { delay: stagger(0.1), duration: 0.5 }
);
```

---

## 🛠️ Regra de Ouro (Golden Rule)

> **"Consistência acima de criatividade isolada."**
> Nunca crie um novo padding, cor ou sombra se já existir um correspondente neste documento. O uso de variáveis CSS (`var(--...)`) é obrigatório para todas as propriedades de design.

```css
/* Exemplo de implementação correta */
.minha-nova-secao {
    padding: 1.5rem;
    background: var(--bg-card);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-card);
}
```

---

## 🏁 END OF DOCUMENT
