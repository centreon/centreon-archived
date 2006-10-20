USE FOR : this script will erase data in perfparse database which do not use anymore in oreon frontend

Create a cron task as the following sample:

* 2 * * * /your_oreon_path/cron/delete_diff.pl > /dev/null

line 36: Specify perfparse directory

$PerfparseInstallFolder = "/srv/perfparse/";

line 43: specify where you would create the lock file

$file_lock = "/var/lock/purge.lock";

line 49:  Modify your oreon database access

$User = "root";
$Password = "";
$DataBase = "oreon";
$Host = "localhost";

line 58: Modify your perfparse database access

$Userpp = "root";
$Passwordpp = "";
$DataBasepp = "perfparse";
$Hostpp = "localhost";
