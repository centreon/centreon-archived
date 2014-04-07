<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

/* Define the path to configuration files */
define('CENTREON_ETC', '../config/');

$centreon_path = __DIR__;

ini_set('display_errors', 'On');

/* Add classpath to include path */
set_include_path($centreon_path . '/application/class/Centreon' . PATH_SEPARATOR . get_include_path());

require_once 'vendor/autoload.php';

spl_autoload_register(function ($classname) use ($centreon_path) {
    $filename = $centreon_path . '/application/class/' . str_replace('\\', '/', $classname) . '.php';
    if (file_exists($filename)) {
        require $filename;
    }
});

spl_autoload_register(function ($classname) use ($centreon_path) {
    $classname = strtolower($classname);
    $tmp = explode("\\", $classname);
    $shortname = $tmp[(count($tmp) - 1)];
    $filename = $centreon_path . '/application/' . str_replace('\\', '/', $classname) . '/'. $shortname .'.php';
    if (file_exists($filename)) {
        require $filename;
    }
});

spl_autoload_register(function ($classname) use ($centreon_path) {
    $tmp = explode("\\", $classname);
    $myClassName = array_pop($tmp);
    $rawpath = implode("/", $tmp);
    $path = strtolower($rawpath);
    $filename = $centreon_path . '/application/' . $path. '/' . $myClassName . '.php';
    if (file_exists($filename)) {
        require $filename;
    } else {
        $path = str_replace("Models", "models", $rawpath);
        $filename = $centreon_path . '/application/' . $path. '/' . $myClassName . '.php';
        if (file_exists($filename)) {
            require $filename;
        }
    }
});

require_once $centreon_path.'/application/functions/array.php';

try {
    $bootstrap = new \Centreon\Core\Bootstrap();
    $bootstrap->init();
} catch (\Exception $e) {
    echo $e;
}
