Create a cron task as the following sample:

0 0 1-31 * * nagios php -q /your_oreon_path/cron/ArchiveLogInDB.php

Open the following file: /your_oreon_path/cron/ArchiveLogInDB.php
And modify path variables for your config:

$path_oreon = '/usr/local/oreon/';
$NagiosPathArchive = "/var/log/nagios/archives";

