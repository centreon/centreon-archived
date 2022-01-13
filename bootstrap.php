<?php
/*
 * Copyright 2005-2017 Centreon
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
 * Centreon also meet, for each linked independent module, the terms and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(__DIR__ . '/www/class'),
    realpath(__DIR__ . '/www/lib'),
    get_include_path()
)));

// Centreon Autoload
spl_autoload_register(function ($sClass) {
    $fileName = lcfirst($sClass);
    $fileNameType1 = __DIR__ ."/www/class/" . $fileName . ".class.php";
    $fileNameType2 = __DIR__ . "/www/class/" . $fileName . ".php";

    if (file_exists($fileNameType1)) {
        require_once $fileNameType1;
    } elseif (file_exists($fileNameType2)) {
        require_once $fileNameType2;
    }
});

function loadDependencyInjector()
{
    global $dependencyInjector;
    return $dependencyInjector;
}

// require composer file
$loader = require __DIR__ . '/vendor/autoload.php';

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);

require_once __DIR__ . "/container.php";
