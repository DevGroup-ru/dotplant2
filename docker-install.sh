docker-compose build
docker-compose up -d
docker-compose exec php composer install
docker-compose exec php installer
