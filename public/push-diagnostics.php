<?php
/**
 * Push Notifications Diagnostic Tool — DesbravaHub
 *
 * Captures real-time behavior, server config, DB state, SW status,
 * and live logs to help diagnose push notification failures.
 *
 * Access: /push-diagnostics.php
 * SECURITY: Delete or protect this file on production when not needed.
 */

require_once __DIR__ . '/../bootstrap/bootstrap.php';

// ── Server-side data ────────────────────────────────────────────────────────

// VAPID
$vapidPublic  = config('vapid.public_key', '');
$vapidPrivate = config('vapid.private_key', '');
$vapidSubject = config('vapid.subject', '');

$vapidOk = !empty($vapidPublic) && !empty($vapidPrivate);

// DB subscriptions (all tenants — diagnostic mode)
$allSubs = [];
try {
    $allSubs = db_fetch_all("
        SELECT ps.*, u.name AS user_name, u.email AS user_email,
               t.name AS tenant_name, t.slug AS tenant_slug
        FROM push_subscriptions ps
        LEFT JOIN users u ON u.id = ps.user_id
        LEFT JOIN tenants t ON t.id = ps.tenant_id
        ORDER BY ps.created_at DESC
        LIMIT 50
    ", []);
} catch (\Throwable $e) {
    $allSubs = ['error' => $e->getMessage()];
}

// Recent push log (last 60 lines)
$logFile  = BASE_PATH . '/storage/logs/push.log';
$logLines = [];
if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $logLines = array_slice($lines, -60);
}

