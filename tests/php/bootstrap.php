<?php
/**
 * Copyright 2016 Centreon
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
 */

// mock path constants to redirect to base centreon directory
$mockedPathConstants = ['_CENTREON_PATH_', '_CENTREON_ETC_', '_CENTREON_LOG_', '_CENTREON_CACHEDIR_'];
foreach ($mockedPathConstants as $mockedPathConstant) {
    if (!defined($mockedPathConstant)) {
        define($mockedPathConstant, realpath(__DIR__ . '/../../') . '/');
    }
}

$mockedPreRequisiteConstants = [
    '_CENTREON_PHP_VERSION_' => '8.0',
    '_CENTREON_MARIA_DB_MIN_VERSION_' => '10.5',
];
foreach ($mockedPreRequisiteConstants as $mockedPreRequisiteConstant => $value) {
    if (!defined($mockedPreRequisiteConstant)) {
        define($mockedPreRequisiteConstant, $value);
    }
}


// mock variable constants to redirect to base centreon directory
$mockedVarConstants = ['hostCentreon', 'hostCentstorage', 'user', 'password', 'db', 'dbcstg', 'port'];
foreach ($mockedVarConstants as $mockedVarConstant) {
    if (!defined($mockedVarConstant)) {
        define($mockedVarConstant, '');
    }
}

// Disable warnings for PEAR.
error_reporting(E_ALL & ~E_STRICT);

require_once realpath(__DIR__ . '/polyfill.php');
$loader = require realpath(__DIR__ . '/../../vendor/autoload.php');

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);

if (!function_exists('loadDependencyInjector')) {
    // Mock DB manager
    \Tests\Centreon\DependencyInjector::getInstance()[Centreon\ServiceProvider::CENTREON_DB_MANAGER] =
        new Centreon\Test\Mock\CentreonDBManagerService;

    function loadDependencyInjector()
    {
        return \Tests\Centreon\DependencyInjector::getInstance();
    }
}
