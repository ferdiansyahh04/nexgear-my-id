<?php
/**
 * Brutalist-aware email shell. Email clients are picky — we keep it inline,
 * table-free where possible, and fall back to safe web fonts.
 *
 * Variables expected:
 *   $title       (string) — used in <title> + preview header
 *   $heading     (string) — big H1 below the brand
 *   $intro       (string) — supporting copy above $body
 *   $body        (string) — pre-rendered HTML block (may contain tables)
 *   $cta         (?array) — ['label' => string, 'url' => string] optional
 *   $footnote    (?string) — small text below CTA
 */
$title    = $title    ?? 'NexGear Store';
$heading  = $heading  ?? 'Hello';
$intro    = $intro    ?? '';
$body     = $body     ?? '';
$cta      = $cta      ?? null;
$footnote = $footnote ?? null;
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= esc($title) ?></title>
</head>
<body style="margin:0;padding:0;background:#f2f2f2;font-family:Helvetica,Arial,sans-serif;color:#000;">
  <div style="max-width:600px;margin:24px auto;background:#ffffff;border:1px solid #000;">
    <div style="padding:18px 24px;border-bottom:1px solid #000;">
      <span style="font-family:Helvetica,Arial,sans-serif;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;font-size:13pt;">NEXGEAR</span>
      <span style="float:right;font-size:9pt;letter-spacing:0.15em;text-transform:uppercase;color:#666;">Store Notice</span>
    </div>
    <div style="padding:32px 24px;">
      <h1 style="margin:0 0 12px 0;font-size:22pt;line-height:1.15;letter-spacing:-0.02em;text-transform:uppercase;font-weight:700;">
        <?= esc($heading) ?>
      </h1>
      <?php if ($intro !== ''): ?>
        <p style="margin:0 0 18px 0;font-size:11pt;line-height:1.6;color:#333;"><?= $intro /* allow inline tags */ ?></p>
      <?php endif; ?>
      <div style="font-size:11pt;line-height:1.6;color:#000;"><?= $body ?></div>

      <?php if ($cta && ! empty($cta['url']) && ! empty($cta['label'])): ?>
        <p style="margin:32px 0 0 0;">
          <a href="<?= esc($cta['url'], 'attr') ?>"
             style="display:inline-block;background:#000;color:#fff;padding:14px 22px;font-family:Helvetica,Arial,sans-serif;font-weight:700;text-transform:uppercase;font-size:10pt;letter-spacing:0.12em;text-decoration:none;border:1px solid #000;">
            <?= esc($cta['label']) ?> &nbsp;→
          </a>
        </p>
      <?php endif; ?>

      <?php if ($footnote): ?>
        <p style="margin:24px 0 0 0;font-size:9pt;color:#777;font-style:italic;"><?= $footnote ?></p>
      <?php endif; ?>
    </div>
    <div style="padding:18px 24px;border-top:1px solid #000;font-size:9pt;color:#666;letter-spacing:0.05em;">
      You received this because you have an account at NexGear Store.<br>
      Questions? <a href="mailto:hello@nexgear.my.id" style="color:#000;">hello@nexgear.my.id</a>
    </div>
  </div>
</body>
</html>
