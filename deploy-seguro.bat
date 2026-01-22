@echo off
echo ========================================
echo   Deploy Seguro - Apenas arquivos necessarios
echo ========================================
echo.

set TEMP_DIR=vercel-deploy-temp

echo Criando pasta temporaria para deploy...
if exist "%TEMP_DIR%" (
    echo Removendo pasta temporaria antiga...
    rmdir /s /q "%TEMP_DIR%"
)
mkdir "%TEMP_DIR%"

echo.
echo Copiando apenas os arquivos necessarios...
copy "index.html" "%TEMP_DIR%\" >nul 2>&1 && echo [OK] index.html
copy "pracas.html" "%TEMP_DIR%\" >nul 2>&1 && echo [OK] pracas.html
if exist "apresenta.html" copy "apresenta.html" "%TEMP_DIR%\" >nul 2>&1 && echo [OK] apresenta.html
if exist "vercel.json" copy "vercel.json" "%TEMP_DIR%\" >nul 2>&1 && echo [OK] vercel.json
if exist "package.json" copy "package.json" "%TEMP_DIR%\" >nul 2>&1 && echo [OK] package.json
if exist "README.md" copy "README.md" "%TEMP_DIR%\" >nul 2>&1 && echo [OK] README.md

echo.
echo Copiando arquivos de logo...
if exist "LOGO.svg" (
    copy "LOGO.svg" "%TEMP_DIR%\" >nul 2>&1 && echo [OK] LOGO.svg
) else (
    echo [AVISO] LOGO.svg nao encontrado!
)
if exist "LOGO-black.svg" (
    copy "LOGO-black.svg" "%TEMP_DIR%\" >nul 2>&1 && echo [OK] LOGO-black.svg
) else (
    echo [AVISO] LOGO-black.svg nao encontrado!
)
if exist "new-logo.svg" (
    copy "new-logo.svg" "%TEMP_DIR%\" >nul 2>&1 && echo [OK] new-logo.svg
) else (
    echo [AVISO] new-logo.svg nao encontrado!
)

echo.
echo ========================================
echo   Arquivos copiados! Fazendo deploy...
echo ========================================
echo.

cd "%TEMP_DIR%"

echo Verificando se Vercel CLI esta instalado...
vercel --version >nul 2>&1
if errorlevel 1 (
    echo Vercel CLI nao encontrado. Instalando...
    npm install -g vercel
    echo.
)

echo.
echo Fazendo deploy...
vercel --prod

cd ..

echo.
echo Limpando pasta temporaria...
rmdir /s /q "%TEMP_DIR%"

echo.
echo ========================================
echo   Deploy concluido!
echo ========================================
pause
