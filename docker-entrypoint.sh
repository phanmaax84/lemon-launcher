#!/bin/bash
set -e

# Wait for database to be ready
echo "Waiting for database..."
while ! php -r "
\$host = getenv('DB_HOST') ?: 'localhost';
\$port = getenv('DB_PORT') ?: '3306';
\$db = getenv('DB_NAME') ?: 'gdps';
\$user = getenv('DB_USER') ?: 'root';
\$pass = getenv('DB_PASS') ?: '';
try {
    new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
    echo 'OK';
} catch(Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
    echo "Database not ready, waiting 2s..."
    sleep 2
done
echo "Database is ready!"

# Run setup to create tables
cd /var/www/html
php tools/setup.php || true

# Execute the main command
exec "$@"
