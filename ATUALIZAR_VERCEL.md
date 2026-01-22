# Atualizar Apenas index.html e pracas.html na Vercel

## ⚠️ IMPORTANTE: Arquivo .vercelignore

Foi criado um arquivo `.vercelignore` que garante que **APENAS** estes arquivos serão enviados:
- `index.html`
- `pracas.html`
- `apresenta.html`
- `vercel.json`
- `package.json`
- `LOGO.svg`
- `LOGO-black.svg`
- `new-logo.svg`
- `README.md`

**Todos os outros arquivos da pasta (app/, config/, etc.) serão ignorados!**

## Opção 1: Atualizar via Interface Web (Mais Rápido)

1. Acesse seu projeto na Vercel: `vercel.com/moafsas-projects/thiga-transportes`
2. Vá na aba **"Source"** (já está aberta)
3. Clique no arquivo `index.html` na lista de arquivos
4. Clique no botão **"Edit"** ou **"✏️"** (se disponível)
5. Cole o conteúdo atualizado do seu `index.html` local
6. Salve (Ctrl+S ou botão Save)
7. Repita para `pracas.html`
8. A Vercel fará deploy automático das alterações

## Opção 2: Atualizar via CLI (Recomendado)

```bash
# 1. Instalar Vercel CLI (se ainda não tiver)
npm i -g vercel

# 2. Fazer login (se necessário)
vercel login

# 3. Na pasta do projeto, linkar ao projeto existente
vercel link

# Quando perguntar:
# - Link to existing project? Y
# - What’s the name of your existing project? thiga-transportes
# - Which scope? (escolha sua conta)

# 4. Fazer deploy apenas dos arquivos atualizados
vercel --prod

# Ou apenas para preview (sem afetar produção)
vercel
```

## Opção 3: Atualizar via Git (Se o projeto está conectado ao Git)

Se seu projeto na Vercel está conectado a um repositório Git:

```bash
# 1. Certifique-se de que index.html e pracas.html estão atualizados localmente

# 2. Adicione apenas esses arquivos ao commit
git add index.html pracas.html

# 3. Faça commit
git commit -m "Atualiza calculadora e formulário de propostas"

# 4. Faça push
git push

# A Vercel fará deploy automático!
```

## Opção 4: Upload Manual dos Arquivos

1. Acesse o projeto na Vercel
2. Vá em **Settings** → **Git**
3. Se não estiver conectado ao Git, você pode:
   - Conectar a um repositório GitHub/GitLab/Bitbucket
   - Ou usar a interface web para editar os arquivos diretamente

## Verificar se Atualizou Corretamente

Após atualizar, verifique:
- Acesse: `https://thiga-transportes.vercel.app/` (ou sua URL)
- Teste a calculadora
- Teste o link para `pracas.html`
- Verifique se o botão WhatsApp funciona

## Dica: Deploy Rápido via CLI

Se você já tem o projeto linkado, pode fazer deploy direto:

```bash
# Na pasta onde estão os arquivos atualizados
vercel --prod
```

Isso fará deploy apenas dos arquivos que mudaram!

**O arquivo `.vercelignore` garante que apenas `index.html`, `pracas.html` e `vercel.json` serão enviados!**

## Verificar o que será enviado

Antes de fazer deploy, você pode verificar quais arquivos serão enviados:

```bash
# Ver o que será ignorado
vercel --debug
```

Ou simplesmente execute o script `deploy.bat` que mostra a lista antes de fazer deploy.
