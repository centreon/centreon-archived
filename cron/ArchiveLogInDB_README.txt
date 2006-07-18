Create a cron task as the following sample:

0 0 1-31 * * php /your_oreon_path/cron/ArchiveLogInDB.php

line 34:  Modify your path
$NagiosPathArchive = "/var/log/nagios/archives";

line 37:  Modify your path
include_once("/usr/local/oreon/www/oreon.conf.php");