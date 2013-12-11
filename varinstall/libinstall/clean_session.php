#!/usr/bin/php
<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus
 *
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 *
 * For information : contact@centreon.com
 *
 * Developper: Maximilien Bersoult
 *
 */

/*
 * Error Level
 */
error_reporting(E_ERROR | E_PARSE);

function usage($command)
{
    print $command . " centreon_etc_path\n";
    print "\tcentreon_etc_path\tThe path to Centreon configuration default (/etc/centreon)\n";
}

if (count($argv) != 2) {
    fwrite(STDERR, "Incorrect number of arguments\n");
    usage($argv[0]);
    exit(1);
}

$centreon_etc = realpath($argv[1]);

if (!file_exists($centreon_etc . '/centreon.conf.php')) {
    fwrite(STDERR, "Centreon configuration file doesn't exists\n");
    usage($argv[0]);
    exit(1);
}

require_once $centreon_etc . '/centreon.conf.php';
require_once $centreon_path . '/www/class/centreonDB.class.php';

$dbconn = new CentreonDB();
$queryCleanSession = 'DELETE FROM session';
if (PEAR::isError($dbconn->query($queryCleanSession))) {
    fwrite(STDERR, "Error in purge sessions\n");
    exit(1);
}

exit(0);

