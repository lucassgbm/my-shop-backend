# StreetFit — Guia de Instalação

## Estrutura

```
streetfit-api/   → Laravel 11 (API + Filament Admin)
streetfit-web/   → Next.js 14 (Frontend)
```

---

## Backend (streetfit-api)

### Pré-requisitos
- PHP 8.2+
- Composer
- PostgreSQL

### Instalação

```bash
cd streetfit-api

# 1. Instalar dependências
composer install --ignore-platform-reqs

# 2. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 3. Editar .env com suas credenciais
#    DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
#    MP_PUBLIC_KEY, MP_ACCESS_TOKEN
#    ME_CLIENT_ID, ME_CLIENT_SECRET

# 4. Migrations e seed
php artisan migrate --seed

# 5. Storage
php artisan storage:link

# 6. Rodar servidor
php artisan serve --port=8000
```

### Acesso Admin (Filament)
- URL: http://localhost:8000/admin
- Email: admin@streetfit.com.br
- Senha: password

---

## Frontend (streetfit-web)

### Pré-requisitos
- Node.js 20+

### Instalação

```bash
cd streetfit-web

# 1. Instalar dependências
npm install

# 2. Configurar ambiente
cp .env.local.example .env.local
# Editar NEXT_PUBLIC_API_URL=http://localhost:8000/api

# 3. Rodar em desenvolvimento
npm run dev
```

Acesse: http://localhost:3000

### Build para produção

```bash
npm run build
npm start
```

---

## Deploy

### Backend (Render)
1. Criar Web Service apontando para `streetfit-api/`
2. Build Command: `composer install --no-dev --ignore-platform-reqs`
3. Start Command: `php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT`
4. Configurar variáveis de ambiente no painel do Render

### Frontend (Vercel)
1. Importar repositório `streetfit-web/` na Vercel
2. Framework: Next.js (detectado automaticamente)
3. Configurar `NEXT_PUBLIC_API_URL` com a URL do backend no Render

---

## Variáveis de Ambiente

### Backend (.env)
```
APP_URL=https://seu-backend.onrender.com
FRONTEND_URL=https://seu-frontend.vercel.app
DB_CONNECTION=pgsql
DB_SSL=true
SANCTUM_STATEFUL_DOMAINS=seu-frontend.vercel.app
```

### Frontend (.env.local)
```
NEXT_PUBLIC_API_URL=https://seu-backend.onrender.com/api
NEXT_PUBLIC_MP_PUBLIC_KEY=sua-chave-publica-mp
```
