<?php
/**
 * Profile - Livro de Registro e HUD
 * DESIGN: Deep Glass HUD v3.0 (Content Only) with Tabs
 */

// Decode JSON fields if they exist
$medicalConditions = json_decode($userProfile['medical_conditions'] ?? '{}', true) ?: [];
$allergies = json_decode($userProfile['allergies'] ?? '{}', true) ?: [];

// Determine initial active tab
$activeTab = $_GET['tab'] ?? (isset($_GET['incomplete']) ? 'registry' : 'overview');
?>
<style>
    /* Profile Specifics */
    .profile-hud-card {
        background: var(--hud-glass-panel);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--hud-glass-border);
        border-radius: 24px;
        padding: 40px 24px;
        text-align: center;
        max-width: 600px;
        margin: 0 auto 40px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        position: relative;
        overflow: hidden;
    }

    /* Tabs */
    .profile-tabs {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 30px;
        background: rgba(0, 0, 0, 0.2);
        padding: 6px;
        border-radius: 12px;
    }

    .profile-tab-btn {
        background: transparent;
        border: none;
        color: var(--hud-text-dim);
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.85rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .profile-tab-btn.active {
        background: rgba(0, 217, 255, 0.15);
        color: var(--accent-cyan);
        box-shadow: inset 0 0 10px rgba(0, 217, 255, 0.1);
        border: 1px solid rgba(0, 217, 255, 0.3);
    }

    .profile-tab-content {
        display: none;
        text-align: left;
    }

    .profile-tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Hexagon Avatar Border using CSS clip-path or simple border */
    .profile-avatar-hud {
        width: 120px;
        height: 120px;
        margin: 0 auto 20px;
        position: relative;
    }

    .avatar-img-hud {
        width: 100%;
        height: 100%;
        border-radius: 50%; /* Fallback */
        background: linear-gradient(135deg, var(--accent-cyan), var(--hud-bg-radial-start));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 800;
        color: #fff;
        border: 2px solid var(--accent-cyan);
        box-shadow: 0 0 30px rgba(0, 217, 255, 0.2);
    }

    .level-badge-hud {
        position: absolute;
        bottom: 0;
        right: 0;
        background: var(--hud-bg-linear-end);
        border: 1px solid var(--accent-cyan);
        color: var(--accent-cyan);
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 0.8rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.5);
    }

    .profile-name-hud {
        font-size: 1.8rem;
        font-weight: 800;
        color: #fff;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        text-align: center;
    }

    .profile-role-hud {
        color: var(--hud-text-dim);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.2em;
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    /* Stats Row */
    .stats-row-hud {
        display: flex;
        justify-content: center;
        gap: 24px;
        margin-bottom: 40px;
        padding-bottom: 30px;
        border-bottom: 1px solid var(--hud-glass-border);
    }

    .stat-item-hud {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .stat-val-hud {
        font-size: 1.4rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
        margin-bottom: 6px;
        font-family: 'JetBrains Mono', monospace;
    }

    .stat-lbl-hud {
        font-size: 0.65rem;
        text-transform: uppercase;
        color: var(--hud-text-dim);
        font-weight: 700;
        letter-spacing: 0.1em;
    }

    /* Menu Actions */
    .menu-group-hud {
        text-align: left;
    }

    .menu-item-hud {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid transparent;
        border-radius: 8px;
        margin-bottom: 12px;
        transition: all 0.2s;
        text-decoration: none;
        color: var(--hud-text-primary);
    }

    .menu-item-hud:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--accent-cyan);
        transform: translateX(4px);
    }

    .menu-left { display: flex; align-items: center; gap: 16px; }
    
    .menu-icon { 
        color: var(--accent-cyan); 
        opacity: 0.7; 
        font-size: 1.2rem;
    }

    .menu-text {
        font-weight: 700;
        font-size: 0.9rem;
        letter-spacing: 0.05em;
    }

    .menu-right {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.8rem;
        color: var(--hud-text-dim);
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        color: var(--hud-text-dim);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 8px;
        font-weight: 700;
    }

    .form-control, .form-select {
        width: 100%;
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid var(--hud-glass-border);
        color: #fff;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: border-color 0.2s;
    }

    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: var(--accent-cyan);
        background: rgba(0,0,0,0.5);
    }

    .form-row {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    
    .form-col {
        flex: 1;
        min-width: 200px;
    }

    .section-title {
        color: var(--accent-cyan);
        font-size: 1rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin: 30px 0 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(0, 217, 255, 0.2);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Checkboxes */
    .checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--hud-text-primary);
        font-size: 0.85rem;
        cursor: pointer;
        background: rgba(255,255,255,0.03);
        padding: 10px;
        border-radius: 8px;
        border: 1px solid transparent;
        transition: all 0.2s;
    }

    .checkbox-label:hover {
        background: rgba(255,255,255,0.05);
        border-color: rgba(255,255,255,0.1);
    }

    .checkbox-label input[type="checkbox"] {
        accent-color: var(--accent-cyan);
        width: 16px;
        height: 16px;
    }

    .btn-submit-hud {
        display: block;
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, var(--accent-cyan), #0284c7);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 800;
        font-size: 1rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        cursor: pointer;
        margin-top: 30px;
        box-shadow: 0 4px 15px rgba(0, 217, 255, 0.3);
        transition: all 0.3s;
    }

    .btn-submit-hud:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 217, 255, 0.5);
    }
    
    .btn-submit-hud:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .btn-logout-hud {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        margin-top: 32px;
        padding: 16px;
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #ef4444;
        font-weight: 700;
        text-decoration: none;
        border-radius: 8px;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.1em;
        transition: all 0.2s;
    }

    .btn-logout-hud:hover {
        background: rgba(239, 68, 68, 0.2);
        box-shadow: 0 0 15px rgba(239, 68, 68, 0.2);
    }
