<?php
/**
 * Admin Footer Partial
 * Contains closing tags and common scripts
 */
?>
<script src="<?= asset_url('js/toast.js') ?>"></script>
<script src="<?= asset_url('js/pwa-install.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        console.log('[Theme] Initializing toggle logic...');

        // Elements
        const themeBtn = document.getElementById('theme-toggle');
        const iconDark = document.getElementById('theme-icon-dark');
        const iconLight = document.getElementById('theme-icon-light');
        const body = document.body;

        // 1. Initial State Check
        const savedTheme = localStorage.getItem('theme');
        console.log('[Theme] Saved:', savedTheme);

        if (savedTheme === 'dark') {
            body.classList.add('dark');
            if (iconDark) iconDark.style.display = 'none';
            if (iconLight) iconLight.style.display = 'block';
        } else {
            body.classList.remove('dark'); // Ensure clean state
            if (iconDark) iconDark.style.display = 'block';
            if (iconLight) iconLight.style.display = 'none';
        }

        // 2. Event Listener
        if (themeBtn) {
            console.log('[Theme] Button found, attaching listener.');

            // Remove any existing listeners by cloning (optional but safe)
            // const newBtn = themeBtn.cloneNode(true);
            // themeBtn.parentNode.replaceChild(newBtn, themeBtn);

            themeBtn.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent accidental form submits or jumps
                console.log('[Theme] Click detected');

                body.classList.toggle('dark');
                const isDark = body.classList.contains('dark');
                console.log('[Theme] New state isDark:', isDark);

                if (isDark) {
                    if (iconDark) iconDark.style.display = 'none';
                    if (iconLight) iconLight.style.display = 'block';
                    localStorage.setItem('theme', 'dark');
                } else {
                    if (iconDark) iconDark.style.display = 'block';
                    if (iconLight) iconLight.style.display = 'none';
                    localStorage.setItem('theme', 'light');
                }
            });
        } else {
            console.error('[Theme] CRITICAL: Toggle button #theme-toggle not found in DOM');
        }
    });
</script>