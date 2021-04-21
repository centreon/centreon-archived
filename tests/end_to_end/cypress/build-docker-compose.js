const fs = require('fs');
const path = require('path');

const baseRegistry = 'registry.centreon.com';
let release = '';
let imageName = '';
let distrib = '';
const portsExposition = '3400:80';
const containerName = 'centreon-web-only';

// parse args of script execution to define and set the docker image
process.argv.forEach(() => {
  release = process.argv[2] || '21.10';
  imageName = process.argv[3] || 'mon-web';
  distrib = process.argv[4] || 'centos7';
});

const stream = fs.createWriteStream(`${__dirname}/docker-compose.yml`);
const centreonWebPath = path.join(__dirname, '../../..');
const centreonDockerPath = '/usr/share/centreon';

stream.once('open', () => {
  stream.write(`version: "3.2"\n`);
  stream.write(`services:\n`);
  stream.write(`  ${containerName}:\n`);
  stream.write(
    `    image: ${baseRegistry}/${imageName}-${release}:${distrib}\n`,
  );
  stream.write(`    container_name: ${containerName}\n`);
  stream.write(`    ports:\n`);
  stream.write(`      - ${portsExposition}\n`);
  stream.write(`    volumes:\n`);
  stream.write(`      - ${centreonWebPath}/www:${centreonDockerPath}/www\n`);
  stream.write(`      - ${centreonWebPath}/src:${centreonDockerPath}/src\n`);
  stream.write(
    `      - ${centreonWebPath}/vendor:${centreonDockerPath}/vendor\n`,
  );
  stream.write(
    `      - ${centreonWebPath}/bootstrap.php:${centreonDockerPath}/bootstrap.php\n`,
  );
  stream.write(
    `      - ${centreonWebPath}/symfony.lock:${centreonDockerPath}/symfony.lock\n`,
  );

  // Important to close the stream when you're ready
  stream.end();
});