// Current session user (if any)
$sessionUser = null;
try {
    session_start();
    if (!empty($_SESSION['user_id'])) {
        $sessionUser = db_fetch_one("SELECT id, name, email FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }
} catch (\Throwable $e) {}

// Notification records (last 20)
$recentNotifications = [];
try {
    $recentNotifications = db_fetch_all("
        SELECT n.*, u.name AS user_name
        FROM notifications n
        LEFT JOIN users u ON u.id = n.user_id
        ORDER BY n.created_at DESC
        LIMIT 20
    ", []);
} catch (\Throwable $e) {}

// AJAX: send test push to a specific user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');

    $action = $_GET['action'];

    if ($action === 'send_test') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        if (!$userId) { echo json_encode(['ok' => false, 'error' => 'user_id required']); exit; }

        try {
            $service = new \App\Services\NotificationService();
            $service->send(
                $userId,
                'test_push',
                '🔧 Diagnóstico DesbravaHub',
                'Notificação de teste enviada às ' . date('H:i:s') . '. Se você está vendo isso, push está funcionando! 🎯',
                [
                    'channels' => ['toast', 'push'],
                    'priority' => 'normal',
                    'data' => ['icon' => '🔧']
                ]
            );
            echo json_encode(['ok' => true, 'message' => "Push enviado para user $userId"]);
        } catch (\Throwable $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'send_critical') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        if (!$userId) { echo json_encode(['ok' => false, 'error' => 'user_id required']); exit; }

        try {
            $service = new \App\Services\NotificationService();
            $service->send(
                $userId,
                'test_push',
                '🆘 TESTE SOS — Diagnóstico',
                'Alerta crítico de teste às ' . date('H:i:s') . '. Deve aparecer CENTRALIZADO com som urgente.',
                [
                    'channels' => ['toast', 'push'],
                    'priority' => 'critical',
                    'data' => ['icon' => '🆘']
                ]
            );
            echo json_encode(['ok' => true, 'message' => "Push crítico enviado para user $userId"]);
        } catch (\Throwable $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'delete_subs') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        if (!$userId) { echo json_encode(['ok' => false, 'error' => 'user_id required']); exit; }
        db_query("DELETE FROM push_subscriptions WHERE user_id = ?", [$userId]);
        echo json_encode(['ok' => true, 'message' => "Subscriptions deletadas para user $userId"]);
        exit;
    }

    if ($action === 'log') {
        $lines = file_exists($logFile)
            ? array_slice(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -80)
            : [];
        echo json_encode(['lines' => $lines]);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🔧 Push Diagnostics — DesbravaHub</title>
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: #0a0f1e; color: #e2e8f0; font-family: 'Courier New', monospace; font-size: 13px; }

    header { background: linear-gradient(135deg, #1e293b, #0f172a); border-bottom: 1px solid #334155; padding: 16px 24px; display: flex; align-items: center; gap: 12px; }
    header h1 { font-size: 1.2rem; color: #38bdf8; letter-spacing: .05em; }
    header .badge { background: #dc2626; color: #fff; font-size: 10px; padding: 2px 8px; border-radius: 999px; }

    .layout { display: grid; grid-template-columns: 1fr 1fr; gap: 0; height: calc(100vh - 57px); }
    .panel { overflow-y: auto; padding: 16px; border-right: 1px solid #1e293b; }
    .panel:last-child { border-right: none; }

    h2 { font-size: .7rem; text-transform: uppercase; letter-spacing: .15em; color: #64748b; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid #1e293b; }

    .card { background: #0f172a; border: 1px solid #1e293b; border-radius: 8px; padding: 12px 14px; margin-bottom: 10px; }
    .card-title { font-size: .65rem; text-transform: uppercase; letter-spacing: .1em; color: #64748b; margin-bottom: 6px; }

    .status { display: inline-flex; align-items: center; gap: 6px; font-size: .75rem; font-weight: 700; }
    .status.ok   { color: #10b981; }
    .status.warn { color: #f59e0b; }
    .status.err  { color: #ef4444; }
    .status::before { content: '●'; font-size: .6rem; }

    .key { color: #94a3b8; }
    .val { color: #e2e8f0; word-break: break-all; }
    .mono { font-family: 'Courier New', monospace; font-size: .7rem; }

    table { width: 100%; border-collapse: collapse; font-size: .7rem; }
    th { color: #64748b; text-align: left; padding: 4px 8px; border-bottom: 1px solid #1e293b; }
    td { padding: 4px 8px; border-bottom: 1px solid #0f172a; color: #cbd5e1; vertical-align: top; }
    tr:hover td { background: #1e293b44; }

    .tag { display: inline-block; padding: 1px 6px; border-radius: 4px; font-size: .65rem; font-weight: 700; }
    .tag-green { background: #064e3b; color: #34d399; }
    .tag-red   { background: #450a0a; color: #f87171; }
    .tag-blue  { background: #0c4a6e; color: #38bdf8; }
    .tag-yellow{ background: #451a03; color: #fbbf24; }

    button { background: #1d4ed8; color: #fff; border: none; border-radius: 6px; padding: 6px 14px; cursor: pointer; font-size: .75rem; font-weight: 700; transition: background .15s; }
    button:hover { background: #2563eb; }
    button.danger { background: #7f1d1d; color: #fca5a5; }
    button.danger:hover { background: #991b1b; }
    button.warn { background: #78350f; color: #fde68a; }
    button.warn:hover { background: #92400e; }
    input[type=number] { background: #1e293b; border: 1px solid #334155; color: #e2e8f0; padding: 5px 8px; border-radius: 6px; font-size: .75rem; width: 80px; }

    /* Live Log */
    #live-log { background: #020617; border: 1px solid #1e293b; border-radius: 8px; height: 320px; overflow-y: auto; padding: 10px; font-size: .68rem; line-height: 1.6; }
    .log-info  { color: #38bdf8; }
    .log-ok    { color: #10b981; }
    .log-warn  { color: #f59e0b; }
    .log-error { color: #ef4444; }
    .log-push  { color: #a78bfa; font-weight: 700; }
    .log-ts    { color: #334155; margin-right: 4px; }

    #client-status { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px; }
    .cs-item { background: #0f172a; border: 1px solid #1e293b; border-radius: 6px; padding: 8px 10px; }
    .cs-label { font-size: .6rem; color: #64748b; text-transform: uppercase; letter-spacing: .1em; }
    .cs-value { font-size: .8rem; font-weight: 700; margin-top: 3px; }

    .actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-bottom: 10px; }

    .sep { height: 1px; background: #1e293b; margin: 12px 0; }

    .endpoint-cell { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .scroll-anchor { height: 1px; }

    @media (max-width: 900px) {
        .layout { grid-template-columns: 1fr; height: auto; }
        .panel { height: auto; }
    }
</style>
</head>
<body>

<header>
    <span style="font-size:1.4rem">🔧</span>
    <h1>Push Notifications Diagnostics</h1>
    <span class="badge">DEV ONLY</span>
    <span style="margin-left:auto;color:#475569;font-size:.7rem"><?= date('Y-m-d H:i:s') ?></span>
</header>

<div class="layout">

    <!-- ═══════════════ LEFT PANEL — SERVER SIDE ═══════════════ -->
    <div class="panel">

        <!-- VAPID -->
        <h2>🔑 VAPID Configuration</h2>
        <div class="card">
            <div style="display:grid;gap:6px">
                <div>
                    <span class="key">Public Key: </span>
                    <span class="<?= $vapidPublic ? 'status ok' : 'status err' ?>">
                        <?= $vapidPublic ? 'SET ('.strlen($vapidPublic).' chars)' : 'MISSING' ?>
                    </span>
                </div>
                <?php if ($vapidPublic): ?>
                <div class="mono" style="color:#64748b;font-size:.6rem;word-break:break-all"><?= htmlspecialchars(substr($vapidPublic,0,30)) ?>...</div>
                <?php endif; ?>
                <div>
                    <span class="key">Private Key: </span>
                    <span class="<?= $vapidPrivate ? 'status ok' : 'status err' ?>">
                        <?= $vapidPrivate ? 'SET ('.strlen($vapidPrivate).' chars)' : 'MISSING' ?>
                    </span>
                </div>
                <div><span class="key">Subject: </span><span class="val"><?= htmlspecialchars($vapidSubject ?: 'NOT SET') ?></span></div>
                <div><span class="key">Overall: </span>
                    <span class="<?= $vapidOk ? 'status ok' : 'status err' ?>"><?= $vapidOk ? 'READY' : 'INCOMPLETE' ?></span>
                </div>
            </div>
        </div>

        <!-- DB Subscriptions -->
        <h2>📦 push_subscriptions (últimas 50)</h2>
        <?php if (isset($allSubs['error'])): ?>
        <div class="card" style="border-color:#7f1d1d"><span class="status err">DB Error: <?= htmlspecialchars($allSubs['error']) ?></span></div>
        <?php elseif (empty($allSubs)): ?>
        <div class="card"><span class="status warn">Nenhuma subscription registrada no banco</span></div>
        <?php else: ?>
        <div class="card" style="padding:0;overflow:hidden">
        <table>
            <thead><tr>
                <th>ID</th><th>Usuário</th><th>Tenant</th><th>Endpoint (fim)</th><th>Criado</th><th>Ação</th>
            </tr></thead>
            <tbody>
            <?php foreach ($allSubs as $sub): ?>
            <tr>
                <td><?= $sub['id'] ?></td>
                <td><?= htmlspecialchars($sub['user_name'] ?? '—') ?><br><span style="color:#475569">#<?= $sub['user_id'] ?></span></td>
                <td><?= htmlspecialchars($sub['tenant_slug'] ?? '—') ?></td>
                <td class="endpoint-cell mono" title="<?= htmlspecialchars($sub['endpoint']) ?>">...<?= htmlspecialchars(substr($sub['endpoint'], -30)) ?></td>
                <td><?= substr($sub['created_at'] ?? '', 5, 11) ?></td>
                <td>
                    <button class="danger" style="padding:2px 8px;font-size:.6rem" onclick="deleteSubs(<?= $sub['user_id'] ?>)">Del</button>
                    <button style="padding:2px 8px;font-size:.6rem" onclick="sendTest(<?= $sub['user_id'] ?>)">Test</button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>

        <!-- Recent Notifications -->
        <h2 style="margin-top:14px">🔔 notifications (últimas 20)</h2>
        <?php if (empty($recentNotifications)): ?>
        <div class="card"><span class="status warn">Sem registros na tabela notifications</span></div>
        <?php else: ?>
        <div class="card" style="padding:0;overflow:hidden">
        <table>
            <thead><tr><th>ID</th><th>Usuário</th><th>Tipo</th><th>Canais</th><th>Prioridade</th><th>Lida</th><th>Enviada</th></tr></thead>
            <tbody>
            <?php foreach ($recentNotifications as $n): ?>
            <tr>
                <td><?= $n['id'] ?></td>
                <td><?= htmlspecialchars($n['user_name'] ?? '—') ?></td>
                <td class="mono" style="font-size:.6rem"><?= htmlspecialchars($n['type']) ?></td>
                <td class="mono" style="font-size:.6rem"><?= htmlspecialchars($n['channels'] ?? '—') ?></td>
                <td>
                    <?php if ($n['priority'] === 'critical'): ?>
                    <span class="tag tag-red">critical</span>
                    <?php else: ?>
                    <span class="tag tag-blue">normal</span>
                    <?php endif; ?>
                </td>
                <td><?= $n['read_at'] ? '<span class="tag tag-green">sim</span>' : '<span class="tag tag-yellow">não</span>' ?></td>
                <td><?= $n['sent_at'] ? '<span class="tag tag-green">sim</span>' : '<span class="tag tag-red">não</span>' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>

        <!-- Push Log -->
        <h2 style="margin-top:14px">📄 push.log (últimas 60 linhas)</h2>
        <?php if (empty($logLines)): ?>
        <div class="card"><span class="status warn">Log vazio ou inexistente: <?= htmlspecialchars($logFile) ?></span></div>
        <?php else: ?>
        <div style="background:#020617;border:1px solid #1e293b;border-radius:8px;padding:10px;max-height:280px;overflow-y:auto;font-size:.65rem;line-height:1.8">
        <?php foreach ($logLines as $line): ?>
            <?php
            $cls = 'log-info';
            if (str_contains($line, 'SUCCESS')) $cls = 'log-ok';
            elseif (str_contains($line, 'EXPIRED') || str_contains($line, 'FAILED')) $cls = 'log-error';
            elseif (str_contains($line, 'ERROR')) $cls = 'log-error';
            ?>
            <div class="<?= $cls ?>"><?= htmlspecialchars($line) ?></div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div><!-- /left panel -->

    <!-- ═══════════════ RIGHT PANEL — CLIENT SIDE ═══════════════ -->
    <div class="panel">

        <h2>🖥️ Browser / Client Status</h2>
        <div id="client-status">
            <div class="cs-item"><div class="cs-label">Service Worker</div><div class="cs-value" id="cs-sw" style="color:#475569">checking...</div></div>
            <div class="cs-item"><div class="cs-label">Push Permission</div><div class="cs-value" id="cs-perm" style="color:#475569">checking...</div></div>
            <div class="cs-item"><div class="cs-label">Subscription</div><div class="cs-value" id="cs-sub" style="color:#475569">checking...</div></div>
            <div class="cs-item"><div class="cs-label">SW Controller</div><div class="cs-value" id="cs-ctrl" style="color:#475569">checking...</div></div>
            <div class="cs-item"><div class="cs-label">SW Version</div><div class="cs-value" id="cs-swver" style="color:#475569">—</div></div>
            <div class="cs-item"><div class="cs-label">Endpoint Hash</div><div class="cs-value" id="cs-ep" style="color:#475569;font-size:.65rem">—</div></div>
        </div>

        <div class="card" id="sub-detail" style="display:none">
            <div class="card-title">Subscription Detail</div>
            <pre id="sub-json" style="font-size:.6rem;color:#94a3b8;white-space:pre-wrap;word-break:break-all"></pre>
        </div>

        <div class="sep"></div>

        <h2>🚀 Controles de Teste</h2>
        <div class="actions">
            <input type="number" id="test-user-id" placeholder="User ID" value="<?= $sessionUser['id'] ?? '' ?>">
            <button onclick="sendTest()">📤 Send Normal Push</button>
            <button class="warn" onclick="sendCritical()">🆘 Send Critical</button>
            <button class="danger" onclick="deleteSubs()">🗑️ Delete Subs (DB)</button>
        </div>
        <div class="actions">
            <button onclick="doSubscribe()">🔔 Subscribe Browser</button>
            <button onclick="doUnsubscribe()">🔕 Unsubscribe Browser</button>
            <button onclick="refreshStatus()">🔄 Refresh Status</button>
            <button onclick="clearLog()">🧹 Limpar Log</button>
        </div>

        <div class="sep"></div>

        <h2>📡 Live Event Log <span style="font-size:.6rem;color:#475569">(SW messages, push events, erros)</span></h2>
        <div id="live-log"></div>
        <div class="scroll-anchor" id="log-anchor"></div>

        <div class="sep"></div>

        <h2>🌐 Network Monitor (push endpoints)</h2>
        <div id="network-log" style="background:#020617;border:1px solid #1e293b;border-radius:8px;padding:10px;max-height:160px;overflow-y:auto;font-size:.65rem;line-height:1.8">
            <div style="color:#334155">Aguardando chamadas de rede...</div>
        </div>

        <div class="sep"></div>

        <h2>📋 push.log em tempo real</h2>
        <div id="server-log" style="background:#020617;border:1px solid #1e293b;border-radius:8px;padding:10px;max-height:200px;overflow-y:auto;font-size:.65rem;line-height:1.8">
            <div style="color:#334155">Aguardando...</div>
        </div>
        <div style="margin-top:6px">
            <button onclick="fetchServerLog()" style="font-size:.65rem;padding:4px 10px">🔄 Atualizar log do servidor</button>
            <label style="color:#475569;font-size:.65rem;margin-left:10px">
                <input type="checkbox" id="auto-refresh-log" onchange="toggleAutoLog(this.checked)"> Auto-refresh 5s
            </label>
        </div>

    </div><!-- /right panel -->

</div><!-- /layout -->

<script>
// ══════════════════════════════════════════════
// LIVE LOG SYSTEM
// ══════════════════════════════════════════════
const logEl = document.getElementById('live-log');

function ts() {
    return new Date().toTimeString().slice(0, 8);
}

function log(msg, type = 'info') {
    const line = document.createElement('div');
    line.className = 'log-' + type;
    line.innerHTML = `<span class="log-ts">[${ts()}]</span>${escHtml(msg)}`;
    logEl.appendChild(line);
    logEl.scrollTop = logEl.scrollHeight;
}

function clearLog() { logEl.innerHTML = ''; }

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ══════════════════════════════════════════════
// NETWORK MONITOR
// ══════════════════════════════════════════════
const networkEl = document.getElementById('network-log');
const PUSH_KEYWORDS = ['push', 'subscribe', 'notification', 'sos'];

const origFetch = window.fetch;
window.fetch = async function(url, opts) {
    const urlStr = String(url);
    const isPushRelated = PUSH_KEYWORDS.some(k => urlStr.toLowerCase().includes(k));

    if (isPushRelated) {
        const method = opts?.method || 'GET';
        const line = document.createElement('div');
        line.style.color = '#64748b';
        line.textContent = `[${ts()}] ${method} ${urlStr.slice(0, 80)}`;
        networkEl.firstChild?.remove();
        networkEl.appendChild(line);
        networkEl.scrollTop = networkEl.scrollHeight;

        try {
            const res = await origFetch.apply(this, arguments);
            const clone = res.clone();
            const statusColor = res.ok ? '#10b981' : '#ef4444';
            line.innerHTML = `<span style="color:${statusColor};font-weight:700">[${ts()}] ${res.status} ${method}</span> ${escHtml(urlStr.slice(0, 70))}`;

            // Try to read body for diagnostic output
            try {
                const body = await clone.json();
                const detail = document.createElement('div');
                detail.style.color = '#475569';
                detail.style.paddingLeft = '12px';
                detail.textContent = '  → ' + JSON.stringify(body).slice(0, 120);
                networkEl.appendChild(detail);
            } catch {}
            networkEl.scrollTop = networkEl.scrollHeight;
            return res;
        } catch (e) {
            line.innerHTML = `<span style="color:#ef4444;font-weight:700">[${ts()}] FAIL ${method}</span> ${escHtml(urlStr.slice(0, 70))} — ${escHtml(e.message)}`;
            throw e;
        }
    }
    return origFetch.apply(this, arguments);
};

// ══════════════════════════════════════════════
// CONSOLE CAPTURE
// ══════════════════════════════════════════════
['log', 'warn', 'error'].forEach(level => {
    const orig = console[level];
    console[level] = function(...args) {
        orig.apply(console, args);
        const msg = args.map(a => typeof a === 'object' ? JSON.stringify(a) : String(a)).join(' ');
        // Only forward push-related messages
        if (PUSH_KEYWORDS.some(k => msg.toLowerCase().includes(k)) || level === 'error') {
            const type = level === 'error' ? 'error' : level === 'warn' ? 'warn' : 'info';
            log('[console.' + level + '] ' + msg, type);
        }
    };
});

window.onerror = (msg, src, line) => {
    log(`[JS Error] ${msg} @ ${src}:${line}`, 'error');
};

window.onunhandledrejection = (e) => {
    log(`[Unhandled Promise] ${e.reason}`, 'error');
};

// ══════════════════════════════════════════════
// SERVICE WORKER LISTENER (before init)
// ══════════════════════════════════════════════
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', event => {
        const d = event.data;
        if (d?.type === 'PUSH_NOTIFICATION') {
            log(`🔔 PUSH RECEBIDO! title="${d.data?.title}" body="${d.data?.body}" priority="${d.priority}" notification_id="${d.data?.data?.notification_id}"`, 'push');
        } else {
            log(`[SW message] type=${d?.type} data=${JSON.stringify(d).slice(0,120)}`, 'info');
        }
    });

    navigator.serviceWorker.addEventListener('controllerchange', () => {
        log('[SW] Controller mudou (novo SW assumiu controle)', 'warn');
        refreshStatus();
    });
}

// ══════════════════════════════════════════════
// STATUS CHECK
// ══════════════════════════════════════════════
async function refreshStatus() {
    log('Verificando status do cliente...', 'info');

    // SW support
    const swSupport = 'serviceWorker' in navigator;
    const pushSupport = 'PushManager' in window;
    document.getElementById('cs-sw').textContent = swSupport ? (pushSupport ? 'Supported ✓' : 'SW ok / No PushManager') : 'NOT SUPPORTED';
    document.getElementById('cs-sw').style.color = (swSupport && pushSupport) ? '#10b981' : '#ef4444';

    // Permission
    const perm = 'Notification' in window ? Notification.permission : 'unsupported';
    const permEl = document.getElementById('cs-perm');
    permEl.textContent = perm;
    permEl.style.color = perm === 'granted' ? '#10b981' : perm === 'denied' ? '#ef4444' : '#f59e0b';
    log(`Permission: ${perm}`, perm === 'granted' ? 'ok' : perm === 'denied' ? 'error' : 'warn');

    // SW registration
    if (!swSupport) return;

    try {
        const reg = await navigator.serviceWorker.getRegistration('/');
        if (reg) {
            const swState = reg.active?.state || reg.installing?.state || reg.waiting?.state || 'unknown';
            log(`SW Registration: scope=${reg.scope} state=${swState}`, 'ok');
            document.getElementById('cs-swver').textContent = swState;

            const ctrl = navigator.serviceWorker.controller;
            document.getElementById('cs-ctrl').textContent = ctrl ? ctrl.state : 'none (uncontrolled)';
            document.getElementById('cs-ctrl').style.color = ctrl ? '#10b981' : '#f59e0b';

            // Ask SW for its version
            if (ctrl) {
                const mc = new MessageChannel();
                mc.port1.onmessage = e => {
                    if (e.data?.version) {
                        document.getElementById('cs-swver').textContent = e.data.version;
                        log(`SW version: ${e.data.version}`, 'ok');
                    }
                };
                ctrl.postMessage({ type: 'GET_VERSION' }, [mc.port2]);
            }

            // Subscription
            const sub = await reg.pushManager.getSubscription();
            const subEl = document.getElementById('cs-sub');
            if (sub) {
                subEl.textContent = 'Active ✓';
                subEl.style.color = '#10b981';
                const epEnd = sub.endpoint.slice(-20);
                document.getElementById('cs-ep').textContent = '...' + epEnd;
                document.getElementById('sub-detail').style.display = 'block';
                document.getElementById('sub-json').textContent = JSON.stringify({
                    endpoint: sub.endpoint,
                    expirationTime: sub.expirationTime,
                    keys: sub.toJSON().keys
                }, null, 2);
                log(`Subscription ativa: ...${epEnd}`, 'ok');
            } else {
                subEl.textContent = 'Nenhuma';
                subEl.style.color = '#ef4444';
                document.getElementById('cs-ep').textContent = '—';
                document.getElementById('sub-detail').style.display = 'none';
                log('Nenhuma subscription ativa no browser', 'warn');
            }
        } else {
            log('Nenhum SW registrado para /', 'warn');
            document.getElementById('cs-sw').textContent = 'No Registration';
            document.getElementById('cs-sw').style.color = '#f59e0b';
        }
    } catch (e) {
        log(`Erro ao verificar SW: ${e.message}`, 'error');
    }
}

// ══════════════════════════════════════════════
// SUBSCRIBE / UNSUBSCRIBE
// ══════════════════════════════════════════════
const VAPID_PUBLIC = <?= json_encode($vapidPublic) ?>;

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) outputArray[i] = rawData.charCodeAt(i);
    return outputArray;
}

async function doSubscribe() {
    log('Iniciando subscribe...', 'info');
    try {
        const perm = await Notification.requestPermission();
        log(`Permission result: ${perm}`, perm === 'granted' ? 'ok' : 'error');
        if (perm !== 'granted') return;

        const reg = await navigator.serviceWorker.ready;
        log(`SW ready, scope: ${reg.scope}`, 'ok');

        // Don't unsubscribe first — just reuse or create
        let sub = await reg.pushManager.getSubscription();
        if (sub) {
            log(`Subscription existente reutilizada: ...${sub.endpoint.slice(-20)}`, 'ok');
        } else {
            sub = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC)
            });
            log(`Nova subscription criada: ...${sub.endpoint.slice(-20)}`, 'ok');
        }

        // Determine endpoint from current URL or manual input
        const slug = prompt('Slug do tenant (ex: meu-clube):', '');
        if (!slug) { log('Cancelado (sem slug)', 'warn'); return; }
        const apiUrl = `/${slug}/api/push/subscribe`;

        log(`Enviando para servidor: ${apiUrl}`, 'info');
        const res = await fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(sub),
            credentials: 'include'
        });
        const json = await res.json();
        log(`Servidor respondeu: ${JSON.stringify(json)}`, res.ok ? 'ok' : 'error');
        refreshStatus();
    } catch (e) {
        log(`Subscribe falhou: ${e.message}`, 'error');
    }
}

async function doUnsubscribe() {
    log('Iniciando unsubscribe...', 'warn');
    try {
        const reg = await navigator.serviceWorker.getRegistration('/');
        if (!reg) { log('Nenhum SW registrado', 'error'); return; }
        const sub = await reg.pushManager.getSubscription();
        if (!sub) { log('Nenhuma subscription para remover', 'warn'); return; }
        const ok = await sub.unsubscribe();
        log(`Unsubscribe: ${ok ? 'SUCESSO' : 'FALHOU'}`, ok ? 'ok' : 'error');
        refreshStatus();
    } catch (e) {
        log(`Unsubscribe falhou: ${e.message}`, 'error');
    }
}

// ══════════════════════════════════════════════
// SERVER TEST ACTIONS
// ══════════════════════════════════════════════
function getUserId() {
    return document.getElementById('test-user-id').value;
}

async function sendTest(userId) {
    const uid = userId || getUserId();
    if (!uid) { log('Informe um User ID', 'warn'); return; }
    log(`Enviando push normal para user ${uid}...`, 'info');
    try {
        const fd = new FormData();
        fd.append('user_id', uid);
        const res = await origFetch('?action=send_test', { method: 'POST', body: fd });
        const data = await res.json();
        log(`Servidor: ${JSON.stringify(data)}`, data.ok ? 'push' : 'error');
        setTimeout(fetchServerLog, 2000);
    } catch (e) {
        log(`Erro: ${e.message}`, 'error');
    }
}

async function sendCritical(userId) {
    const uid = userId || getUserId();
    if (!uid) { log('Informe um User ID', 'warn'); return; }
    log(`Enviando push CRITICAL para user ${uid}...`, 'warn');
    try {
        const fd = new FormData();
        fd.append('user_id', uid);
        const res = await origFetch('?action=send_critical', { method: 'POST', body: fd });
        const data = await res.json();
        log(`Servidor: ${JSON.stringify(data)}`, data.ok ? 'push' : 'error');
        setTimeout(fetchServerLog, 2000);
    } catch (e) {
        log(`Erro: ${e.message}`, 'error');
    }
}

async function deleteSubs(userId) {
    const uid = userId || getUserId();
    if (!uid) { log('Informe um User ID', 'warn'); return; }
    if (!confirm(`Deletar TODAS as subscriptions do user ${uid} do banco?`)) return;
    log(`Deletando subscriptions do user ${uid}...`, 'warn');
    try {
        const fd = new FormData();
        fd.append('user_id', uid);
        const res = await origFetch('?action=delete_subs', { method: 'POST', body: fd });
        const data = await res.json();
        log(`Servidor: ${JSON.stringify(data)}`, data.ok ? 'ok' : 'error');
        setTimeout(() => location.reload(), 1000);
    } catch (e) {
        log(`Erro: ${e.message}`, 'error');
    }
}

// ══════════════════════════════════════════════
// SERVER LOG POLLING
// ══════════════════════════════════════════════
const serverLogEl = document.getElementById('server-log');
let autoLogTimer = null;

async function fetchServerLog() {
    try {
        const res = await origFetch('?action=log', { method: 'POST' });
        const data = await res.json();
        serverLogEl.innerHTML = '';
        (data.lines || []).forEach(line => {
            const div = document.createElement('div');
            div.style.color = line.includes('SUCCESS') ? '#10b981' :
                              line.includes('EXPIRED') || line.includes('FAILED') || line.includes('ERROR') ? '#ef4444' :
                              '#64748b';
            div.textContent = line;
            serverLogEl.appendChild(div);
        });
        serverLogEl.scrollTop = serverLogEl.scrollHeight;
    } catch (e) { /* silent */ }
}

function toggleAutoLog(on) {
    clearInterval(autoLogTimer);
    if (on) autoLogTimer = setInterval(fetchServerLog, 5000);
}

// ══════════════════════════════════════════════
// LISTEN FOR CUSTOM EVENTS (from push-notifications.js)
// ══════════════════════════════════════════════
document.addEventListener('push-notification-received', e => {
    log(`🎯 Evento "push-notification-received" disparado: ${JSON.stringify(e.detail).slice(0,120)}`, 'push');
});

// ══════════════════════════════════════════════
// INIT
// ══════════════════════════════════════════════
log('Página de diagnóstico carregada. Iniciando verificações...', 'info');

// Register SW if not already (diagnostic mode)
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js')
        .then(reg => {
            log(`SW registrado: scope=${reg.scope} installing=${!!reg.installing} waiting=${!!reg.waiting} active=${!!reg.active}`, 'ok');
            return reg.update();
        })
        .then(() => {
            log('SW update check concluído', 'ok');
            refreshStatus();
        })
        .catch(e => log(`SW registration failed: ${e.message}`, 'error'));
} else {
    log('Service Workers NÃO suportados neste browser', 'error');
}

fetchServerLog();
</script>
</body>
</html>
