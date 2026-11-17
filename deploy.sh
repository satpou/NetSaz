#!/usr/bin/env bash
# Deploy NetSaz ke VM produksi (Proxmox, lewat Cloudflare Tunnel).
#
# Pemakaian:  ./deploy.sh
#
# Yang dilakukan:
#   1. Push branch main ke GitHub.
#   2. Di VM: maintenance mode -> git pull -> composer & npm build ->
#      migrate -> cache ulang -> restart queue worker -> online lagi.
#
# Butuh entri "netsaz-app" di ~/.ssh/config (ProxyCommand cloudflared).
# Perubahan yang belum di-commit TIDAK ikut ter-deploy.

set -euo pipefail

HOST="${DEPLOY_HOST:-netsaz-app}"
APP_DIR="${DEPLOY_DIR:-/var/www/netsaz}"
BRANCH="main"

cd "$(dirname "$0")"

current_branch="$(git rev-parse --abbrev-ref HEAD)"
if [[ "$current_branch" != "$BRANCH" ]]; then
  echo "✗ Sedang di branch '$current_branch'. Pindah ke '$BRANCH' dulu." >&2
  exit 1
fi

if [[ -n "$(git status --porcelain)" ]]; then
  echo "⚠ Ada perubahan yang belum di-commit. Itu TIDAK ikut ter-deploy:"
  git status --short
  echo
fi

echo "→ Push $BRANCH ke GitHub"
git push origin "$BRANCH"

echo "→ Deploy di $HOST:$APP_DIR"
ssh "$HOST" APP_DIR="$APP_DIR" BRANCH="$BRANCH" bash -s <<'REMOTE'
set -euo pipefail
cd "$APP_DIR"

php artisan down --retry=15 || true
trap 'php artisan up' EXIT

git fetch --quiet origin "$BRANCH"
git merge --ff-only "origin/$BRANCH"
echo "  kode sekarang: $(git log --oneline -1)"

composer install --no-dev --optimize-autoloader --no-interaction --quiet
npm ci --no-audit --no-fund --silent
npm run build --silent

php artisan migrate --force
php artisan optimize:clear >/dev/null
php artisan optimize
php artisan queue:restart
REMOTE

echo "✓ Selesai. Cek https://netsaz.web.id"
