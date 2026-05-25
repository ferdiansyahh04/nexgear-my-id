# Strip UTF-8 BOM from PHP files that PowerShell's Set-Content polluted.
# Idempotent — files without BOM are left unchanged.
param(
    [Parameter(Mandatory)] [string[]] $Path
)

foreach ($p in $Path) {
    if (-not (Test-Path $p)) {
        Write-Host "[skip] not found: $p"
        continue
    }
    $bytes = [System.IO.File]::ReadAllBytes($p)
    if ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) {
        $stripped = $bytes[3..($bytes.Length - 1)]
        [System.IO.File]::WriteAllBytes($p, $stripped)
        Write-Host "[OK] stripped BOM from $p"
    } else {
        Write-Host "[skip] no BOM in $p"
    }
}