</style>

<div class="hud-wrapper">
    <header class="hud-header" style="justify-content: center; border-bottom: 0; background: none;">
        <!-- Clean header for focused profile view -->
    </header>

    <div class="profile-hud-card">
        <!-- Decoration Lines -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 2px; background: linear-gradient(90deg, transparent, var(--accent-cyan), transparent); opacity: 0.5;"></div>

        <!-- Custom Profile Tabs -->
        <div class="profile-tabs">
            <button class="profile-tab-btn <?= $activeTab === 'overview' ? 'active' : '' ?>" onclick="switchProfileTab('overview')">
                <span class="material-icons-round" style="font-size: 18px;">dashboard</span>
                Visão Geral
            </button>
            <button class="profile-tab-btn <?= $activeTab === 'registry' ? 'active' : '' ?>" onclick="switchProfileTab('registry')">
                <span class="material-icons-round" style="font-size: 18px;">medical_information</span>
                Livro de Registro
            </button>
        </div>

        <!-- TAB: OVERVIEW (HUD) -->
        <div id="tab-overview" class="profile-tab-content <?= $activeTab === 'overview' ? 'active' : '' ?>">
            <div class="profile-avatar-hud">
                <div class="avatar-img-hud">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div class="level-badge-hud">
                    LVL <?= $progress['level']['number'] ?? 1 ?>
                </div>
            </div>

            <h2 class="profile-name-hud"><?= htmlspecialchars($user['name']) ?></h2>
            <div class="profile-role-hud">
                <span class="material-icons-round" style="font-size: 14px; color: var(--accent-cyan)">verified</span>
                Desbravador Oficial
            </div>

            <div class="stats-row-hud">
                <div class="stat-item-hud">
                    <span class="stat-val-hud" style="color: var(--accent-cyan);"><?= number_format($progress['xp'] ?? 0) ?></span>
                    <span class="stat-lbl-hud">XP TOTAL</span>
                </div>
                <div class="stat-item-hud">
                    <span class="stat-val-hud"><?= $progress['activities']['completed'] ?? 0 ?></span>
                    <span class="stat-lbl-hud">MISSÕES</span>
                </div>
                <div class="stat-item-hud">
                    <span class="stat-val-hud" style="color: var(--accent-warning);">
                        <?= count(array_filter($achievements ?? [], fn($a) => $a['earned_at'])) ?>
                    </span>
                    <span class="stat-lbl-hud">INSÍGNIAS</span>
                </div>
            </div>

            <div class="menu-group-hud">
                <div class="menu-item-hud">
                    <div class="menu-left">
                        <span class="material-icons-round menu-icon">shield</span>
                        <span class="menu-text">UNIDADE</span>
                    </div>
                    <span class="menu-right" style="color: #fff;"><?= htmlspecialchars($tenant['name']) ?></span>
                </div>

                <?php if (!empty($user['pathfinder_class'])): ?>
                <div class="menu-item-hud">
                    <div class="menu-left">
                        <span class="material-icons-round menu-icon">school</span>
                        <span class="menu-text">CLASSE</span>
                    </div>
                    <span class="menu-right"><?= htmlspecialchars($user['pathfinder_class']) ?></span>
                </div>
                <?php endif; ?>

                <div class="menu-item-hud">
                    <div class="menu-left">
                        <span class="material-icons-round menu-icon">calendar_today</span>
                        <span class="menu-text">ALISTAMENTO</span>
                    </div>
                    <span class="menu-right"><?= date('d/m/Y', strtotime($user['created_at'])) ?></span>
                </div>

                <a href="<?= base_url($tenant['slug'] . '/suporte') ?>" class="menu-item-hud" hx-boost="false">
                    <div class="menu-left">
                        <span class="material-icons-round menu-icon" style="color: #14b8a6;">headset_mic</span>
                        <span class="menu-text">SUPORTE / AJUDA</span>
                    </div>
                    <span class="material-icons-round" style="color: var(--hud-text-dim); font-size: 1.2rem;">chevron_right</span>
                </a>
            </div>

            <a href="<?= base_url($tenant['slug'] . '/logout?t=' . time()) ?>" hx-boost="false" class="nav-item-hud danger btn-logout-hud">
                <span class="material-icons-round" style="font-size: 1rem;">logout</span>
                ENCERRAR SESSÃO
            </a>
        </div>

        <?php if (isset($_GET['incomplete'])): ?>
        <div id="incomplete-notice" style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); border-radius: 12px; padding: 15px; margin: 20px; display: flex; flex-direction: column; align-items: center; gap: 12px; animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="material-icons-round" style="color: var(--danger);">warning</span>
                <p style="color: var(--danger); margin: 0; font-size: 0.9rem; font-weight: bold;">
                    Para liberar o acesso ao painel, você precisa preencher todas as informações obrigatórias do seu Livro de Registro.
                </p>
            </div>
            <button onclick="switchProfileTab('registry')" class="hud-btn" style="background: var(--danger); color: #fff; padding: 8px 16px; border-radius: 8px; font-weight: bold; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                <span class="material-icons-round">edit_note</span>
                PREENCHER AGORA
            </button>
        </div>
        <style>
            @keyframes shake {
                10%, 90% { transform: translate3d(-1px, 0, 0); }
                20%, 80% { transform: translate3d(2px, 0, 0); }
                30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
                40%, 60% { transform: translate3d(4px, 0, 0); }
            }
        </style>
        <?php endif; ?>

        <!-- TAB: REGISTRY BOOK (Medical & Full Form) -->
        <div id="tab-registry" class="profile-tab-content <?= $activeTab === 'registry' ? 'active' : '' ?>">
            <form id="registryForm" onsubmit="saveProfile(event)">
                
                <div class="section-title">
                    <span class="material-icons-round">badge</span> Informações Gerais
                </div>

                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-col form-group">
                        <label>Data de Nascimento <span style="color: var(--danger);">*</span></label>
                        <?php
                            $formattedBirthDate = '';
                            if (!empty($user['birth_date'])) {
                                $dateObj = date_create($user['birth_date']);
                                if ($dateObj) {
                                    $formattedBirthDate = date_format($dateObj, 'Y-m-d');
                                }
                            }
                        ?>
                        <input type="date" class="form-control" name="birth_date" id="birth_date" value="<?= htmlspecialchars($formattedBirthDate) ?>" required onchange="calculateAge()">
                    </div>
                    <div class="form-col form-group">
                        <label>Idade (Calculada)</label>
                        <input type="text" class="form-control" id="age_display" value="-" readonly style="background: rgba(255, 255, 255, 0.05); cursor: not-allowed; text-align: center; font-weight: bold; color: var(--accent-cyan);">
                    </div>
                    <div class="form-col form-group">
                        <label>Série Escolar <span style="color: var(--danger);">*</span></label>
                        <input type="text" class="form-control" name="school_grade" placeholder="Ex: 8º Ano Fundamental" value="<?= htmlspecialchars($userProfile['school_grade'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Endereço Completo <span style="color: var(--danger);">*</span></label>
                    <input type="text" class="form-control" name="address" placeholder="Rua, Número, Bairro, CEP..." value="<?= htmlspecialchars($userProfile['address'] ?? '') ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-col form-group">
                        <label>Email Pessoal</label>
                        <!-- Email locked to prevent login breaking, adjust if needed -->
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly style="opacity: 0.6; cursor: not-allowed;">
                    </div>
                    <div class="form-col form-group">
                        <label>Telefone (WhatsApp) <span style="color: var(--danger);">*</span></label>
                        <input type="text" class="form-control" name="phone" placeholder="(11) 90000-0000" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Telefone de Emergência (Responsável) <span style="color: var(--danger);">*</span></label>
                    <input type="text" class="form-control" name="phone_emergency" placeholder="(11) 90000-0000" value="<?= htmlspecialchars($userProfile['phone_emergency'] ?? '') ?>" required>
                </div>

                <div class="section-title">
                    <span class="material-icons-round">favorite</span> Informações Médicas
                </div>

                <div class="form-row">
                    <div class="form-col form-group">
                        <label>Tipo Sanguíneo <span style="color: var(--danger);">*</span></label>
                        <select class="form-select" name="blood_type" required>
                            <option value="">Selecione...</option>
                            <option value="A" <?= ($userProfile['blood_type'] ?? '') == 'A' ? 'selected' : '' ?>>Tipo A</option>
                            <option value="B" <?= ($userProfile['blood_type'] ?? '') == 'B' ? 'selected' : '' ?>>Tipo B</option>
                            <option value="AB" <?= ($userProfile['blood_type'] ?? '') == 'AB' ? 'selected' : '' ?>>Tipo AB</option>
                            <option value="O" <?= ($userProfile['blood_type'] ?? '') == 'O' ? 'selected' : '' ?>>Tipo O</option>
                        </select>
                    </div>
                    <div class="form-col form-group">
                        <label>Fator RH <span style="color: var(--danger);">*</span></label>
                        <select class="form-select" name="rh_factor" required>
                            <option value="">Selecione...</option>
                            <option value="+" <?= ($userProfile['rh_factor'] ?? '') == '+' ? 'selected' : '' ?>>Positivo (+)</option>
                            <option value="-" <?= ($userProfile['rh_factor'] ?? '') == '-' ? 'selected' : '' ?>>Negativo (-)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Vacina contra Tétano (Data da última dose) <span style="color: var(--danger);">*</span></label>
                    <input type="text" class="form-control" name="tetanus_vaccine" placeholder="Ex: 05/2021 ou 'Em dia'" value="<?= htmlspecialchars($userProfile['tetanus_vaccine'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Sofro de (Condições Preexistentes):</label>
                    <div class="checkbox-grid">
                        <label class="checkbox-label">
                            <input type="checkbox" name="cond_diabetes" <?= !empty($medicalConditions['diabetes']) ? 'checked' : '' ?>> Diabetes
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="cond_epilepsy" <?= !empty($medicalConditions['epilepsia']) ? 'checked' : '' ?>> Epilepsia
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="cond_heart" <?= !empty($medicalConditions['coracao']) ? 'checked' : '' ?>> Problema Cardíaco
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="cond_hemophilia" <?= !empty($medicalConditions['hemofilia']) ? 'checked' : '' ?>> Hemofilia
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="cond_bronchitis" <?= !empty($medicalConditions['bronquite']) ? 'checked' : '' ?>> Bronquite
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="cond_asthma" <?= !empty($medicalConditions['asma']) ? 'checked' : '' ?>> Asma
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Outras Condições (Especifique)</label>
                    <input type="text" class="form-control" name="cond_others" placeholder="Especificar..." value="<?= htmlspecialchars($medicalConditions['outros'] ?? '') ?>">
                </div>

                <div class="form-group" style="margin-top: 25px;">
                    <label>Sou Alérgico a:</label>
                    <div class="checkbox-grid" style="grid-template-columns: 1fr 1fr;">
                        <label class="checkbox-label">
                            <input type="checkbox" name="allergy_penicillin" <?= !empty($allergies['penicilina']) ? 'checked' : '' ?>> Penicilina
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="allergy_serum" <?= !empty($allergies['soro']) ? 'checked' : '' ?>> Soro
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Outras Alergias (Especifique - Alimentos, Insetos, Remédios)</label>
                    <input type="text" class="form-control" name="allergy_others" placeholder="Especificar..." value="<?= htmlspecialchars($allergies['outros'] ?? '') ?>">
                </div>

                <button type="submit" class="btn-submit-hud" id="btnSaveProfile">
                    <span class="material-icons-round" style="vertical-align: text-bottom; margin-right: 5px;">save</span>
                    Salvar Ficha Completa
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function calculateAge() {
    const birthDateInput = document.getElementById('birth_date');
    const ageDisplay = document.getElementById('age_display');
    
    if (!birthDateInput.value) {
        ageDisplay.value = '-';
        return;
    }

    const today = new Date();
    const birthDate = new Date(birthDateInput.value);
    
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }

    ageDisplay.value = age + ' anos';
}

