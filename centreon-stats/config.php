<?php
const STATS_PATH = '/tmp';
const STATS_PREFIX = 'stats_';

// Credentials
const USERNAME = 'admin';
const PASSWORD = 'centreon';

// Sub route to authenticate
const AUTH_URL = 'http://10.30.2.85/centreon/api/index.php?action=authenticate';

// Route of the webservices
const WS_ROUTE = 'http://10.30.2.85/centreon/api/index.php?object=centreon_statistics&action=';

// webservices
const INFOS_RESOURCE = 'PlatformInfo';
const VERSIONNING_RESOURCE = 'version';
const UUID_RESOURCE = 'CentreonUUID';

// Url where the stats are sent.
const CENTREON_STATS_URL = 'https://statistics.centreon.com';
?>
