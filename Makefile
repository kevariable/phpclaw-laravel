DC = docker compose run --rm app

.PHONY: build test coverage analyse format shell

build:
	docker compose build

test:
	$(DC) vendor/bin/pest

coverage:
	$(DC) vendor/bin/pest --coverage-text

analyse:
	$(DC) vendor/bin/phpstan analyse

format:
	$(DC) vendor/bin/pint

shell:
	$(DC) bash
