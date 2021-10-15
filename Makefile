include .env

init:
	@cd web/composer && composer install
	@docker-compose up -d mysqldb
	@migrate -path=web/database/migrations/ -database "mysql://dev:dev@tcp(task2.loc:8989)/test" up

start:
	@cd web/composer && composer install
	@docker-compose up -d
	@migrate -path=web/database/migrations/ -database "mysql://dev:dev@tcp(task2.loc:8989)/test" up

stop:
	@docker-compose down

restart:
	@docker-compose down
	@docker-compose up -d