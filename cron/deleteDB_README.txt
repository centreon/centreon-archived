Create a cron task as the following sample:

59 23 * * * /your_oreon_path/cron/deleteDB.pl > /dev/null

line 34: Specify nagios directory

$PerfparseInstallFolder = "/srv/perfparse/";

line 35: specify where you would create the lock file

$file_lock = "/var/lock/purge.lock";

line 41:  Modify your oreon database access

$User = "root";
$Password = "";
$DataBase = "oreon";
$Host = "localhost";

line 50: Modify your perfparse database access

$Userpp = "root";
$Passwordpp = "";
$DataBasepp = "perfparse";
$Hostpp = "localhost";
