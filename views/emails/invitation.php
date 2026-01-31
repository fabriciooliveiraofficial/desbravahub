<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convite Especial | <?= htmlspecialchars($tenant['name']) ?></title>
    <!-- Import Google Fonts for unique typography -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700&family=Playfair+Display:ital,wght@0,600;1,600&display=swap');
    </style>
    <style>
        /* Base Reset */
        body, html { margin: 0; padding: 0; }
        
        /* Theme Variables (Inline for Email Compatibility) */
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #F8FAFC; /* Light Blue-Grey Background */
            color: #1E293B; /* Slate 800 - High Contrast Text */
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* Container */
        .wrapper {
            width: 100%;
            background-color: #F8FAFC;
            padding: 40px 10px;
        }

        .email-container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.05); /* Soft, premium shadow */
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        /* Header with Organic Shape */
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); /* Deep Navy Gradient */
            padding: 48px 32px 32px;
            text-align: center;
            position: relative;
        }

        /* Decorative Pattern Overlay */
        .header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: 
                radial-gradient(circle at 120% 50%, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0) 40%),
                radial-gradient(circle at -20% 20%, rgba(6, 182, 212, 0.05) 0%, rgba(6, 182, 212, 0) 40%);
        }

        .brand-name {
            color: #ffffff;
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: -0.5px;
            margin: 0;
            position: relative;
        }

        .brand-logo {
            font-size: 42px;
            display: block;
            margin-bottom: 12px;
            text-shadow: 0 4px 12px rgba(0,0,0,0.2);
            position: relative;
        }

        /* Content Area */
        .content {
            padding: 48px 40px;
            text-align: left;
        }

        .greeting {
            font-size: 20px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 8px;
        }

        .headline {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            line-height: 1.2;
            color: #0F172A;
            margin: 0 0 24px 0;
        }

        .highlight-text {
            color: #0E7490; /* Cyan 700 */
            position: relative;
            display: inline-block;
        }
        
        /* Subtle underline effect for highlight */
        .highlight-text::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 0;
            width: 100%;
            height: 6px;
            background-color: rgba(6, 182, 212, 0.15); /* Soft Cyan */
            z-index: -1;
            transform: skewX(-12deg);
        }

        .invite-message {
            font-size: 16px;
            color: #475569;
            margin-bottom: 32px;
        }

        /* Custom Message Quote */
        .quote-box {
            background-color: #F8FAFC;
            border-left: 3px solid #06B6D4;
            padding: 24px 28px;
            margin: 0 0 32px 0;
            border-radius: 0 12px 12px 0;
        }

        .quote-text {
            font-style: italic;
            font-size: 16px;
            color: #334155;
            margin: 0 0 12px 0;
            font-family: 'Playfair Display', serif;
        }

        .quote-author {
            font-size: 13px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Role Card */
        .role-card {
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            margin-bottom: 36px;
        }

        .role-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748B;
            margin-bottom: 8px;
            display: block;
        }

        .role-title {
            font-size: 22px;
            font-weight: 700;
            color: #0F172A;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Hero Button */
        .btn-container {
            text-align: center;
            margin-bottom: 32px;
        }

        .btn-primary {
            display: inline-block;
            background-color: #0F172A; /* Slate 900 */
            color: #ffffff !important;
            padding: 18px 42px;
            border-radius: 100px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.15); /* Lifted shadow */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(15, 23, 42, 0.2);
            background-color: #1E293B;
        }

        .expiry {
            font-size: 13px;
            color: #94A3B8;
            text-align: center;
            display: block;
        }

        /* Footer */
        .footer {
            background-color: #F1F5F9;
            padding: 32px;
            text-align: center;
            font-size: 13px;
            color: #64748B;
            border-top: 1px solid #E2E8F0;
        }
        
        .footer p { margin: 8px 0; }
        .footer-logo { font-size: 20px; display: block; margin-bottom: 12px; opacity: 0.5; }

        @media (max-width: 600px) {
            .content { padding: 32px 24px; }
            .header { padding: 40px 20px 24px; }
            .headline { font-size: 28px; }
            .btn-primary { width: 100%; box-sizing: border-box; }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="email-container">
            <!-- Distinctive Header -->
            <div class="header">
                <div class="brand-logo">⚡</div>
                <h1 class="brand-name"><?= htmlspecialchars($tenant['name']) ?></h1>
            </div>

            <!-- Main Content -->
            <div class="content">
                <div class="greeting"><?= htmlspecialchars($greeting) ?></div>
                
                <h2 class="headline">
                    Você foi convidado para <br>
                    <span class="highlight-text">liderar conosco.</span>
                </h2>

                <p class="invite-message">
                    <strong><?= htmlspecialchars($inviter['name']) ?></strong> indicou você para se juntar à equipe oficial no DesbravaHub. Estamos ansiosos para ver sua contribuição.
                </p>

                <?php if (!empty($customMessage)): ?>
                    <div class="quote-box">
                        <p class="quote-text">"<?= nl2br(htmlspecialchars($customMessage)) ?>"</p>
                        <span class="quote-author">— <?= htmlspecialchars($inviter['name']) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Feature Role Card -->
                <div class="role-card">
                    <span class="role-label">Sua Nova Função</span>
                    <h3 class="role-title">
                        <?= htmlspecialchars($roleLabel) ?>
                    </h3>
                </div>

                <!-- Primary Action -->
                <!-- Primary Action - Bulletproof Table Button -->
                <!-- Primary Action - Bulletproof Table Button -->
                <div class="btn-container">
                    <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                        <tr>
                            <td align="center" bgcolor="#0F172A" style="border-radius: 100px; box-shadow: 0 10px 20px rgba(15, 23, 42, 0.15);">
                                <a href="<?php echo $inviteUrl; ?>" target="_blank" style="font-size: 16px; font-family: 'Outfit', sans-serif; color: #ffffff; text-decoration: none; padding: 18px 42px; border-radius: 100px; border: 1px solid #0F172A; display: inline-block; font-weight: 600; cursor: pointer;">
                                    Aceitar Convite &rarr;
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Fallback Link -->
                <div style="text-align: center; margin-bottom: 24px; word-break: break-all;">
                    <p style="font-size: 13px; color: #64748B; margin-bottom: 8px;">Se o botão não funcionar, clique ou copie o link abaixo:</p>
                    <a href="<?php echo $inviteUrl; ?>" target="_blank" style="color: #0E7490; font-size: 13px; text-decoration: underline;">
                        <?php echo $inviteUrl; ?>
                    </a>
                </div>

                <span class="expiry">⚠️ Link válido até <?php echo date('d/m/Y', strtotime($expiresAt)); ?></span>
            </div>

            <!-- Minimalist Footer -->
            <div class="footer">
                <span class="footer-logo">⚡</span>
                <p>Se você não esperava por este convite, pode ignorá-lo com segurança.</p>
                <p>&copy; <?= date('Y') ?> DesbravaHub. Todos os direitos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
