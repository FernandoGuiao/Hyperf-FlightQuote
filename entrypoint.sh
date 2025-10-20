#!/usr/bin/env sh
set -e

cd /opt/www

ensure_install() {
  COMPOSER_ARGS="${COMPOSER_ARGS:---no-dev -o --no-scripts --no-interaction --prefer-dist}"
  echo "[entrypoint] Running: composer install $COMPOSER_ARGS"
  composer install $COMPOSER_ARGS
}

# Ensure composer.lock exists (Hyperf reads it at runtime)
if [ ! -f composer.lock ]; then
  echo "[entrypoint] composer.lock not found; generating via composer install..."
  ensure_install
fi

# Ensure vendor autoload exists
if [ ! -f vendor/autoload.php ]; then
  echo "[entrypoint] vendor/autoload.php not found; running composer install..."
  ensure_install
fi

# Optional: fix permissions if HOST_UID/HOST_GID provided
if [ -n "${HOST_UID}" ] && [ -n "${HOST_GID}" ]; then
  echo "[entrypoint] Chowning vendor and composer.lock to ${HOST_UID}:${HOST_GID}..."
  chown -R "${HOST_UID}:${HOST_GID}" vendor composer.lock || true
fi

exec php /opt/www/bin/hyperf.php server:watch
