#!/bin/bash
set -e

echo "🚀 Starting Travel Request API setup..."

# Copy .env if it doesn't exist
if [ ! -f .env ]; then
    echo "📋 Creating .env from .env.example..."
    cp .env.example .env
fi

# Install dependencies
echo "📦 Installing dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader 2>/dev/null

# Generate app key if not set
if [ -z "$(grep '^APP_KEY=base64' .env)" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --no-interaction
fi

# Generate JWT secret if not set
if [ -z "$(grep '^JWT_SECRET=.' .env)" ] || grep -q '^JWT_SECRET=$' .env; then
    echo "🔐 Generating JWT secret..."
    php artisan jwt:secret --no-interaction --force
fi

# Wait for MySQL to be ready
echo "⏳ Waiting for database connection..."
max_tries=30
counter=0
until php artisan db:monitor --databases=mysql > /dev/null 2>&1 || [ $counter -eq $max_tries ]; do
    sleep 2
    counter=$((counter + 1))
    echo "   Attempt $counter/$max_tries..."
done

if [ $counter -eq $max_tries ]; then
    echo "⚠️  Database connection timeout. Run migrations manually after DB is ready."
else
    # Run migrations
    echo "🗄️  Running migrations..."
    php artisan migrate --force --no-interaction

    # Seed if tables are empty
    if [ "$(php artisan tinker --execute='echo App\Models\User::count();' 2>/dev/null)" = "0" ]; then
        echo "🌱 Seeding database..."
        php artisan db:seed --force --no-interaction
    fi

    # Setup testing database
    echo "🧪 Setting up testing database..."
    php artisan migrate --env=testing --force --no-interaction 2>/dev/null || true
fi

echo "✅ Setup complete! API is ready."

# Start PHP-FPM
exec php-fpm
