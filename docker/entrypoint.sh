#!/bin/sh
set -e

echo "🚀 Iniciando StreetFit API..."

# Força https:// no APP_URL se necessário
if echo "$APP_URL" | grep -q "^http://"; then
    if [ "$APP_ENV" = "production" ]; then
        export APP_URL=$(echo "$APP_URL" | sed 's|^http://|https://|')
        echo "APP_URL corrigido para: $APP_URL"
    fi
fi

# Permissões do storage
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

# Aguarda o banco via PHP/PDO
echo "⏳ Aguardando banco de dados..."
until php -r "
try {
    \$opts = [];
    if (getenv('DB_SSL') === 'true') {
        \$opts[PDO::PGSQL_ATTR_SSL_MODE] = 'require';
    }
    new PDO(
        'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD'),
        \$opts + [PDO::ATTR_TIMEOUT => 5]
    );
    exit(0);
} catch (Exception \$e) {
    fwrite(STDERR, \$e->getMessage() . PHP_EOL);
    exit(1);
}
" 2>/dev/null; do
    echo "   Banco indisponível, tentando em 3s..."
    sleep 3
done
echo "✅ Banco disponível!"

# Migrations
php artisan migrate --force
echo "✅ Migrations executadas!"

# Seeders — roda só se RUN_SEEDER=true OU se o banco estiver vazio
SHOULD_SEED=false

if [ "$RUN_SEEDER" = "true" ]; then
    SHOULD_SEED=true
    echo "RUN_SEEDER=true — executando seeders..."
else
    USER_COUNT=$(php -r "
        try {
            \$opts = [];
            if (getenv('DB_SSL') === 'true') \$opts[PDO::PGSQL_ATTR_SSL_MODE] = 'require';
            \$pdo = new PDO(
                'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
                getenv('DB_USERNAME'), getenv('DB_PASSWORD'), \$opts
            );
            echo \$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        } catch (Exception \$e) { echo 0; }
    " 2>/dev/null || echo 0)

    if [ "$USER_COUNT" = "0" ]; then
        SHOULD_SEED=true
        echo "Banco vazio — executando seeders pela primeira vez..."
    fi
fi

if [ "$SHOULD_SEED" = "true" ]; then
    php artisan db:seed --force
    echo "✅ Seeders executados!"
else
    echo "ℹ️  Seeders ignorados (banco já tem dados). Use RUN_SEEDER=true para forçar."
fi

# Storage link
php artisan storage:link 2>/dev/null || true

# Caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Caches gerados!"

exec "$@"
