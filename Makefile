# File: Makefile
# Seluruh target WAJIB melalui wrapper Sail. Tidak ada perintah host telanjang.
SHELL := /bin/bash
SAIL := ./vendor/bin/sail

.DEFAULT_GOAL := help
.PHONY: help up down shell test lint lint-check stan fresh backup backup-check logs build dev horizon-restart status perm-check install

help: ## Tampilkan daftar target
	@grep -hE '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-16s\033[0m %s\n", $$1, $$2}'

install: ## Bootstrap: composer install + npm install + permission guard (idempoten)
	$(SAIL) composer install --no-interaction --prefer-dist
	$(SAIL) npm install
	$(MAKE) perm-check

up: ## Nyalakan seluruh container (app, mysql, redis, horizon, scheduler, mailpit)
	$(SAIL) up -d
	$(MAKE) perm-check
	$(SAIL) artisan migrate --force

down: ## Matikan seluruh container
	$(SAIL) down

status: ## Status container
	$(SAIL) ps

shell: ## Masuk ke shell container app
	$(SAIL) shell

test: ## Jalankan Pest test suite (paralel)
	$(SAIL) artisan test --parallel

lint: ## Pint preset laravel (auto-fix)
	$(SAIL) bin pint

lint-check: ## Pint --test (tanpa mengubah file, untuk CI)
	$(SAIL) bin pint --test

stan: ## Larastan / PHPStan level 6
	$(SAIL) bin phpstan analyse --memory-limit=1G

fresh: ## [DESTRUKTIF] Hapus seluruh data & bangun ulang database dari nol
	@echo "!!! [DESTRUKTIF] Semua data akan hilang. Tekan Ctrl+C dalam 5 detik untuk membatalkan."
	@sleep 5
	$(SAIL) artisan migrate:fresh --seed
	$(SAIL) artisan permission:cache-reset

backup: ## Backup database terkompresi ke storage/backups
	$(SAIL) artisan db:backup

backup-check: ## Verifikasi file backup terbaru dapat dipulihkan
	$(SAIL) artisan db:restore-check

logs: ## Ikuti log aplikasi
	$(SAIL) logs -f --tail=200

build: ## Build aset frontend via Vite
	$(SAIL) npm run build

dev: ## Jalankan Vite dev server
	$(SAIL) npm run dev

horizon-restart: ## Restart worker Horizon setelah deploy perubahan kode
	$(SAIL) artisan horizon:terminate

perm-check: ## Linux Permission Guard: pastikan storage & bootstrap/cache ditulis sebagai user host
	@echo "== Permission Guard =="
	@test -f .env && grep -q '^WWWUSER=' .env || (echo "WWWUSER=$$(id -u)" >> .env)
	@test -f .env && grep -q '^WWWGROUP=' .env || (echo "WWWGROUP=$$(id -g)" >> .env)
	@# chown dijalankan sebagai root: diperlukan karena bind mount host->container.
	@$(SAIL) exec -u root laravel.test chown -R sail:sail storage bootstrap/cache
	@$(SAIL) exec -u root laravel.test chmod -R ug+rwX storage bootstrap/cache
	@# File uji dibuat sebagai user sail agar kepemilikannya mewakili proses aplikasi,
	@# lalu dipetakan ke user host (WWWUSER) oleh bind mount.
	@$(SAIL) exec -u sail laravel.test sh -c 'touch storage/logs/.perm-check'
	@OWNER=$$(stat -c '%U:%G' storage/logs/.perm-check 2>/dev/null || echo 'n/a'); \
	 EXPECT="$$(id -un):$$(id -gn)"; \
	 if [ "$$OWNER" = "$$EXPECT" ]; then \
	   echo "  owner file uji: $$OWNER (sesuai user host)"; \
	 else \
	   echo "  [PERINGATAN] owner file uji: $$OWNER, diharapkan $$EXPECT"; \
	 fi
	@rm -f storage/logs/.perm-check
	@echo "== Permission Guard selesai =="
