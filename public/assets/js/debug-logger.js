/**
 * Debug Logger - Diagnóstico de Frontend
 * Captura erros, logs e eventos do HTMX para exibição na tela.
 */
(function () {
    // Config
    const MAX_LOGS = 50;

    // UI Setup
    const styles = `
        #debug-console {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 300px;
            background: rgba(15, 23, 42, 0.95);
            color: #f1f5f9;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            border-top: 2px solid #334155;
            transition: transform 0.3s ease;
            transform: translateY(100%);
        }
        #debug-console.open {
            transform: translateY(0);
        }
        #debug-header {
            padding: 8px 16px;
            background: #1e293b;
            border-bottom: 1px solid #334155;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }
        #debug-toggle {
            position: fixed;
            bottom: 10px;
            left: 10px;
            z-index: 100000;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #debug-content {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }
        .log-entry {
            margin-bottom: 4px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 4px;
            word-break: break-all;
        }
        .log-time { color: #94a3b8; margin-right: 8px; }
        .log-type-info { color: #3b82f6; }
        .log-type-warn { color: #f59e0b; }
        .log-type-error { color: #ef4444; font-weight: bold; background: rgba(239,68,68,0.1); padding: 2px; }
        .log-type-htmx { color: #10b981; }
    `;

    const styleEl = document.createElement('style');
    styleEl.textContent = styles;
    document.head.appendChild(styleEl);

    // Create Container
    const container = document.createElement('div');
    container.id = 'debug-console';
    container.innerHTML = `
        <div id="debug-header">
            <span>🔧 Diagnóstico do Sistema (Logs)</span>
            <div>
                <button onclick="document.getElementById('debug-content').innerHTML=''" style="padding:4px 8px; cursor:pointer;">Limpar</button>
                <button onclick="document.getElementById('debug-console').classList.remove('open')" style="padding:4px 8px; cursor:pointer;">Fechar</button>
            </div>
        </div>
        <div id="debug-content"></div>
    `;
    document.body.appendChild(container);

    // Toggle Button
    const btn = document.createElement('button');
    btn.id = 'debug-toggle';
    btn.textContent = '🐛';
    btn.onclick = () => container.classList.toggle('open');
    document.body.appendChild(btn);

    const logContent = document.getElementById('debug-content');

    function addLog(type, args) {
        const entry = document.createElement('div');
        entry.className = `log-entry`;

        const time = new Date().toLocaleTimeString();
        const msg = Array.from(args).map(a => {
            if (typeof a === 'object') {
                try { return JSON.stringify(a); } catch (e) { return '[Object]'; }
            }
            return String(a);
        }).join(' ');

        entry.innerHTML = `
            <span class="log-time">[${time}]</span>
            <span class="log-type-${type}">[${type.toUpperCase()}]</span>
            <span>${msg}</span>
        `;

        logContent.appendChild(entry);
        logContent.scrollTop = logContent.scrollHeight;

        // Visual feedback on button if error
        if (type === 'error') {
            btn.style.backgroundColor = '#b91c1c';
            btn.classList.add('pulse');
        }
    }

    // Intercept Console
    const originalLog = console.log;
    const originalWarn = console.warn;
    const originalError = console.error;

    console.log = function () { addLog('info', arguments); originalLog.apply(console, arguments); };
    console.warn = function () { addLog('warn', arguments); originalWarn.apply(console, arguments); };
    console.error = function () { addLog('error', arguments); originalError.apply(console, arguments); };

    // Capture Global Errors
    window.onerror = function (msg, url, line, col, error) {
        addLog('error', [`Global Error: ${msg} at ${url}:${line}:${col}`]);
        return false;
    };

    // Capture HTMX Events
    if (window.htmx) {
        htmx.on('htmx:afterRequest', function (evt) {
            const status = evt.detail.xhr.status;
            const type = status >= 400 ? 'error' : 'htmx';
            addLog(type, [`HTMX Req Finished: ${evt.detail.requestConfig.path} [${status}]`]);
        });

        htmx.on('htmx:responseError', function (evt) {
            addLog('error', [`HTMX Error: ${evt.detail.xhr.responseText || 'Connection Error'}`]);
        });

        htmx.on('htmx:sendError', function (evt) {
            addLog('error', ['HTMX Network Error (Failed to send)']);
        });

        // Debug DOM swaps
        htmx.on('htmx:swapError', function (evt) {
            addLog('error', [`HTMX Swap Error: ${evt.detail.xhr.responseText}`]);
        });
    }

    addLog('info', ['Debug Logger Inicializado. Monitorando...']);

})();