// Run calculation immediately and on DOM load/swap
document.addEventListener('DOMContentLoaded', () => setTimeout(calculateAge, 100));
document.body.addEventListener('htmx:afterSwap', () => setTimeout(calculateAge, 100));
setTimeout(calculateAge, 100); // Immediate call for partial loads

function switchProfileTab(tab) {
    document.querySelectorAll('.profile-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.profile-tab-content').forEach(c => c.classList.remove('active'));
    
    // Safely find the target button to highlight
    let targetBtn = null;
    if (window.event && window.event.currentTarget && window.event.currentTarget.classList.contains('profile-tab-btn')) {
        targetBtn = window.event.currentTarget;
    } else {
        targetBtn = document.querySelector(`.profile-tab-btn[onclick*="${tab}"]`);
    }
    
    if (targetBtn) targetBtn.classList.add('active');
    
    const tabContent = document.getElementById('tab-' + tab);
    if (tabContent) tabContent.classList.add('active');
    
    // Update URL to persist tab state
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    window.history.pushState({}, '', url);

    // Recalculate age if switching to registry tab where the input is
    if (tab === 'registry') {
        setTimeout(calculateAge, 50);
    }
}

async function saveProfile(e) {
    if (e) e.preventDefault();
    
    const form = document.getElementById('registryForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    // Checkboxes standard processing
    data.cond_diabetes = form.cond_diabetes.checked;
    data.cond_epilepsy = form.cond_epilepsy.checked;
    data.cond_heart = form.cond_heart.checked;
    data.cond_hemophilia = form.cond_hemophilia.checked;
    data.cond_bronchitis = form.cond_bronchitis.checked;
    data.cond_asthma = form.cond_asthma.checked;
    
    data.allergy_penicillin = form.allergy_penicillin.checked;
    data.allergy_serum = form.allergy_serum.checked;

    const btn = document.getElementById('btnSaveProfile');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="material-icons-round spin" style="vertical-align: text-bottom; margin-right: 5px;">loop</span> Salvando...';

    try {
        const res = await fetch('<?= base_url($tenant['slug'] . '/perfil/salvar') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const json = await res.json();
        
        if (json.success) {
            alert('Sucesso! Ficha salva com segurança.');
            // Reload with cache-busting to force server sync
            const url = new URL(location.href);
            url.searchParams.set('t', Date.now());
            location.href = url.toString();
        } else {
            alert('Erro: ' + (json.message || 'Erro desconhecido'));
        }
    } catch (err) {
        console.error(err);
        alert('Erro de conexão ao salvar.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}
</script>
