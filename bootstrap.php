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

// Calling PHP-DI
use Pimple\Container;

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(__DIR__ . '/www/class'),
    realpath(__DIR__ . '/www/lib'),
    get_include_path()
)));

// Centreon Autoload
spl_autoload_register(function ($sClass) {
    $fileName = $sClass;
    $fileName{0} = strtolower($fileName{0});
    $fileNameType1 = __DIR__ . "/www/class/" . $fileName . ".class.php";
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
require __DIR__ . '/vendor/autoload.php';

// Creating container
$dependencyInjector = new Container();

// Define Centreon Configuration Database Connection
$dependencyInjector['configuration_db'] = function ($c) {
    return new \CentreonDB('centreon');
};

// Define Centreon Realtime Database Connection
$dependencyInjector['realtime_db'] = function ($c) {
    return new \CentreonDB('centstorage');
};

// Define Centreon Rest Http Client
$dependencyInjector['rest_http'] = function ($c) {
    return new \CentreonRestHttp();
};

// Define filesystem
$dependencyInjector['filesystem'] = function ($c) {
    return new \Symfony\Component\Filesystem\Filesystem();
};

// Utils
$dependencyInjector['utils'] = function ($c) use ($dependencyInjector) {
    return $dependencyInjector[CentreonLegacy\ServiceProvider::CENTREON_LEGACY_UTILS];
};

// Define finder
$dependencyInjector['finder'] = $dependencyInjector->factory(function ($c) {
    return new \Symfony\Component\Finder\Finder();
});

// Define Language translator
$dependencyInjector['translator'] = $dependencyInjector->factory(function ($c) {
    global $centreon;
    $translator = new CentreonLang(_CENTREON_PATH_, $centreon);
    $translator->bindLang();
    $translator->bindLang('help');
    return $translator;
});

$dependencyInjector['path.files_generation'] = _CENTREON_PATH_ . '/filesGeneration/';

// Defines the web service that will transform the translation files into one json file
$dependencyInjector[CentreonI18n::class] = function ($container) {
    require_once _CENTREON_PATH_ . '/www/api/class/centreon_i18n.class.php';
    $lang = getenv('LANG');
    if ($lang === false) {
        // Initialize the language translator
        $container['translator'];
        $lang = getenv('LANG');
    }
    if (strstr($lang, '.UTF-8') === false) {
        $lang .= '.UTF-8';
    }
    $translationFile = _CENTREON_PATH_  . "www/locale/{$lang}/LC_MESSAGES/messages.ser";
    $translation = new CentreonI18n();
    $translation->setFilesGenerationPath($translationFile);
    return $translation;
};

// Centreon configuration files
$configFiles = $dependencyInjector['finder']
    ->files()
    ->name('*.config.php')
    ->depth('== 0')
    ->in(__DIR__ . '/config');
foreach ($configFiles as $configFile) {
    $configFileName = $configFile->getBasename();
    require __DIR__ . '/config/' . $configFileName;
}

// Dynamically register service provider
\Centreon\Infrastructure\Provider\AutoloadServiceProvider::register($dependencyInjector);