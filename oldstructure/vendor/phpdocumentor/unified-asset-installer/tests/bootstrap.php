<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

error_reporting(E_ALL);

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('Composer\Test', __DIR__.'/../vendor/composer/composer/tests');
$loader->add('phpDocumentor', __DIR__.'/unit');
