include .env

init:
	@cd web/composer && composer install
	@docker-compose up -d mysqldb
	while [ ![@migrate -path=web/database/migrations/ -database "mysql://dev:dev@tcp(task2.loc:8989)/test" up] ]; do done

start:
	@docker-compose up -d

stop:
	@docker-compose down

restart:
	@docker-compose down
	@docker-compose up -d