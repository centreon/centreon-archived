Create a cron task as the following sample:

0 0 1-31 * * nagios php -q /your_oreon_path/cron/reporting/ArchiveLogInDB.php

Open the following file: /your_oreon_path/cron/reporting/ArchiveLogInDB.php
And verify path variables for your config:

$path_oreon = '/your_oreon_path/';


