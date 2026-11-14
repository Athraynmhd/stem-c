# STEM-C USK - hentikan listener PHP FastCGI yang dijalankan start-php-fcgi.ps1.
# Berkas ini sengaja ASCII saja (lihat catatan di start-php-fcgi.ps1).

param(
    # 9102-9104 tetap disapu supaya sisa percobaan balancer ikut mati.
    [int[]] $Ports = @(9101, 9102, 9103, 9104)
)

$pidFile = Join-Path $PSScriptRoot 'php-fcgi.pids'
$targets = @()

if (Test-Path $pidFile) {
    $targets += Get-Content $pidFile | Where-Object { $_ -match '^\d+$' } | ForEach-Object { [int] $_ }
}

# Cadangan: ambil dari port kalau berkas PID hilang atau tidak sinkron.
foreach ($port in $Ports) {
    $listening = Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue

    if ($listening) {
        $targets += $listening.OwningProcess
    }
}

$stopped = 0

foreach ($processId in ($targets | Sort-Object -Unique)) {
    $process = Get-Process -Id $processId -ErrorAction SilentlyContinue

    if ($process -and $process.ProcessName -eq 'php-cgi') {
        Stop-Process -Id $processId -Force
        Write-Host "Listener PID $processId dihentikan."
        $stopped += 1
    }
}

if ($stopped -eq 0) {
    Write-Host 'Tidak ada listener php-cgi yang berjalan.'
}

Remove-Item $pidFile -ErrorAction SilentlyContinue
