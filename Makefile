.PHONY: up down restart logs artisan composer npm shell install build setup rebuild

# Запуск — всё поднимается в Docker, entrypoint сам ставит composer/npm и собирает assets
up:
	docker-compose up -d --build

# Первый запуск с пересборкой (если что-то поменялось)
setup: up
	@echo "Ожидание готовности контейнеров..."
	@sleep 5
	docker-compose exec app composer install --no-interaction 2>/dev/null || true
	docker-compose exec app npm install 2>/dev/null || true
	docker-compose exec app npm run build 2>/dev/null || true
	@echo "Готово. Откройте http://localhost"

down:
	docker-compose down

restart:
	docker-compose restart

logs:
	docker-compose logs -f app

artisan:
	docker-compose exec app php artisan $(filter-out $@,$(MAKECMDGOALS))

composer:
	docker-compose exec app composer $(filter-out $@,$(MAKECMDGOALS))

npm:
	docker-compose exec app npm $(filter-out $@,$(MAKECMDGOALS))

shell:
	docker-compose exec app bash

install:
	docker-compose exec app composer install --no-interaction
	docker-compose exec app npm install
	docker-compose exec app php artisan key:generate --no-interaction

build:
	docker-compose exec app npm run build

# Полная пересборка
rebuild:
	docker-compose down
	docker-compose build --no-cache
	docker-compose up -d
	@echo "Ожидание MySQL..."
	@sleep 15
	docker-compose exec app composer install --no-interaction
	docker-compose exec app npm install
	docker-compose exec app npm run build
	@echo "Готово. http://localhost"
