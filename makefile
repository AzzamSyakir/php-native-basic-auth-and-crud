# docker command
start-docker:
	docker compose --env-file src/.env -f ./docker/docker-compose.yml up -d

stop-docker:
	docker compose --env-file src/.env -f ./docker/docker-compose.yml down --remove-orphans

clean-docker:
	clear && docker system prune && docker volume prune && docker image prune -a -f && docker container prune && docker buildx prune
