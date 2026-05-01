<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error</title>
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .box { background: rgba(255,255,255,0.05); padding: 2rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.1); max-width: 600px; }
        h1 { color: #8b5cf6; margin-top: 0; }
        pre { background: #000; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="box">
        <h1>An Error Occurred</h1>
        <p><?= esc($message) ?></p>
        <?php if (defined('ENVIRONMENT') && ENVIRONMENT === 'development'): ?>
            <pre><?= esc($exception->getFile()) ?>:<?= esc($exception->getLine()) ?></pre>
            <pre><?= esc($exception->getTraceAsString()) ?></pre>
        <?php endif; ?>
        <a href="/" style="color: #6366f1;">Return Home</a>
    </div>
</body>
</html>
