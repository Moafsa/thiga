@echo off
echo ========================================
echo   Deploy Thiga Transportes - Vercel
echo   Apenas index.html e pracas.html
echo ========================================
echo.

echo Verificando se Vercel CLI esta instalado...
vercel --version >nul 2>&1
if errorlevel 1 (
    echo Vercel CLI nao encontrado. Instalando...
    npm install -g vercel
    echo.
)

echo.
echo IMPORTANTE: Apenas os seguintes arquivos serao enviados:
echo   - index.html
echo   - pracas.html
echo   - apresenta.html
echo   - vercel.json
echo   - package.json
echo   - LOGO.svg
echo   - LOGO-black.svg
echo   - new-logo.svg
echo   - README.md
echo.

echo Verificando se os arquivos de logo existem...
if exist "LOGO.svg" (
    echo [OK] LOGO.svg encontrado
) else (
    echo [AVISO] LOGO.svg NAO encontrado!
)
if exist "LOGO-black.svg" (
    echo [OK] LOGO-black.svg encontrado
) else (
    echo [AVISO] LOGO-black.svg NAO encontrado!
)
if exist "new-logo.svg" (
    echo [OK] new-logo.svg encontrado
) else (
    echo [AVISO] new-logo.svg NAO encontrado!
)
echo.

echo Todos os outros arquivos (app/, config/, etc.) serao ignorados!
echo.

pause

echo.
echo Fazendo deploy dos arquivos atualizados...
echo.

vercel --prod

echo.
echo ========================================
echo   Deploy concluido!
echo ========================================
pause
