# Deploy na Vercel - Guia Rápido

## Arquivos Necessários
Apenas estes dois arquivos são necessários:
- `index.html`
- `pracas.html`

Todos os recursos (fontes, bibliotecas, imagens) já estão usando CDNs ou URLs externas.

## Opção 1: Deploy via Interface Web da Vercel

1. Acesse [vercel.com](https://vercel.com) e faça login
2. Clique em "Add New Project"
3. Escolha "Upload" ou "Import Git Repository"
4. Se escolher Upload:
   - Arraste apenas os arquivos `index.html` e `pracas.html`
   - Ou crie uma pasta, coloque os arquivos e faça upload da pasta
5. Configure:
   - **Framework Preset**: "Other" ou "Static Site"
   - **Build Command**: Deixe vazio (não precisa build)
   - **Output Directory**: Deixe vazio ou coloque `.` (ponto)
6. Clique em "Deploy"

## Opção 2: Deploy via CLI da Vercel

```bash
# Instalar Vercel CLI (se ainda não tiver)
npm i -g vercel

# Na pasta onde estão os arquivos
vercel

# Siga as instruções:
# - Link to existing project? N
# - Project name: thiga-transportes (ou outro nome)
# - Directory: . (ponto)
# - Override settings? N
```

## Opção 3: Deploy via GitHub

1. Crie um repositório no GitHub
2. Adicione apenas `index.html` e `pracas.html`
3. Na Vercel, importe o repositório
4. Configure:
   - **Framework Preset**: "Other"
   - **Build Command**: (deixe vazio)
   - **Output Directory**: (deixe vazio)
5. Deploy automático!

## Configuração Recomendada

Crie um arquivo `vercel.json` (opcional) na raiz:

```json
{
  "version": 2,
  "builds": [
    {
      "src": "*.html",
      "use": "@vercel/static"
    }
  ],
  "routes": [
    {
      "src": "/",
      "dest": "/index.html"
    },
    {
      "src": "/pracas",
      "dest": "/pracas.html"
    }
  ]
}
```

## Notas Importantes

- ✅ Todos os recursos externos (CDNs) já estão configurados
- ✅ O logo usa URL externa: `https://www.thiga.com.br/LOGO-black.svg`
- ✅ Não precisa de build ou compilação
- ✅ Site estático puro HTML/CSS/JS

## URLs Após Deploy

Após o deploy, você terá:
- Site principal: `https://seu-projeto.vercel.app/`
- Página de propostas: `https://seu-projeto.vercel.app/pracas.html`
