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
    echo "WARNING: composer install failed. Trying without lock file..."
    composer update --no-interaction --prefer-dist --optimize-autoloader || echo "WARNING: composer update also failed. Continuing..."
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

    # Wait for MySQL to be ready
    echo "Waiting for database connection..."
    max_tries=30
    counter=0
    while [ $counter -lt $max_tries ]; do
        if php artisan migrate:status > /dev/null 2>&1; then
            break
        fi
        counter=$((counter + 1))
        echo "  Attempt $counter/$max_tries..."
        sleep 2
    done

    if [ $counter -lt $max_tries ]; then
        echo "Running migrations..."
        php artisan migrate --force --no-interaction || true

        # Seed if tables are empty
        user_count=$(php artisan tinker --execute='echo App\Models\User::count();' 2>/dev/null || echo "0")
        if [ "$user_count" = "0" ]; then
            echo "Seeding database..."
            php artisan db:seed --force --no-interaction || true
        fi

        # Setup testing database
        echo "Setting up testing database..."
        php artisan migrate --env=testing --force --no-interaction 2>/dev/null || true
    else
        echo "WARNING: Database connection timeout. Run migrations manually."
    fi
else
    echo "WARNING: vendor directory not found. Run 'composer install' manually."
fi

echo "Setup complete! Starting PHP-FPM..."

# Start PHP-FPM (this must succeed or container exits)
exec php-fpm
