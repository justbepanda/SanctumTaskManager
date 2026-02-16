.PHONY: setup up down restart shell reseed test

DCSERVICE=app

# Установка
setup: prepare-env build install-deps migrate-f
	@echo "🚀 Task Manager API is ready at http://localhost:8000"

# Подготовка конфига (только если его нет)
prepare-env:
	@test -f .env || cp .env.example .env
	@echo "✅ .env file prepared"

# Сборка и запуск
build:
	docker compose up -d --build

# Установка зависимостей
install-deps:
	docker compose exec ${DCSERVICE} composer install
	docker compose exec ${DCSERVICE} php artisan key:generate

migrate-f:
	docker compose exec ${DCSERVICE} php artisan config:clear
	docker compose exec ${DCSERVICE} php artisan migrate:fresh --seed
	docker compose exec ${DCSERVICE} php artisan optimize:clear

up:
	docker compose up -d
	@echo "🚀 Task Manager API is ready at http://localhost:8000"

down:
	docker compose down -v

shell:
	docker compose exec ${DCSERVICE} bash

reseed:
	docker compose exec ${DCSERVICE} php artisan migrate:fresh --seed

restart:
	docker compose restart $(DCSERVICE)

test:
	docker compose exec ${DCSERVICE} php artisan test
