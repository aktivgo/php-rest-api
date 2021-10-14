include .env

init:
	cd web/composer && composer install
	docker-compose up -d mysql
	migrate -path=/migrations/ -database "mysql://dev:dev@tcp(task2.loc:8989)/test" up

docker-start:
	@docker-compose up -d

docker-stop:
	@docker-compose down

docker-restart:
	@docker-compose down
	@docker-compose up -d