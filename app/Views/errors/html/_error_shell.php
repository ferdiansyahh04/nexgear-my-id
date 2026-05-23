<?php
/**
 * Standalone error-page shell. We do NOT extend layouts/main.php so that
 * runtime exceptions don't recurse through the layout (which itself
 * touches the database via partials like nav/offcanvas_cart).
 *
 * Variables:
 *   $code    (string) — big numeric/code label, e.g. "404"
 *   $title   (string) — short heading
 *   $message (string) — supporting copy
 *   $extra   (string|null) — optional raw HTML appended below copy
 */
$code    = $code    ?? 'ERR';
$title   = $title   ?? 'Something Went Wrong';
$message = $message ?? 'An unexpected error occurred.';
$extra   = $extra   ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?> | NexGear Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@400;500;700&family=Cormorant+Garamond:ital,wght@0,400;1,400&display=swap" rel="stylesheet">
    <script nonce="{csp-script-nonce}">
        (function() {
            try {
                var saved = localStorage.getItem('nexgear_theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = saved || (prefersDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) { /* ignore */ }
        })();
    </script>
    <style>
        :root {
            --bg: #f2f2f2;
            --panel: #fff;
            --ink: #000;
            --muted: #666;
            --border: #000;
            --accent: #d4ff37;
        }
        [data-theme="dark"] {
            --bg: #0d0d0d;
            --panel: #161616;
            --ink: #f5f5f5;
            --muted: #888;
            --border: #2a2a2a;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--ink);
            font-family: 'Inter', -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            line-height: 1.6;
        }
        .err-shell {
            background: var(--panel);
            border: 1px solid var(--border);
            box-shadow: 8px 8px 0 var(--border);
            max-width: 720px;
            width: 100%;
        }
        .err-header {
            border-bottom: 1px solid var(--border);
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .err-brand {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--ink);
            text-decoration: none;
        }
        .err-eyebrow {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .err-body {
            padding: 3rem 2rem;
        }
        .err-code {
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-size: clamp(5rem, 14vw, 9rem);
            line-height: 1;
            letter-spacing: -0.04em;
            color: var(--ink);
            margin: 0 0 0.5rem 0;
        }
        .err-code::after {
            content: '';
            display: block;
            width: 88px;
            height: 6px;
            background: var(--accent);
            margin-top: 8px;
        }
        .err-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(1.5rem, 3vw, 2.25rem);
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: -0.03em;
            text-transform: uppercase;
            margin: 1.25rem 0 1rem 0;
        }
        .err-message {
            font-size: 1rem;
            color: var(--muted);
            max-width: 480px;
            margin-bottom: 2rem;
        }
        .err-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 2rem;
        }
        .err-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--ink);
            color: var(--bg);
            border: 1px solid var(--ink);
            padding: 14px 24px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            text-decoration: none;
            transition: background 0.15s ease, color 0.15s ease;
            cursor: pointer;
        }
        .err-btn:hover { background: transparent; color: var(--ink); }
        .err-btn-secondary {
            background: transparent;
            color: var(--ink);
        }
        .err-btn-secondary:hover { background: var(--ink); color: var(--bg); }

        .err-trace {
            margin-top: 2rem;
            border-top: 1px solid var(--border);
            padding-top: 1.5rem;
        }
        .err-trace-label {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 0.75rem;
        }
        .err-trace pre {
            background: var(--bg);
            border: 1px solid var(--border);
            padding: 12px 14px;
            font-family: 'JetBrains Mono', Consolas, monospace;
            font-size: 0.75rem;
            line-height: 1.5;
            margin: 0 0 12px 0;
            white-space: pre-wrap;
            word-break: break-word;
            max-height: 260px;
            overflow: auto;
            color: var(--ink);
        }
    </style>
</head>
<body>
    <main class="err-shell">
        <header class="err-header">
            <a href="<?= site_url('/') ?>" class="err-brand">NEXGEAR</a>
            <span class="err-eyebrow">Error Surface</span>
        </header>
        <section class="err-body">
            <h1 class="err-code"><?= esc($code) ?></h1>
            <h2 class="err-title"><?= esc($title) ?></h2>
            <p class="err-message"><?= esc($message) ?></p>
            <?= $extra ?>
            <div class="err-actions">
                <a href="<?= site_url('/') ?>" class="err-btn">Return Home <span>→</span></a>
                <a href="<?= site_url('/products') ?>" class="err-btn err-btn-secondary">Browse Collection</a>
            </div>
        </section>
    </main>
</body>
</html>
