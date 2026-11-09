#!/usr/bin/env bash
# File: scripts/env-setup.sh
# Menulis / memperbarui konfigurasi .env secara IDEMPOTEN (grep -q || echo >>).
# Aman dijalankan berulang kali.
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

[ -f .env ] || cp .env.example .env

# set_env KEY VALUE [--overwrite]
# Default: hanya menambahkan bila key belum ada (idempoten, tidak menimpa).
set_env() {
    local key="$1" value="$2" overwrite="${3:-}"
    if [ "$overwrite" = "--overwrite" ]; then
        if grep -q "^${key}=" .env; then
            sed -i -E "s|^${key}=.*|${key}=${value}|" .env
        else
            echo "${key}=${value}" >> .env
        fi
    else
        grep -q "^${key}=" .env || echo "${key}=${value}" >> .env
    fi
}

# --- Linux Permission Guard (wajib pakai id -u / id -g, bukan $UID) ---
set_env WWWUSER "$(id -u)"
set_env WWWGROUP "$(id -g)"

# --- Aplikasi & waktu ---
set_env APP_NAME "\"Portal Berita\"" --overwrite
set_env APP_TIMEZONE UTC --overwrite
set_env APP_LOCALE id --overwrite
set_env APP_FALLBACK_LOCALE id --overwrite
set_env APP_FAKER_LOCALE id_ID --overwrite
set_env APP_URL http://localhost --overwrite

# --- Database ---
set_env DB_CONNECTION mysql --overwrite
set_env DB_HOST mysql --overwrite
set_env DB_PORT 3306 --overwrite
set_env DB_DATABASE portal_berita --overwrite
set_env DB_USERNAME sail --overwrite
set_env DB_PASSWORD password --overwrite
set_env DB_COLLATION utf8mb4_unicode_ci --overwrite

# --- Redis: isolasi DB logis ---
set_env REDIS_CLIENT phpredis --overwrite
set_env REDIS_HOST redis --overwrite
set_env REDIS_PORT 6379 --overwrite
set_env REDIS_DB 0 --overwrite
set_env REDIS_CACHE_DB 1 --overwrite
set_env REDIS_QUEUE_DB 2 --overwrite
set_env REDIS_SESSION_DB 3 --overwrite
set_env REDIS_VIEWS_DB 4 --overwrite

# --- Driver ---
set_env QUEUE_CONNECTION redis --overwrite
set_env CACHE_STORE redis --overwrite
set_env SESSION_DRIVER redis --overwrite
set_env SESSION_LIFETIME 120 --overwrite
set_env CACHE_PREFIX portal_berita --overwrite

# --- Mail (Mailpit lokal) ---
set_env MAIL_MAILER smtp --overwrite
set_env MAIL_HOST mailpit --overwrite
set_env MAIL_PORT 1025 --overwrite
set_env MAIL_FROM_ADDRESS "\"redaksi@portal.test\"" --overwrite
set_env MAIL_FROM_NAME "\"\${APP_NAME}\"" --overwrite

# --- Storage & disk ---
set_env FILESYSTEM_DISK public --overwrite
set_env MEDIA_DISK public --overwrite

# --- Pencarian (default MySQL FULLTEXT; Scout/Meilisearch opsional) ---
set_env SEARCH_DRIVER database --overwrite
set_env SCOUT_DRIVER meilisearch --overwrite
set_env MEILISEARCH_HOST http://meilisearch:7700 --overwrite
set_env MEILISEARCH_NO_ANALYTICS true --overwrite

# --- Horizon / antrean ---
set_env HORIZON_PREFIX "portal_berita_horizon:" --overwrite
set_env QUEUE_MONITOR_EMAIL admin@portal.test --overwrite

# --- Seed password kru (default lokal yang jelas, jangan dipakai di produksi) ---
set_env CREW_SEED_PASSWORD "RedaksiPortal2026!" --overwrite
set_env READER_SEED_PASSWORD "PembacaPortal2026!" --overwrite

# --- Cache halaman publik ---
set_env PAGE_CACHE_TTL 60 --overwrite
set_env ADS_ENABLED false --overwrite

# --- Observability ---
set_env TELESCOPE_ENABLED true --overwrite
set_env PULSE_ENABLED true --overwrite

echo "== .env selesai disiapkan =="
grep -cE '^[A-Z_]+=' .env | awk '{print "  total variabel:", $1}'
