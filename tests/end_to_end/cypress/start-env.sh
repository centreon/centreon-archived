#!/bin/sh

cd ../../
npm run build

cd tests/end_to_end/cypress
docker-compose pull
docker-compose up -d --force-recreate
docker exec -it centreon-web-only sh -c "rm -rf /usr/share/centreon/www/install"
