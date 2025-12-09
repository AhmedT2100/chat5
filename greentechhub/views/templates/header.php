<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>GreenTechHub</title>

    <!-- CSS -->
    <link rel="stylesheet" href="/greentechhub/public/css/style.css">

    <!-- App JS (deferred so DOM is ready) -->
    <script src="/greentechhub/public/js/reclamation_validation.js" defer></script>

    <!--
      Immediate theme initializer (runs very early to avoid FOUC).
      It only sets data-theme on <html> so CSS picks the right theme before render.
    -->
    <script>
    (function() {
        try {
            var saved = localStorage.getItem('theme');
            if (saved === 'dark' || saved === 'light') {
                document.documentElement.setAttribute('data-theme', saved);
            } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        } catch (e) { /* ignore errors */ }
    })();
    </script>
</head>
<body>

<nav class="navbar">
    <div class="container">

        <a class="navbar-brand" href="/greentechhub/public/index.php">GreenTechHub</a>

        <ul class="navbar-nav">
            <?php if(empty($_SESSION['user_id'])): ?>
                <li><a href="/greentechhub/public/index.php?route=auth/login">Connectez</a></li>
            <?php else: ?>
                <li><a href="/greentechhub/public/index.php?route=reclamation">Mes réclamations</a></li>
                <?php if(!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li><a href="/greentechhub/public/index.php?route=admin/reclamation">Admin</a></li>
                <?php endif; ?>
                <li><a href="/greentechhub/public/index.php?route=auth/logout">Déconnexion</a></li>
            <?php endif; ?>
        </ul>

        <!-- THEME TOGGLE: placed outside the <ul> so script reliably finds it -->
        <div style="display:inline-block; margin-left:15px;">
            <button id="modeToggle" class="btn btn-outline" type="button" aria-pressed="false" title="Toggle theme">
                <!-- text will be replaced by JS on init -->
                🌙 Dark Mode
            </button>
        </div>

    </div>
</nav>

<div id="content" class="container">

<!--
  Theme toggle binder. This runs after DOMContentLoaded and is guaranteed to find the button.
  It updates <html data-theme="..."> and saves user preference to localStorage.
-->
<script>
(function() {
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        var btn = document.getElementById('modeToggle');
        if (btn) {
            btn.textContent = theme === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode';
            btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
        }
    }

    function init() {
        var btn = document.getElementById('modeToggle');

        // Ensure <html> has theme: prefer saved, otherwise respect current attribute (set by immediate script)
        var saved = null;
        try { saved = localStorage.getItem('theme'); } catch(e) { saved = null; }

        if (saved === 'dark' || saved === 'light') {
            applyTheme(saved);
        } else {
            // if no saved, keep whatever immediate initializer set, or fallback to light
            var current = document.documentElement.getAttribute('data-theme') || 'light';
            applyTheme(current);
        }

        if (!btn) return;

        btn.addEventListener('click', function() {
            var current = document.documentElement.getAttribute('data-theme') || 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            try { localStorage.setItem('theme', next); } catch (e) { /* ignore */ }
        });

        // If user has not chosen a theme, follow system changes automatically
        if (!saved && window.matchMedia) {
            var mq = window.matchMedia('(prefers-color-scheme: dark)');
            var listener = function(e) {
                // only auto-change if user didn't pick explicit theme
                if (!localStorage.getItem('theme')) {
                    applyTheme(e.matches ? 'dark' : 'light');
                }
            };
            if (typeof mq.addEventListener === 'function') mq.addEventListener('change', listener);
            else if (typeof mq.addListener === 'function') mq.addListener(listener);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
