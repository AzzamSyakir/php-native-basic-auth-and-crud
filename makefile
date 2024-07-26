# docker command
start-docker:
	clear && docker compose -f ./docker/docker-compose.yml up -d 

stop-docker:
	clear && docker compose -f ./docker/docker-compose.yml down --remove-orphans

clean-docker:
	clear && docker system prune && docker volume prune && docker image prune -a -f && docker container prune && docker buildx prune
