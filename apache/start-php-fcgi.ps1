# STEM-C USK - jalankan listener PHP FastCGI persisten.
#
# Kenapa perlu: Apache XAMPP di mesin ini tidak punya mod_fcgid, jadi Apache
# tidak bisa mengelola proses PHP sendiri. Tanpa proses persisten, setiap
# request memakai php-cgi.exe baru dan OPcache tidak pernah panas - satu
# halaman Laravel sempat butuh ~900 ms.
#
# Satu listener saja: php-cgi.exe di Windows melayani satu request sekaligus,
# dan upaya membagi beban lewat balancer:// Apache gagal di SCRIPT_FILENAME
# (lihat catatan di stemc-local.conf). Untuk satu pengembang ini cukup.
#
# Catatan: berkas ini sengaja ASCII saja. PowerShell 5.1 membaca .ps1 tanpa BOM
# sebagai ANSI, sehingga karakter non-ASCII bisa merusak parsing.
#
# Pakai:
#   powershell -ExecutionPolicy Bypass -File apache\start-php-fcgi.ps1
#   powershell -ExecutionPolicy Bypass -File apache\stop-php-fcgi.ps1

param(
    [int[]] $Ports = @(9101),
    [string] $PhpDir = 'D:\USK\php84'
)

$ErrorActionPreference = 'Stop'

$phpCgi = Join-Path $PhpDir 'php-cgi.exe'

if (-not (Test-Path $phpCgi)) {
    throw "php-cgi.exe tidak ditemukan di $PhpDir"
}

# PHPRC memastikan php.ini yang dipakai milik PHP 8.4 portable, bukan php.ini
# XAMPP. MAX_REQUESTS 0 = proses tidak didaur ulang, supaya OPcache tetap panas.
$env:PHPRC = $PhpDir
$env:PHP_FCGI_MAX_REQUESTS = '0'

$pidFile = Join-Path $PSScriptRoot 'php-fcgi.pids'
$running = @()

foreach ($port in $Ports) {
    $listening = Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue

    if ($listening) {
        $owner = $listening[0].OwningProcess
        Write-Host "Port $port sudah dipakai (PID $owner), dilewati."
        $running += $owner
        continue
    }

    $process = Start-Process -FilePath $phpCgi -ArgumentList '-b', "127.0.0.1:$port" -WindowStyle Hidden -PassThru

    Start-Sleep -Milliseconds 400

    if ($process.HasExited) {
        Write-Warning "Listener di port $port langsung berhenti (cek php.ini)."
        continue
    }

    Write-Host "PHP FastCGI hidup di 127.0.0.1:$port (PID $($process.Id))"
    $running += $process.Id
}

$running | Sort-Object -Unique | Set-Content -Path $pidFile -Encoding ascii

Write-Host "PID tersimpan di $pidFile"
