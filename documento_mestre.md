# 🏆 DESBRAVAHUB V3.1 - THE DEFINITIVE MASTER GUIDE
**Versão:** 3.1 (Growth, Gaming & Governance Edition)
**Arquitetura:** Multi-tenant SaaS | PWA | PHP/MySQL | Shared Hosting Optimized
**Mantra:** "Governança Pedagógica, Gamificação Automática e Utilidade Real."

---

## 1. ARQUITETURA DE DADOS & SCHEMA

### 1.1 Tabelas Mestre (Imutáveis - Super Admin)
Onde reside a "Verdade Oficial" da Organização.

```sql
-- Especialidades Oficiais
CREATE TABLE official_specialties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    category ENUM('Natureza', 'Artes', 'Habilidades', 'Espiritual', 'Saúde') NOT NULL,
    official_icon_url VARCHAR(255),
    hex_color VARCHAR(7),
    difficulty_tier ENUM('1_basic', '2_intermediate', '3_advanced', '4_master') NOT NULL,
    fixed_total_xp INT NOT NULL DEFAULT 500, -- Base para o Fair Play Engine
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Requisitos Oficiais (Texto do Manual)
CREATE TABLE official_requirements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    specialty_id INT NOT NULL,
    order_index INT NOT NULL,
    description TEXT NOT NULL,
    is_mandatory BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (specialty_id) REFERENCES official_specialties(id) ON DELETE CASCADE
);
```

### 1.2 Tabelas de Execução do Clube (Mutáveis - Líderes)
Onde o líder define a *metodologia* de ensino.

```sql
-- O Clube "instala" a especialidade
CREATE TABLE club_specialties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    club_id INT NOT NULL,
    official_specialty_id INT NOT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    started_at DATE,
    FOREIGN KEY (official_specialty_id) REFERENCES official_specialties(id)
);

-- Atividade Customizada (O "COMO" fazer)
CREATE TABLE club_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    club_specialty_id INT NOT NULL,
    official_requirement_id INT NOT NULL,
    response_type ENUM('text', 'multiple_choice', 'video_url', 'photo_upload', 'file') NOT NULL, 
    custom_instructions TEXT,
    calculated_xp_share INT NOT NULL, -- Gerado pelo Fair Play Engine
    FOREIGN KEY (club_specialty_id) REFERENCES club_specialties(id) ON DELETE CASCADE,
    FOREIGN KEY (official_requirement_id) REFERENCES official_requirements(id)
);
```

### 1.3 Identidade e Crescimento (Growth & Events)
Novo ecossistema de marketing e presença digital.

```sql
-- Perfil do Clube (Página Pública)
CREATE TABLE club_profiles (
    club_id INT PRIMARY KEY,
    display_name VARCHAR(100),
    slug VARCHAR(100) UNIQUE,
    logo_url VARCHAR(255),
    cover_image_url VARCHAR(255),
    meeting_address VARCHAR(255),
    meeting_time VARCHAR(100),
    social_instagram VARCHAR(100),
    social_whatsapp_group VARCHAR(255),
    welcome_message TEXT,
    leaders_json JSON, -- [{"name": "...", "role": "..."}]
    seo_meta_description VARCHAR(160)
);

-- Eventos e Inscrições
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    club_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(150),
    event_date DATETIME NOT NULL,
    is_paid BOOLEAN DEFAULT FALSE,
    price DECIMAL(10, 2),
    payment_link VARCHAR(255),
    status ENUM('draft', 'published', 'cancelled') DEFAULT 'draft'
);
```

### 1.4 Gamificação Avançada (RPG System)
```sql
-- XP Log e Níveis
CREATE TABLE user_xp_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    amount INT NOT NULL,
    source_type VARCHAR(50), -- 'activity', 'login_streak', 'bonus'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Moeda Virtual (DesbravaCoins)
CREATE TABLE user_inventory (
    user_id INT PRIMARY KEY,
    coins_balance INT DEFAULT 0,
    streak_days INT DEFAULT 0,
    last_login DATE
);
```

---

## 2. REGRAS DE NEGÓCIO & ENGENHARIA

### 2.1 Fair Play XP Engine (Obrigatorio)
O sistema calcula o valor de cada atividade para evitar inflação de rankings.
- **Lógica:** `Calculated_XP = Specialty_Fixed_XP / Count(Mandatory_Requirements)`.
- Implementar no `ActivityController` ao salvar/atualizar atividades.

### 2.2 Integrity Lock (Trava de Segurança)
- Bloquear a transição de `club_specialties.status` para 'published' se `Count(club_activities) < Count(official_requirements WHERE is_mandatory=1)`.

### 2.3 QR Code & Growth Hacking
- Gerar QR Code no backend (PHP) e salvar em `/public/uploads/qrcodes/`.
- Conteúdo: `https://desbravahub.app/c/[slug]/join?utm_source=qr_offline`.

---

## 3. UX/UI & PWA WORLD CLASS

### 3.1 Learning Paths (LMS Style)
- Visualização em **Nodos Animados** (estilo Duolingo/Candy Crush).
- Cores de estado: Bloqueado (Cinza), Em Progresso (Amarelo), Concluído (Verde).

### 3.2 Offline First (Service Workers)
- **Stale-While-Revalidate** para assets essenciais.
- **IndexedDB**: Cache de respostas offline para reenvio automático.

### 3.3 Botão SOS (Geolocalização)
- Ativado em eventos. Envia JSON (Lat/Long) + Push Notification via Firebase (FCM) para os líderes.

---

## 4. CHECKLIST DE IMPLEMENTAÇÃO

1. **DB MIGRATIONS**: Executar DDL de todas as tabelas (Mestre, Clube, Identity, Events, RPG).
2. **BACKEND CORE**: Fair Play XP Engine e Integrity Lock logic.
3. **CMS LIDER**: Perfil do Clube, Gerador de QR Code e CRUD de Eventos.
4. **APP DESBRAVADOR**: Mapa de Trilhas (LMS UI), Feed de Conquistas e SOS Button.
5. **PUBLIC PAGE**: Landing Page otimizada para SEO e Conversão (CTA WhatsApp).
6. **GAME ENGINE**: Habilitar sitema de XP, Moedas e Rankings por Divisão.
7. **PWA**: Finalizar Manifest, Service Workers e Cache.

---

## 5. SEGURANÇA & PERFORMANCE
- **Optimized SQL**: Índices em `slug`, `club_id` e `user_id`.
- **Image handling**: Conversão automática para WebP e limit 2MB.
- **Access Control**: Travas de permissão por cargo (Líder, Desbravador, Pais).