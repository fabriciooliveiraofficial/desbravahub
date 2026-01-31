<?php
/**
 * Home Page - Página Global para Cadastro de Clubes
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DesbravaHub | Plataforma para Clubes de Desbravadores</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #00d9ff;
            --secondary: #00ff88;
            --accent: #ff6b35;
            --dark-bg: #0a0a1a;
            --card-bg: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.08);
            --text: #e0e0e0;
            --text-muted: #888;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark-bg);
            color: var(--text);
            overflow-x: hidden;
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            background: rgba(10, 10, 26, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Hero */
        .hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 120px 20px 80px;
            position: relative;
            background:
                radial-gradient(ellipse at 20% 30%, rgba(0, 217, 255, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 70%, rgba(0, 255, 136, 0.1) 0%, transparent 50%),
                var(--dark-bg);
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(0, 217, 255, 0.1);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 50px;
            font-size: 0.85rem;
            color: var(--primary);
            margin-bottom: 24px;
        }

        h1 {
            font-size: clamp(2.5rem, 7vw, 4rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 24px;
            max-width: 800px;
        }

        h1 span {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 600px;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark-bg);
            box-shadow: 0 4px 20px rgba(0, 217, 255, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 217, 255, 0.5);
        }

        .btn-secondary {
            background: transparent;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            border-color: var(--primary);
            background: rgba(0, 217, 255, 0.1);
        }

        /* Features Grid */
        .features {
            padding: 100px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 16px;
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto 60px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }

        .feature-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            border-color: var(--primary);
            box-shadow: 0 12px 40px rgba(0, 217, 255, 0.15);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(0, 217, 255, 0.2), rgba(0, 255, 136, 0.2));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 1.3rem;
            margin-bottom: 12px;
        }

        .feature-card p {
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* How it works */
        .how-it-works {
            padding: 100px 20px;
            background: linear-gradient(180deg, transparent, rgba(0, 217, 255, 0.03), transparent);
        }

        .steps {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            max-width: 1000px;
            margin: 0 auto;
        }

        .step {
            flex: 1;
            min-width: 250px;
            max-width: 300px;
            text-align: center;
        }

        .step-number {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark-bg);
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .step h3 {
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        .step p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* CTA */
        .cta {
            padding: 100px 20px;
            text-align: center;
            background: linear-gradient(180deg, transparent, rgba(0, 255, 136, 0.05));
        }

        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 16px;
        }

        .cta p {
            color: var(--text-muted);
            margin-bottom: 32px;
        }

        /* Footer */
        footer {
            padding: 40px 20px;
            text-align: center;
            border-top: 1px solid var(--border);
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            header {
                padding: 16px 20px;
            }

            .hero-buttons {
                flex-direction: column;
                width: 100%;
                padding: 0 20px;
            }

            .btn {
                justify-content: center;
            }

            .steps {
                gap: 30px;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="logo">⚡ DesbravaHub</div>
        <button class="btn btn-secondary" onclick="openLoginModal()" style="padding: 10px 20px;">
            🔐 Entrar
        </button>
    </header>

    <section class="hero">
        <div class="hero-badge">🚀 Plataforma Moderna para Desbravadores</div>
        <h1>Transforme seu Clube de<br><span>Desbravadores</span></h1>
        <p class="hero-subtitle">
            Gestão de especialidades, gamificação, provas digitais e engajamento contínuo.
            Tudo em uma plataforma moderna pensada para o Brasil.
        </p>
        <div class="hero-buttons">
            <a href="<?= base_url('cadastrar-clube') ?>" class="btn btn-primary">
                ✨ Cadastrar meu Clube
            </a>
            <button class="btn btn-secondary" onclick="openLoginModal()">
                🔐 Já tenho conta
            </button>
        </div>
    </section>

    <section class="features" id="recursos">
        <h2 class="section-title">Recursos Poderosos</h2>
        <p class="section-subtitle">
            Tudo que seu clube precisa para engajar desbravadores e acompanhar seu progresso
        </p>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3>Gestão de Especialidades</h3>
                <p>Crie especialidades progressivas com pré-requisitos, níveis mínimos e recompensas em XP.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Provas Digitais</h3>
                <p>Aceite provas via upload de arquivos, links do YouTube, Instagram, TikTok ou quizzes.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🏆</div>
                <h3>Gamificação</h3>
                <p>XP, níveis, conquistas e ranking. Transforme o aprendizado em uma jornada épica.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">👤</div>
                <h3>Painel Exclusivo</h3>
                <p>Cada desbravador tem seu painel isolado com progresso, agenda e histórico.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🔔</div>
                <h3>Notificações Híbridas</h3>
                <p>Toast em tempo real, push notifications e e-mail. Seu desbravador sempre atualizado.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Multi-Tenant Seguro</h3>
                <p>Cada clube tem seu ambiente isolado. Dados nunca são compartilhados entre clubes.</p>
            </div>
        </div>
    </section>

    <section class="how-it-works">
        <h2 class="section-title">Como Funciona</h2>
        <p class="section-subtitle">Seu clube funcionando em 3 passos simples</p>

        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Cadastre seu Clube</h3>
                <p>Crie sua conta e configure o ambiente do seu clube em minutos.</p>
            </div>

            <div class="step">
                <div class="step-number">2</div>
                <h3>Configure Especialidades</h3>
                <p>Adicione diretores, crie especialidades e defina as regras de progressão.</p>
            </div>

            <div class="step">
                <div class="step-number">3</div>
                <h3>Engaje Desbravadores</h3>
                <p>Convide membros e acompanhe a evolução com provas, XP e conquistas.</p>
            </div>
        </div>
    </section>

    <section class="cta">
        <h2>Pronto para <span
                style="background: linear-gradient(90deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Transformar</span>
            seu Clube?</h2>
        <p>Cadastre-se gratuitamente e comece a usar hoje mesmo.</p>
        <a href="<?= base_url('cadastrar-clube') ?>" class="btn btn-primary">
            🚀 Cadastrar meu Clube Agora
        </a>
    </section>

    <footer>
        <p>© <?= date('Y') ?> DesbravaHub. Feito com ❤️ para Desbravadores do Brasil.</p>
    </footer>

    <!-- Login Modal -->
    <div class="modal" id="loginModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🔐 Entrar</h2>
                <button class="modal-close" onclick="closeLoginModal()">×</button>
            </div>

            <div id="clubStep">
                <p class="modal-desc">Encontre seu clube para fazer login</p>

                <div class="form-group">
                    <label>🔍 Buscar por nome</label>
                    <input type="text" id="clubSlug" placeholder="Digite o nome do clube..." autocomplete="off"
                        oninput="filterClubs()">
                </div>

                <div class="club-list" id="clubList">
                    <p class="loading">Carregando clubes...</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--dark-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px;
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .modal-header h2 {
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 28px;
            cursor: pointer;
        }

        .modal-close:hover {
            color: var(--text);
        }

        .modal-desc {
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.02);
            color: var(--text);
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn-full {
            width: 100%;
            justify-content: center;
        }

        .suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--dark-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            z-index: 100;
        }

        .suggestions.active {
            display: block;
        }

        .suggestion-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid var(--border);
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item:hover {
            background: rgba(0, 217, 255, 0.1);
        }

        .suggestion-name {
            font-weight: 600;
        }

        .suggestion-slug {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .filter-row {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
        }

        .filter-row select {
            flex: 1;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.02);
            color: var(--text);
            font-size: 0.9rem;
        }

        .club-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .club-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.2s;
        }

        .club-item:last-child {
            border-bottom: none;
        }

        .club-item:hover {
            background: rgba(0, 217, 255, 0.1);
        }

        .club-info h4 {
            margin: 0 0 4px;
            font-size: 1rem;
        }

        .club-info span {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .club-arrow {
            color: var(--primary);
            font-size: 1.2rem;
        }

        .no-results {
            padding: 40px 20px;
            text-align: center;
            color: var(--text-muted);
        }

        .loading {
            padding: 40px 20px;
            text-align: center;
            color: var(--text-muted);
        }
    </style>

    <script>
        let clubs = [];

        function openLoginModal() {
            document.getElementById('loginModal').classList.add('active');
            loadClubs();
        }

        function closeLoginModal() {
            document.getElementById('loginModal').classList.remove('active');
        }

        document.getElementById('loginModal').addEventListener('click', (e) => {
            if (e.target.id === 'loginModal') closeLoginModal();
        });

        async function loadClubs() {
            try {
                const response = await fetch('<?= base_url('api/clubs') ?>');
                const data = await response.json();
                clubs = data.clubs || [];
                filterClubs();
            } catch (err) {
                clubs = [];
                document.getElementById('clubList').innerHTML = '<p class="no-results">Erro ao carregar clubes</p>';
            }
        }

        function filterClubs() {
            const search = document.getElementById('clubSlug').value.toLowerCase().trim();

            let filtered = clubs;
            if (search) {
                filtered = clubs.filter(c =>
                    c.name.toLowerCase().includes(search) ||
                    c.slug.toLowerCase().includes(search)
                );
            }

            renderClubs(filtered);
        }

        function renderClubs(list) {
            const container = document.getElementById('clubList');

            if (list.length === 0) {
                container.innerHTML = '<p class="no-results">Nenhum clube encontrado</p>';
                return;
            }

            container.innerHTML = list.map(c => `
                <div class="club-item" onclick="goToClub('${c.slug}')">
                    <div class="club-info">
                        <h4>${c.name}</h4>
                        <span>/${c.slug}</span>
                    </div>
                    <span class="club-arrow">→</span>
                </div>
            `).join('');
        }

        function goToClub(slug) {
            window.location.href = '<?= base_url('/') ?>' + slug + '/login';
        }
    </script>
</body>

</html>