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

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

// Load cached env vars if the .env.local.php file exists
// Run "composer dump-env prod" to create it (requires symfony/flex >=1.2)
if (file_exists(dirname(__DIR__) . '/.env.local.php')
    && is_array($env = @include dirname(__DIR__) . '/.env.local.php')
) {
    $_ENV += $env;
} elseif (!class_exists(Dotenv::class)) {
    throw new RuntimeException(
        'Please run "composer require symfony/dotenv" to load the ".env" files configuring the application.'
    );
} else {
    // load all the .env files
    (new Dotenv(false))->loadEnv(dirname(__DIR__) . '/.env');
}

$_SERVER += $_ENV;
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) ?: 'dev';
$_SERVER['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? 'prod' !== $_SERVER['APP_ENV'];
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] =
    (int) $_SERVER['APP_DEBUG']
    || filter_var($_SERVER['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0';

if (!isset($GLOBALS['constants']) && !isset($GLOBALS['conf_centreon'])) {
    $constants = [];
    $conf_centreon = [];
    include_once dirname(__DIR__) . "/config/centreon.config.php";
    (new Dotenv(false))->populate($constants);
    (new Dotenv(false))->populate($conf_centreon);
} else {
    (new Dotenv(false))->populate($GLOBALS['constants']);
    (new Dotenv(false))->populate($GLOBALS['conf_centreon']);
}

include_once dirname(__DIR__) . "/container.php";
