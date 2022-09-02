<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

session_start();
require_once __DIR__ . '/../../../../bootstrap.php';
require_once '../../steps/functions.php';

function recurseRmdir($dir)
{
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? recurseRmdir("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

function recurseCopy($source, $dest)
{
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    if (is_file($source)) {
        return copy($source, $dest);
    }

    if (!is_dir($dest)) {
        mkdir($dest);
    }

    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        recurseCopy("$source/$entry", "$dest/$entry");
    }

    $dir->close();
    return true;
}

$parameters = filter_input_array(INPUT_POST);
$current = filter_var($_POST['current'] ?? "step 5", FILTER_SANITIZE_STRING);

if ($parameters) {
    if ((int)$parameters["send_statistics"] === 1) {
        $query = "INSERT INTO options (`key`, `value`) VALUES ('send_statistics', '1')";
    } else {
        $query = "INSERT INTO options (`key`, `value`) VALUES ('send_statistics', '0')";
    }

    $db = $dependencyInjector['configuration_db'];
    $db->query("DELETE FROM options WHERE `key` = 'send_statistics'");
    $db->query($query);
}

$name = 'install-' . $_SESSION['CURRENT_VERSION'] . '-' . date('Ymd_His');
$completeName = _CENTREON_VARLIB_ . '/installs/' . $name;
$sourceInstallDir = str_replace('step_upgrade', '', realpath(dirname(__FILE__) . '/../'));

try {
    if (recurseCopy($sourceInstallDir, $completeName)) {
        recurseRmdir($sourceInstallDir);
    }
} catch (Exception $e) {
    exitUpgradeProcess(1, $current, '', $e->getMessage());
}

session_destroy();
