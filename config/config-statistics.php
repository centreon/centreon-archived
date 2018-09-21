<?php
const STATS_PATH = '/tmp';
const STATS_PREFIX = 'stats_';

// Credentials
const USERNAME = 'admin';
const PASSWORD = 'centreon';

// Sub route to authenticate
const AUTH_URL = 'http://127.0.0.1/centreon/api/index.php?action=authenticate';

// Route of the webservices
const WS_ROUTE = 'http://127.0.0.1/centreon/api/index.php?object=centreon_statistics&action=';

// webservices
const INFOS_RESOURCE = 'platformInfo';
const VERSIONNING_RESOURCE = 'version';
const INFO_TIMEZONE = 'platformTimezone';
const UUID_RESOURCE = 'CentreonUUID';

// Url where the stats are sent.
const CENTREON_STATS_URL = 'https://statistics.centreon.com';
