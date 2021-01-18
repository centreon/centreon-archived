#!/bin/sh
node ./cypress/build-docker-compose.js

cd ../../
npm run build

cd tests/e2e/cypress
docker-compose pull
docker-compose up -d --force-recreate
docker exec -it centreon-web-only sh -c "rm -rf /usr/share/centreon/www/install"
