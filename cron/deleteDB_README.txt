USE FOR : This script will erase data which correspond to purge policy (defined in oreon frontend) in perfparse database.

Create a cron task as the following sample:

59 23 * * * /your_oreon_path/cron/deleteDB.pl > /dev/null

line 34: Specify perfparse directory

$PerfparseInstallFolder = "/srv/perfparse/";

line 41: specify where you would create the lock file

$file_lock = "/var/lock/purge.lock";

line 47: specify where is oreon.conf.php

$file_lock = "/srv/oreon/www/oreon.conf.php";