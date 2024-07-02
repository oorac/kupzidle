ifneq (,$(wildcard .env))
	include .env
	export $(shell sed 's/=.*//' .env)
endif

# Start dev environment
up:
	docker compose -f docker-compose.yml up -d --remove-orphans;
	@echo 'App is running on http://localhost:8103';
	@echo 'RabbitMQ is running on http://localhost:15672';

# Start dev environment with forced build
up\:build:
	docker compose -f docker-compose.yml up -d --build;
	@echo 'App is running on http://localhost:8103';
	@echo 'RabbitMQ is running on http://localhost:15672';

# Stop dev environment
down:
	docker compose -f docker-compose.yml down;

# Show logs - format it using less
logs:
	docker compose -f docker-compose.yml logs -f --tail=10 | less -S +F;

# Exec sh on php container
exec\:php:
	docker compose -f docker-compose.yml exec php sh;

exec\:mysql:
	docker compose -f docker-compose.yml exec mysql sh;

# Init project
init:
	docker compose -f docker-compose.yml exec php bin/console orm:schema-tool:drop --force --full-database;
	docker compose -f docker-compose.yml exec php bin/console orm:schema-tool:create;
	docker compose -f docker-compose.yml exec php bin/console doctrine:fixtures:load --no-interaction --append

# Migrations
diff:
	make clean
	docker compose -f docker-compose.yml exec php bin/console orm:schema-tool:drop --force --full-database;
	docker compose -f docker-compose.yml exec php bin/console migrations:migrate --allow-no-migration --no-interaction;
	docker compose -f docker-compose.yml exec php bin/console migrations:diff;
	git add migrations/*
	make init

cs:
	docker compose -f docker-compose.yml exec php ./vendor/bin/phpcs --cache=./qa/phpcs/phpcs.cache --standard=./qa/phpcs/ruleset.xml --extensions=php --encoding=utf-8 --tab-width=4 -sp --colors app

cs-fix:
	docker compose -f docker-compose.yml exec php ./vendor/bin/phpcbf --standard=./qa/phpcs/ruleset.xml --extensions=php --encoding=utf-8 --tab-width=4 --colors app

updateDb:
	sudo chmod -R 777 .;
	chown -R www-data:www-data .;
	rm -rf temp/cache/* && rm -rf temp/proxies/*;
	composer du -o;
	php cli orm:schema-tool:update --force --dump-sql --complete;
	sudo chmod -R 777 .;
	chown -R www-data:www-data .;

# clear temp/cache and proxies
clean:
	rm -rf temp/cache/* && rm -rf temp/proxies/*

# Register a class
du:
	composer du -o;

chmod:
	sudo chmod -R 777 .;
	chown -R www-data:www-data .;

process-split-xml:
	./bin/manage_consumers.sh add process-split-xml

save-label:
	./bin/manage_consumers.sh add save-label

save-product:
	./bin/manage_consumers.sh add save-product

save-parameter:
	./bin/manage_consumers.sh add save-parameter