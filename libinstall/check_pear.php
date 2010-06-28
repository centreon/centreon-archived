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

function usage() {
	print $argv[0] . " check|install [file]\n";
	print "\tcheck\tcheck if the package list is installed\n";
	print "\tupgrade\tupgrade the packages\n";
	print "\tinstall\tinstall the packages\n";
}

function check_file($file) {
	if (!file_exists($file)) {
		fwrite(STDERR, "The file with the list of packages does not exist\n");
		exit(2);
	}
	if (!is_readable($file)) {
		fwrite(STDERR, "The file with the list of packages cannot be read\n");
		exit(2);
	}
}

function get_list($file) {
	$packages = array();
	$fd = fopen($file, 'r');
	while ($line = fgets($fd)) {
		list($name, $version, $status) = split('::', trim($line));
		$package = array('name' => $name, 'version' => $version);
		if ($status) {
			$package['status'] = $status;
		}
		$packages[] = $package;
	}
	fclose($fd);
	return($packages);
}

function check($packages) {
	$config =& PEAR_Config::singleton();
	$reg =& $config->getRegistry();
	$ret = 0;
	foreach ($packages as $package) {
		//echo "\033[s\033[1;37m" . $package['name'] . "\033[0m\033[33G\033[0;37m" . $package['version'] . "\033[0m\033[45G";
		echo "\033[s" . $package['name'] . "\033[0m\033[33G" . $package['version'] . "\033[0m\033[45G";
		$package_info =& $reg->getPackage($package['name']);
		if (is_null($package_info)) {
			$ret = 1;
			echo "\033[u\033[60G\033[1;31mNOK\033[0m\n";
		} else {
			$version = $package_info->getVersion();
			echo $version;
			if (version_compare($package['version'], $version, '<=')) {
				echo "\033[u\033[60G\033[1;32mOK\033[0m\n";
			} else {
				$ret = 1;
				echo "\033[u\033[60G\033[1;31mNOK\033[0m\n";
			}
		}
	}
	return($ret);
}

function install($packages) {
	$config =& PEAR_Config::singleton();
	$reg =& $config->getRegistry();
	PEAR_Command::setFrontendType('CLI');
	$cmd = PEAR_Command::factory('install', $config);
	$ret = 0;
	foreach ($packages as $package) {
		if (!$reg->packageExists($package['name'])) {
			echo "\033[s" . $package['name'] . "\033[0m\033[33G" . $package['version'] . "\033[0m\033[45G";
			$name = $package['name'];
			if (isset($package['status'])) {
				$name .= '-' . $package['status'];
			}
			ob_start();
			$ok = $cmd->run('install', array('soft' => true, 'onlyreqdeps' => true), array($name));
			ob_end_clean();
			$package_info =& $reg->getPackage($package['name']);
			if (!is_null($package_info)) {
				echo $package_info->getVersion();
				echo "\033[u\033[60G\033[1;32mOK\033[0m\n";
			} else {
				$ret = 1;
				echo "\033[u\033[60G\033[1;31mNOK\033[0m\n";
			}
		}
	}
	return($ret);
}

function upgrade($packages) {
	$config =& PEAR_Config::singleton();
	$reg =& $config->getRegistry();
	PEAR_Command::setFrontendType('CLI');
	$cmd = PEAR_Command::factory('install', $config);
	$ret = 0;
	foreach ($packages as $package) {
		$package_info =& $reg->getPackage($package['name']);
		if (is_null($package_info)) {
			continue;
		}
		
		if ($package['name'] == "PEAR") {
               $ok = $cmd->run('install', array('soft' => true, 'nodeps' => true, 'force' => true), array($package['name']));
        }
		
		$installed_version = $package_info->getVersion();
		if (version_compare($package['version'], $installed_version, '>')) {
			echo "\033[s" . $package['name'] . "\033[0m\033[33G" . $package['version'] . "\033[0m\033[45G" . $installed_version . "\t";
			$name = $package['name'];
			if (isset($package['status'])) {
				$name .= '-' . $package['status'];
			}
			ob_start();
			$ok = $cmd->run('install', array('soft' => true, 'onlyreqdeps' => true, 'upgrade' => true), array($name));
			ob_end_clean();
			$package_info =& $reg->getPackage($package['name']);
			if (!is_null($package_info)) {
				echo $package_info->getVersion();
				echo "\033[u\033[60G\033[1;32mOK\033[0m\n";
			} else {
				$ret = 1;
				echo "\033[u\033[60G\033[1;31mNOK\033[0m\n";
			}
		}
	}
}

if (count($argv) < 2 || count($argv) > 3) {
	fwrite(STDERR, "Incorrect number of arguments\n");
	usage();
	exit(2);
}

if (count($argv) == 3) {
	$file = $argv[2];
} else {
	$file = 'pear.lst';
}
check_file($file);

$packages = get_list($file);

require_once('PEAR.php');
require_once('PEAR/Config.php');
require_once('PEAR/Command.php');

$ret = 0;

switch ($argv[1]) {
	case 'check':
		$ret = check($packages);
		break;
	case 'install':
		$ret = install($packages);
		break;
	case 'upgrade':
		$ret = upgrade($packages);
		break;
	default:
		fwrite(STDERR, "Incorrect argument\n");
		usage();
		exit(2);
}

exit($ret);
?>
