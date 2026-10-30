# Forge deploy script for unipal-api (zero-downtime deployments).
# Site: api.unipal.group (main) / staging-api.unipal.group (develop), PHP 8.3+, web directory /public.
# Environment: APP_ENV=production, APP_DEBUG=false, DB_CONNECTION=pgsql, CACHE_STORE=redis,
# QUEUE_CONNECTION=redis, SESSION_DRIVER=redis. Add a queue worker in Forge (connection redis).
# First deploy only, from the site's SSH session: php artisan unipal:user you@unipal.group --role=owner

$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader
$FORGE_PHP artisan optimize
$FORGE_PHP artisan storage:link
$FORGE_PHP artisan migrate --force

$ACTIVATE_RELEASE()

$RESTART_QUEUES()
