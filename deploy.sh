#!/bin/bash

echo "========================================"
echo "  Deploy Thiga Transportes - Vercel"
echo "========================================"
echo ""

echo "Verificando se Vercel CLI está instalado..."
if ! command -v vercel &> /dev/null; then
    echo "Vercel CLI não encontrado. Instalando..."
    npm install -g vercel
    echo ""
fi

echo ""
echo "Fazendo deploy dos arquivos atualizados..."
echo ""

vercel --prod

echo ""
echo "========================================"
echo "  Deploy concluído!"
echo "========================================"
