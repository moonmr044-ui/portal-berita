# Portal Berita Digital

Portal berita berskala besar (acuan fungsionalitas & UX: Detik.com dan Kompas.com).
Berjalan **100% di Docker (Laravel Sail)** — host tidak perlu PHP, Composer, atau Node.

---

## 1. Versi Teknologi (terkunci saat scaffold)

| Komponen | Versi | Catatan |
|---|---|---|
| PHP | **8.5** (dev runtime Sail), **8.3** (produksi) | `composer.json` mensyaratkan `^8.3` |
| Laravel Framework | **12.x** | dipatok eksplisit dari skeleton 13 agar sesuai spesifikasi |
| MySQL | **8.4** | InnoDB, `utf8mb4_unicode_ci` |
| Redis | **7** | isolasi DB logis 0–4 |
| Meilisearch | **v1.11** | profil opsional `search` |
| Mailpit | latest | hanya development |
| Node.js | **22** (Dockerfile.prod), **24** (Sail runtime) | bundler Vite |
| Tailwind CSS | 4.x | `@tailwindcss/typography`, `@tailwindcss/forms` |
| Pest / PHPUnit | Pest 3 / PHPUnit 12 | `php artisan test --parallel` |
| Larastan | PHPStan level **6+** | `phpstan.neon` |
| Laravel Pint | preset `laravel` | gate CI |

> **Verifikasi versi:** `./vendor/bin/sail exec laravel.test php artisan --version`
> dan `./vendor/bin/sail exec laravel.test php -v`.

---

## 2. Bootstrap Pertama Kali (hanya sekali, sebelum Sail tersedia)

Semua perintah dijalankan dari root repositori. Perintah bootstrap **idempoten**
(aman dijalankan ulang, tidak menimpa proyek yang sudah ada).

```bash
cd ~/Documents

# 1. Scaffold proyek (dilewati bila folder sudah ada)
[ -d portal-berita ] || docker run --rm -u "$(id -u):$(id -g)" \
    -v "$(pwd):/opt" -w /opt laravelsail/php84-composer:latest \
    composer create-project laravel/laravel portal-berita

cd portal-berita

# 2. Patok Laravel 12 (spesifikasi meminta Laravel 11/12)
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/opt" -w /opt \
    laravelsail/php84-composer:latest \
    composer require "laravel/framework:^12.0" --with-all-dependencies

# 3. Pasang Sail + scaffold service
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/opt" -w /opt \
    laravelsail/php84-composer:latest composer require laravel/sail --dev
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/opt" -w /opt \
    laravelsail/php84-composer:latest php artisan sail:install \
    --with=mysql,redis,mailpit,meilisearch

# 4. Linux Permission Guard + konfigurasi .env (idempoten)
bash scripts/env-setup.sh

# 5. Nyalakan stack
make up
```

Setelah container naik, `make perm-check` memastikan berkas di `storage/` dan
`bootstrap/cache/` dimiliki user host (bukan root).

---

## 3. Perintah Harian (WAJIB lewat Sail)

Host OS tidak boleh menjalankan `php`, `artisan`, `composer`, `npm`, `node` secara telanjang.

```bash
make up            # nyalakan seluruh container + migrate
make down          # matikan container
make shell         # masuk shell container
make test          # Pest --parallel
make lint          # Pint auto-fix
make lint-check    # Pint --test (CI mode)
make stan          # PHPStan / Larastan level 6
make build         # Vite build
make logs          # ikuti log
make backup        # mysqldump terkompresi
make fresh         # [DESTRUKTIF] migrate:fresh --seed
make perm-check    # verifikasi izin storage
make help          # daftar seluruh target
```

Padanan manual Sail:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail composer require spatie/laravel-permission
./vendor/bin/sail npm run build
docker compose exec -u sail laravel.test php artisan tinker
```

---

## 4. Isolasi Redis DB Logis

| DB | Kegunaan | Env |
|---|---|---|
| 0 | default | `REDIS_DB` |
| 1 | cache aplikasi | `REDIS_CACHE_DB` |
| 2 | queue / Horizon | `REDIS_QUEUE_DB` |
| 3 | session | `REDIS_SESSION_DB` |
| 4 | views counter & popularitas | `REDIS_VIEWS_DB` |

`FLUSHALL` **dilarang**. Untuk pembersihan parsial, gunakan `FLUSHDB`
pada koneksi spesifik: `Redis::connection('views')->flushdb()`.

---

## 5. Verifikasi Kesehatan

```bash
bash scripts/verify-phase0.sh   # 7 pemeriksaan Fase 0
```

Pemeriksaan mencakup: keberadaan berkas, `WWWUSER`/`WWWGROUP`, status container,
healthcheck MySQL/Redis/app, izin `storage`, dan proses aplikasi bukan root.

### Layanan dan port

| Layanan | URL / Port |
|---|---|
| Aplikasi | http://localhost |
| Mailpit (UI) | http://localhost:8025 |
| Meilisearch | http://localhost:7700 |
| MySQL | `127.0.0.1:3306` |
| Redis | `127.0.0.1:6379` |
| Vite HMR | `localhost:5173` |

---

## 6. Produksi

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build
```

Rahasia disediakan lewat `.env.production` (chmod 600, tidak masuk git) atau
Docker secret. `Dockerfile.prod` multi-stage menghasilkan image tanpa dev
dependency, OPcache aktif, dan proses berjalan sebagai user `appuser` (non-root).

---

## 7. Struktur Repositori (Fase 0)

```
.
├── docker-compose.yml          # stack development (app, mysql, redis, horizon, scheduler, mailpit, meilisearch)
├── docker-compose.prod.yml     # stack produksi
├── Dockerfile.prod             # multi-stage: node build -> composer -> php-fpm+nginx
├── Makefile                    # semua target memakai wrapper Sail
├── phpstan.neon                # Larastan level 6
├── docker/prod/                # nginx, supervisord, php-fpm pool, reverse proxy TLS
├── scripts/env-setup.sh        # konfigurasi .env idempoten + permission guard
├── scripts/verify-phase0.sh    # skrip verifikasi Fase 0
└── .github/workflows/ci.yml    # 3 job: quality, tests, frontend
```

---

## 8. Lisensi

MIT.
