#!/usr/bin/env bash
# File: scripts/verify-phase0.sh
# Verifikasi Fase 0: bootstrap Docker/Sail, permission guard, Redis multi-DB, service health.
# Sifat: IDEMPOTEN & READ-ONLY terhadap data aplikasi (tidak menghapus apa pun).
set -uo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

SAIL="./vendor/bin/sail"
PASS=0
FAIL=0

ok()    { printf '  \033[32m[OK]\033[0m   %s\n' "$1"; PASS=$((PASS + 1)); }
bad()   { printf '  \033[31m[FAIL]\033[0m %s\n' "$1"; FAIL=$((FAIL + 1)); }
head_() { printf '\n\033[1m== %s ==\033[0m\n' "$1"; }

head_ "1. Berkas Fase 0"
for f in docker-compose.yml Makefile Dockerfile.prod docker-compose.prod.yml \
         .github/workflows/ci.yml .env phpstan.neon scripts/env-setup.sh; do
    [ -f "$f" ] && ok "ada: $f" || bad "hilang: $f"
done

head_ "2. WWWUSER / WWWGROUP (wajib id -u / id -g)"
grep -q '^WWWUSER=' .env  && ok "WWWUSER terisi: $(grep '^WWWUSER=' .env | cut -d= -f2)"  || bad "WWWUSER kosong"
grep -q '^WWWGROUP=' .env && ok "WWWGROUP terisi: $(grep '^WWWGROUP=' .env | cut -d= -f2)" || bad "WWWGROUP kosong"

head_ "3. Container berjalan"
for svc in laravel.test mysql redis horizon scheduler mailpit; do
    if $SAIL ps --status running 2>/dev/null | grep -q "$svc"; then
        ok "running: $svc"
    else
        bad "tidak running: $svc"
    fi
done

head_ "4. Healthcheck"
$SAIL exec -T mysql sh -c 'mysqladmin ping -h 127.0.0.1 -u root -p"$MYSQL_ROOT_PASSWORD"' >/dev/null 2>&1 \
    && ok "mysql: mysqladmin ping" || bad "mysql: ping gagal"
$SAIL exec -T redis redis-cli ping 2>/dev/null | grep -q PONG \
    && ok "redis: redis-cli ping -> PONG" || bad "redis: ping gagal"
$SAIL exec -T laravel.test php -r "exit(@file_get_contents('http://127.0.0.1/up')===false?1:0);" >/dev/null 2>&1 \
    && ok "app: /up merespons (HTTP 200)" || bad "app: /up gagal"

head_ "5. Isolasi Redis DB logis (0=default,1=cache,2=queue,3=session,4=views)"
for db in 0 1 2 3 4; do
    if $SAIL exec -T redis redis-cli -n "$db" ping 2>/dev/null | grep -q PONG; then
        ok "redis DB $db dapat diakses"
    else
        bad "redis DB $db tidak dapat diakses"
    fi
done

head_ "6. Permission Guard"
# chown perlu root karena bind mount host->container; berkas produksi tetap milik sail.
$SAIL exec -u root laravel.test chown -R sail:sail storage bootstrap/cache >/dev/null 2>&1
$SAIL exec -u sail laravel.test sh -c 'touch storage/logs/.perm-check' >/dev/null 2>&1 \
    || bad "storage tidak writable oleh user sail"
OWNER="$(stat -c '%U:%G' storage/logs/.perm-check 2>/dev/null || echo 'n/a')"
EXPECT="$(id -un):$(id -gn)"
if [ "$OWNER" = "$EXPECT" ]; then
    ok "owner file uji = $OWNER (sesuai user host, bukan root)"
else
    bad "owner file uji = $OWNER, diharapkan $EXPECT"
fi
rm -f storage/logs/.perm-check

head_ "7. Proses aplikasi bukan root"
APP_USER="$($SAIL exec -T laravel.test sh -c "ps -eo user,args --no-headers | grep -F 'artisan serve' | grep -v grep | awk '{print \$1}' | head -1" 2>/dev/null | tr -d '\r\n')"
if [ "$APP_USER" = "sail" ]; then
    ok "proses aplikasi berjalan sebagai 'sail'"
elif [ -z "$APP_USER" ]; then
    bad "proses 'artisan serve' tidak ditemukan di container app"
else
    bad "proses aplikasi berjalan sebagai '$APP_USER' (harus 'sail')"
fi
SUPERVISOR_USER="$($SAIL exec -T laravel.test printenv SUPERVISOR_PHP_USER 2>/dev/null | tr -d '\r\n')"
[ "$SUPERVISOR_USER" = "sail" ] && ok "supervisor user = sail" || bad "supervisor user = '$SUPERVISOR_USER' (harus 'sail')"

head_ "8. Gate kualitas"
$SAIL bin pint --test >/dev/null 2>&1 && ok "Pint --test lolos" || bad "Pint --test gagal"
$SAIL bin phpstan analyse --memory-limit=1G --no-progress >/dev/null 2>&1 && ok "PHPStan level 6 lolos" || bad "PHPStan gagal"
[ -f public/build/manifest.json ] && ok "aset Vite ter-build (manifest.json ada)" || bad "manifest Vite hilang"

printf '\n\033[1mHasil: %d OK, %d GAGAL\033[0m\n' "$PASS" "$FAIL"
[ "$FAIL" -eq 0 ] || exit 1
