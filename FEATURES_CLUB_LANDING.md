# Club Landing Page — Feature Roadmap

> Página pública do clube (`/c/{slug}`) transformada em plataforma social privada e memorável.
> Inspiração: Instagram Stories, TikTok For You Page, BeReal, Locket, Discord, 23snaps.

---

## FASE 1 — Frontend-only (sem alterações no backend/DB)

### ✅ Concluído nesta sessão

- [x] **Fix — Instagram embed** → Substituído blockquote+SDK por iframe direto `/reel/{id}/embed/` (funciona com ad-blockers, Brave, Firefox ETP)
- [x] **Fix — TikTok áudio** → `ttActivateSound` recria o `<iframe>` com `allow="autoplay"` desde o início (browser policy exige criação nova)
- [x] **Fix — SDK removido** → `embed.js` Instagram/TikTok removidos (viewer usa iframe direto; sem impacto no carregamento)
- [x] **Perf — Preconnect hints** → `<link rel="preconnect">` para Instagram/TikTok no lugar dos script tags pesados
- [x] **Web Share API** → `shareMedia()` já usa `navigator.share()` nativo com fallback para clipboard

### ✅ Implementado nesta sessão

- [x] **Story Bubbles** — barra horizontal com avatares dos autores acima do feed; clique filtra o grid por aquele membro; "Todos" reseta o filtro; filtro persiste para novos cards carregados via scroll infinito
- [x] **Navegação no Viewer** — setas prev/next fixas fora do iframe (não bloqueadas pelo conteúdo); swipe left/right por toque; teclado ← → ; botões desativados nos extremos; respeita filtro de autor ativo
- [x] **Feed Mode** — botão toggle Grid ↔ Feed no toolbar; modo feed mostra cards em coluna única horizontal (thumbnail + info); toggle preserva estado do filtro de autor
- [x] **Download button** — botão de download no viewer para conteúdo de `storage/` (fotos/vídeos locais); invisível para links externos (Instagram, TikTok, YouTube)

---

## FASE 2 — Backend-light (queries, sem schema novo)

> ✅ Concluído.

- [x] **"Visto por X" proeminente** — `data-view-count` nos cards; visível na barra de interações
- [x] **Filtro por tipo** — pills "Tudo / Fotos / Vídeos / Reels / TikTok / YouTube" acima do grid; `filterByType()` combina com filtro de autor; contagem de itens por tipo exibida em cada pill
- [x] **Story bubbles com contagem** — badge com número de posts por autor em cada bubble
- [x] **Viewer — info do autor** — strip com avatar, nome e total de posts ao abrir qualquer mídia
- [x] **Ordem por popularidade** — toggle "Recentes ⏱ | Mais curtidos ❤ | Mais vistos 👁" no toolbar; DOM sort, sem chamada ao servidor
- [x] **Meta tags OG** — `og:image` dinâmico: primeiro thumbnail do feed → logo do clube; `twitter:image` incluído

---

## FASE 3 — Backend com novas features (schema + novas rotas)

> ✅ Concluído.

### Reações rápidas (tipo Discord/Facebook)
- [x] **DB** — tabela `public_reactions` (auto-create no primeiro uso, UNIQUE por session+source)
- [x] **Backend** — `POST /c/{slug}/react` toggle por tipo; retorna contagens atualizadas
- [x] **Frontend** — long-press ou right-click no ❤️ abre picker (❤️ 😍 😂 😮 👏 🔥); tap rápido = like; top-3 reações exibidas no card

### Organização por Eventos (23snaps-style)
- [x] **Backend** — `$eventGroups` calculado no controller: eventos dos últimos 90 dias, match ±14d antes / ±7d depois
- [x] **Frontend** — barra horizontal de álbuns acima do grid; clique filtra cards por faixa de data; toggle para resetar filtro

### Modo Fullscreen Vertical (TikTok-style)
- [x] **Frontend** — botão ▶ no toolbar abre overlay fullscreen com cards em `100svh` + `scroll-snap-type: y mandatory`
- [x] **Frontend** — cards TikTok têm thumbnail, título, autor, botões like/share/comentar/expandir na lateral
- [x] **Mobile** — botão fechar (X) fixo; viewer padrão disponível para abrir o conteúdo completo

### Service Worker / Offline-first
- [x] **`public/sw.js`** — já existia (v32): CacheFirst para assets, NetworkFirst para páginas, Background Sync, Push Notifications completo
- [x] **`public/manifest.json`** — já existia com ícones, shortcuts, display standalone
- [x] **`<link rel="manifest">`** — adicionado ao layout público + `<meta name="theme-color">`
- [x] **Banner PWA** — `#pwaInstallBanner` aparece 3s após `beforeinstallprompt`; botões Instalar / Fechar

### Notificações Push
- [x] **SW** — `push` event handler completo no sw.js existente (com SOS, vibração Morse, broadcast para tabs abertas)
- [ ] **Push para visitantes da landing** — requer VAPID key e endpoint de subscrição público (fase futura)

---

## FASE 4 — Melhorias de Conteúdo (requer Admin UX changes)

> Estimativa: 1-2 sessões.

- [ ] **Thumbnails TikTok** — ao aprovar/destacar mídia TikTok, chamar oEmbed imediatamente e persistir `thumbnail_url` (atualmente falha em Hostinger por timeout)
- [ ] **Thumbnails Instagram** — campo de upload manual de thumbnail no painel de aprovação para mídias do Instagram (sem token Meta)
- [ ] **Título automático** — preencher `title` via oEmbed para TikTok e via og:title para YouTube ao destacar
- [ ] **Moderação de comentários** — notificar admin por email quando novo comentário aguarda aprovação

---

## Referências & Inspirações

| Plataforma | Feature adaptada |
|---|---|
| **Instagram Stories** | Story bubbles + swipe navigation |
| **TikTok For You** | Feed vertical + auto-play + swipe |
| **BeReal** | Foco em autenticidade, metadata "postado há X min" |
| **Locket Widget** | Conteúdo push-based, sem algoritmo |
| **23snaps** | Organização por evento/álbum, download de originais |
| **Discord** | Reações por emoji, thread por mídia |
| **YouTube** | Miniaturas, badges de tipo, views prominentes |

---

## Notas técnicas

- **Instagram thumbnails** — impossível sem token Meta (API pública desativada em 2020)
- **TikTok audio autoplay** — impossível contornar a política do browser sem interação dentro do iframe (cross-origin boundary)
- **Meta token** — gratuito mas burocrático; só vale para thumbnails e não resolve o problema de embed
- **Hostinger outbound HTTP** — pode bloquear `file_get_contents` para oEmbed; alternativa: usar cURL com `CURLOPT_FOLLOWLOCATION`
