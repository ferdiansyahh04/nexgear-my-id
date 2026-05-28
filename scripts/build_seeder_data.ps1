# Build a clean JSON the PHP seeder can consume:
#  - Strip HTML from body
#  - Trim long descriptions
#  - Force UTF-8 read/write to avoid mojibake
$ErrorActionPreference = 'Stop'

$raw = [System.IO.File]::ReadAllText('d:\nexgear-store\nexgear-my-id\products.json', [System.Text.Encoding]::UTF8)
$j = $raw | ConvertFrom-Json

$keyboards = @(
    'noir-spade-65',
    'noir-timeless65-65-multi-layout-wireless-mechanical-keyboard-gasket-mount',
    'noir-timeless82-v2-classic',
    'noir-timeless82-v2-75-wireless-mechanical-keyboard',
    'noir-timeless82-v2-special-edition-75-via-qmk-mechanical-keyboard-gasket-mount',
    'noir-timeless-1800',
    'noir-n1-x-65-wireless-via-mechanical-keyboard',
    'noir-spade65-qmk-via-65-multi-layout-aluminum-mechanical-keyboard-gasket-mount-copy',
    'noir-spade65-65-custom-build-multi-layout-aluminum-mechanical-keyboard-gasket-mount',
    'noir-timeless-he-wireless-mechanical-keyboard-hall-effect-1'
)
$mice = @(
    'noir-e1-mouse-ergonomic-wireless-lightweight-gaming-mouse-paw3311-with-charging-dock',
    'noir-s1-mouse-symmetric-wireless-lightweight-gaming-mouse-paw3311-with-charging-dock',
    'neo-melo-mouse-wireless-bluetooth-slim-silent-click-usb-c-charging',
    'noir-m1-nex-wireless-lightweight-gaming-mouse-pmw-3331',
    'noir-m2-wireless-ultra-lightweight-gaming-mouse-copy',
    'noir-m2-pro-wireless-ultra-lightweight-gaming-mouse',
    'noir-m1-lite-wireless-lightweight-gaming-mouse-pmw-3325-copy'
)

function StripHtml($html) {
    if ([string]::IsNullOrWhiteSpace($html)) { return '' }
    $t = $html -replace '<[^>]+>', ' '
    $t = $t -replace '&nbsp;', ' '
    $t = $t -replace '&amp;', '&'
    $t = $t -replace '&[a-zA-Z]+;', ' '
    $t = $t -replace '\s+', ' '
    return $t.Trim()
}

function CleanText($s) {
    if (-not $s) { return '' }
    # Replace common unicode oddities + flag emojis with safe ASCII
    $r = $s
    $r = $r -replace [char]0x2014, '-'   # em-dash
    $r = $r -replace [char]0x2013, '-'   # en-dash
    $r = $r -replace [char]0x2019, "'"   # right single quote
    $r = $r -replace [char]0x2018, "'"   # left single quote
    $r = $r -replace [char]0x201C, '"'   # left double quote
    $r = $r -replace [char]0x201D, '"'   # right double quote
    $r = $r -replace [char]0x00C2, ''    # stray Â
    $r = $r -replace [char]0x00A0, ' '   # nbsp
    # Strip non-BMP (emoji) by removing surrogate pairs
    $r = [regex]::Replace($r, '[\uD800-\uDBFF][\uDC00-\uDFFF]', '')
    return $r.Trim()
}

$wantHandles = $keyboards + $mice
$out = @()
foreach ($p in $j.products) {
    if (-not ($wantHandles -contains $p.handle)) { continue }
    $cat = if ($keyboards -contains $p.handle) { 'keyboards' } else { 'mice' }

    $title = CleanText $p.title

    $desc = CleanText (StripHtml $p.body_html)
    if ($desc.Length -gt 600) {
        $desc = $desc.Substring(0, 600).TrimEnd() + '...'
    }
    if ([string]::IsNullOrWhiteSpace($desc)) {
        $desc = "$title - sourced from Noir Gear's lineup."
    }

    $out += [PSCustomObject]@{
        handle      = $p.handle
        title       = $title
        category    = $cat
        price       = [decimal]$p.variants[0].price
        description = $desc
        image1_url  = $p.images[0].src
        image2_url  = if ($p.images.Count -ge 2) { $p.images[1].src } else { $null }
    }
}

$dest = 'd:\nexgear-store\nexgear-my-id\app\Database\Seeds\data'
if (-not (Test-Path $dest)) { New-Item -ItemType Directory -Path $dest -Force | Out-Null }
$json = $out | ConvertTo-Json -Depth 4
[System.IO.File]::WriteAllText((Join-Path $dest 'noirgear_kb_mouse.json'), $json, [System.Text.UTF8Encoding]::new($false))
"Wrote {0} products to noirgear_kb_mouse.json" -f $out.Count
