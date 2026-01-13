# Script para executar a migration do histórico de rotas
# Tenta encontrar o PHP e executar a migration

Write-Host "🔍 Procurando PHP..." -ForegroundColor Cyan

# Tenta caminhos comuns do PHP no Windows
$phpPaths = @(
    "php",
    "C:\php\php.exe",
    "C:\xampp\php\php.exe",
    "C:\wamp64\bin\php\php*\php.exe",
    "C:\laragon\bin\php\php*\php.exe",
    "$env:ProgramFiles\PHP\php.exe",
    "$env:ProgramFiles(x86)\PHP\php.exe"
)

$phpFound = $null

foreach ($path in $phpPaths) {
    try {
        if ($path -like "*\php*\php.exe") {
            # Para caminhos com wildcard, tenta encontrar
            $found = Get-ChildItem -Path (Split-Path $path) -Filter "php.exe" -ErrorAction SilentlyContinue | Select-Object -First 1
            if ($found) {
                $phpFound = $found.FullName
                break
            }
        } else {
            $result = & $path --version 2>&1
            if ($LASTEXITCODE -eq 0) {
                $phpFound = $path
                break
            }
        }
    } catch {
        continue
    }
}

if (-not $phpFound) {
    Write-Host "❌ PHP não encontrado no PATH ou nos caminhos comuns." -ForegroundColor Red
    Write-Host ""
    Write-Host "Por favor, execute manualmente:" -ForegroundColor Yellow
    Write-Host "  php artisan migrate" -ForegroundColor White
    Write-Host ""
    Write-Host "Ou adicione o PHP ao PATH do sistema." -ForegroundColor Yellow
    exit 1
}

Write-Host "✅ PHP encontrado: $phpFound" -ForegroundColor Green
Write-Host ""
Write-Host "🚀 Executando migration..." -ForegroundColor Cyan
Write-Host ""

# Navega para o diretório do projeto
$projectPath = Split-Path -Parent $PSScriptRoot
Set-Location $projectPath

# Executa a migration
& $phpFound artisan migrate

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✅ Migration executada com sucesso!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📊 Tabela 'driver_route_history' criada!" -ForegroundColor Cyan
    Write-Host "🎉 Sistema de histórico de rotas está pronto para uso!" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "❌ Erro ao executar migration. Verifique os logs acima." -ForegroundColor Red
    exit 1
}
