<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

// Creating container
use Pimple\Container;

$dependencyInjector = \Centreon\LegacyContainer::getInstance();

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

// Dynamically register service provider
Centreon\Infrastructure\Provider\AutoloadServiceProvider::register($dependencyInjector);

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
