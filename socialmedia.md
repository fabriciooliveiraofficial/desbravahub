# Arquitetura de Conversão e Embedding de Mídias Sociais

Este documento descreve as técnicas, ferramentas e padrões arquiteturais utilizados no sistema **DesbravaHub** para converter URLs de redes sociais em players nativos e interativos, permitindo a visualização de conteúdos sem que o usuário precise sair da plataforma.

## 1. Visão Geral da Arquitetura
O sistema utiliza uma abordagem híbrida que combina processamento *server-side* (PHP) para segurança e estruturação inicial, com execução *client-side* (JS) via SDKs oficiais das plataformas para renderização e interatividade.

### Componentes Principais:
- **`helpers/media.php`**: O motor central que contém a lógica de regex e geração de HTML.
- **`curated_media`**: Tabela de banco de dados que armazena URLs filtradas e validadas para o feed público.
- **SDK Registry Pattern**: Um sistema global que evita a duplicidade de scripts (JS) na página.

---

## 2. Técnicas de Reconhecimento (Regex)
O sistema utiliza expressões regulares (Regex) de alta precisão para identificar vídeos de diferentes plataformas:

- **YouTube**: Identifica vídeos padrão, lives e o formato vertical (Shorts).
  - *Regex*: `/(?:(?:(?:m|www)\.)?youtube\.com\/(?:[^\/\s]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/|live\/)|youtu\.be\/)([^"&?\/\s]{11})/i`
- **Instagram**: Captura Reels, posts de fotos e vídeos da IGTV.
  - *Regex*: `/(?:https?:\/\/)?(?:www\.)?instagram\.com\/(p|reel|reels|tv)\/([A-Za-z0-9_-]+)/i`
- **TikTok**: Reconhece vídeos via ID numérico direto na URL.
  - *Regex*: `/(?:https?:\/\/)?(?:www\.)?tiktok\.com\/.*\/video\/([0-9]+)/i`
- **X (Twitter)**: Normaliza links `x.com` de volta para `twitter.com` para compatibilidade com o widget oficial.
  - *Regex*: `/(?:https?:\/\/)?(?:www\.)?(?:twitter|x)\.com\/[^\/\s]+\/status\/([0-9]+)/i`

---

## 3. Estratégias de Embedding por Plataforma

### YouTube (Player Nativo)
Utiliza uma `<iframe>` padrão baseada em `youtube.com/embed/`.
- **Diferencial**: Detecta automaticamente se o vídeo é um *Short* para ajustar o `aspect-ratio` do container para 9:16 (vertical), garantindo que o vídeo preencha a tela corretamente em dispositivos móveis.

### Instagram & X (SDK Pattern)
Para estas redes, o sistema utiliza as tags semânticas `<blockquote>`.
- **Vantagem**: Ao usar o SDK oficial, o player herda automaticamente os estilos nativos da rede social (botões de like, perfil, legendas), oferecendo uma experiência de "player premium".
- **Técnica**: O sistema limpa a URL (remove trackers) para garantir que apenas o conteúdo essencial seja carregado.

### TikTok (Resolução de Links Curtos)
URLs do tipo `vm.tiktok.com` não contêm o ID do vídeo diretamente. 
- **Ferramenta**: O sistema utiliza o protocolo **oEmbed** da TikTok. O servidor faz uma requisição silenciosa via `file_get_contents` para a API da TikTok para extrair o código de incorporação real antes de entregar a página ao usuário.

---

## 4. Gerenciamento Inteligente de SDKs (Performance)
Um problema comum em aplicações web é carregar o mesmo script várias vezes. Implementamos o **SDK Injection Pattern**:

1. A função `embed_media()` detecta qual plataforma está sendo usada e marca uma bandeira em um array global (`$GLOBALS['_media_sdk_registry']`).
2. No final da página (logo antes do `</body>`), a função `embed_media_sdk_scripts()` verifica esse registro.
3. Se houver vídeos de 3 plataformas diferentes, ela carrega os 3 SDKs uma única vez de forma assíncrona (`async`), mantendo o carregamento da página veloz.

---

## 5. Curação e Thumbnails
Para o feed público (Hub), onde a velocidade é crítica, o sistema não carrega os players imediatamente:

- **Thumbnails Dinâmicas**: A função `fetch_media_thumbnail` busca a imagem de capa em alta resolução (ex: via `img.youtube.com`) para mostrar no feed.
- **Lazy Loading**: Os scripts das redes sociais só são ativados quando o usuário realmente clica para ver o conteúdo, economizando dados e processamento.

---

> [!TIP]
> **Dica Técnica**: Para garantir que as redes sociais carreguem corretamente em modais ou abas dinâmicas, o sistema dispara um sinal de re-processamento via JavaScript (ex: `instgrm.Embeds.process()`) sempre que uma nova mídias é exibida.
