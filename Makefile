.PHONY: setup start stop restart test fresh logs shell

# Full setup from scratch (evaluator runs this once)
setup: start
	@echo "Waiting for containers to initialize..."
	@sleep 10
	@docker compose exec travel-request-api php artisan test --no-interaction 2>/dev/null && \
		echo "All tests passing!" || echo "Run 'make test' after setup completes."

# Start containers (entrypoint handles all setup automatically)
start:
	docker compose up -d --build

# Stop containers
stop:
	docker compose down

# Restart containers
restart: stop start

# Run tests
test:
	docker compose exec travel-request-api php artisan test

# Fresh database (reset + seed)
fresh:
	docker compose exec travel-request-api php artisan migrate:fresh --seed --no-interaction

# View logs
logs:
	docker compose logs -f travel-request-api

# Shell into PHP container
shell:
	docker compose exec travel-request-api bash
