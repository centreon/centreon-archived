#!/bin/sh

cd cypress
docker-compose pull
docker-compose up -d --force-recreate
docker exec -it centreon-web-only sh -c "rm -rf /usr/share/centreon/www/install"
