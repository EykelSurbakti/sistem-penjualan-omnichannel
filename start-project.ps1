# Start MariaDB server if not running
$mysqldRunning = Get-Process mysqld -ErrorAction SilentlyContinue
if (-not $mysqldRunning) {
    Write-Host "Starting MariaDB Database Server..." -ForegroundColor Cyan
    $p = New-Object System.Diagnostics.Process
    $p.StartInfo.FileName = "C:\xampp\mysql\bin\mysqld.exe"
    $p.StartInfo.Arguments = "--defaults-file=C:\xampp\mysql\bin\my.ini"
    $p.StartInfo.UseShellExecute = $true
    $p.StartInfo.WindowStyle = [System.Diagnostics.ProcessWindowStyle]::Hidden
    $p.Start() | Out-Null
    Start-Sleep -Seconds 2
} else {
    Write-Host "MariaDB Database Server is already running." -ForegroundColor Green
}

Write-Host "Starting Laravel 11 Development Server at http://127.0.0.1:8000 ..." -ForegroundColor Cyan
Write-Host "Admin Panel Dashboard: http://127.0.0.1:8000/admin" -ForegroundColor Yellow
Write-Host "Login Email: admin@iseller.local | Password: password" -ForegroundColor Green

& "C:\Users\eykel\.gemini\antigravity\scratch\php83\php.exe" artisan serve --port=8000
