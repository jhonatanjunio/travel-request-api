#!/bin/bash

echo "Starting Travel Request API setup..."

# Copy .env if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Install dependencies
echo "Installing dependencies..."
if ! composer install --no-interaction --prefer-dist --optimize-autoloader; then
    echo "WARNING: composer install failed. Trying update..."
    composer update --no-interaction --prefer-dist --optimize-autoloader || true
fi

# Only proceed with artisan commands if vendor dir exists
if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then

    # Generate app key if not set
    if ! grep -q '^APP_KEY=base64' .env 2>/dev/null; then
        echo "Generating application key..."
        php artisan key:generate --no-interaction || true
    fi

    # Generate JWT secret if not set
    if ! grep -q '^JWT_SECRET=.\+' .env 2>/dev/null; then
        echo "Generating JWT secret..."
        php artisan jwt:secret --no-interaction --force || true
    fi

    # Wait for MySQL using PDO connection test
    echo "Waiting for database connection..."
    max_tries=30
    counter=0
    while [ $counter -lt $max_tries ]; do
        if php -r "
            try {
                new PDO(
                    'mysql:host=' . (getenv('DB_HOST') ?: 'db') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . (getenv('DB_DATABASE') ?: 'travel_management'),
                    getenv('DB_USERNAME') ?: 'travel_user',
                    getenv('DB_PASSWORD') ?: 'travel_password'
                );
                exit(0);
            } catch (Exception \$e) { exit(1); }
        " 2>/dev/null; then
            echo "Database connected!"
            break
        fi
        counter=$((counter + 1))
        echo "  Attempt $counter/$max_tries..."
        sleep 2
    done

    if [ $counter -lt $max_tries ]; then
        echo "Running migrations..."
        php artisan migrate --force --no-interaction 2>&1 || echo "WARNING: Migration failed"

        # Seed if tables are empty
        user_count=$(php artisan tinker --execute='echo App\Models\User::count();' 2>/dev/null || echo "0")
        if [ "$user_count" = "0" ]; then
            echo "Seeding database..."
            php artisan db:seed --force --no-interaction 2>&1 || echo "WARNING: Seeding failed"
        fi

        # Setup testing database
        echo "Setting up testing database..."
        php artisan migrate --env=testing --force --no-interaction 2>&1 || true
    else
        echo "WARNING: Database connection timeout after $max_tries attempts."
    fi
else
    echo "WARNING: vendor directory not found."
fi

echo "Setup complete! Starting PHP-FPM..."
exec php-fpm
