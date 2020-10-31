docker-compose up -d
sleep 5
docker exec commpeak_myapp_1 php myapp/bin/console doctrine:migrations:migrate --no-interaction
