@echo off
echo ========================================
echo   Verificando arquivos para deploy
echo ========================================
echo.

echo Verificando arquivos HTML...
if exist "index.html" (
    echo [OK] index.html encontrado
) else (
    echo [ERRO] index.html NAO encontrado!
)

if exist "pracas.html" (
    echo [OK] pracas.html encontrado
) else (
    echo [ERRO] pracas.html NAO encontrado!
)

if exist "apresenta.html" (
    echo [OK] apresenta.html encontrado
) else (
    echo [AVISO] apresenta.html nao encontrado (opcional)
)

echo.
echo Verificando arquivos de logo...
if exist "LOGO.svg" (
    echo [OK] LOGO.svg encontrado
) else (
    echo [ERRO] LOGO.svg NAO encontrado!
)

if exist "LOGO-black.svg" (
    echo [OK] LOGO-black.svg encontrado
) else (
    echo [AVISO] LOGO-black.svg nao encontrado (opcional)
)

if exist "new-logo.svg" (
    echo [OK] new-logo.svg encontrado
) else (
    echo [AVISO] new-logo.svg nao encontrado (opcional)
)

echo.
echo Verificando arquivos de configuracao...
if exist "vercel.json" (
    echo [OK] vercel.json encontrado
) else (
    echo [AVISO] vercel.json nao encontrado (opcional)
)

if exist "package.json" (
    echo [OK] package.json encontrado
) else (
    echo [AVISO] package.json nao encontrado (opcional)
)

if exist ".vercelignore" (
    echo [OK] .vercelignore encontrado
) else (
    echo [ERRO] .vercelignore NAO encontrado!
)

echo.
echo ========================================
echo   Verificacao concluida!
echo ========================================
echo.
pause
