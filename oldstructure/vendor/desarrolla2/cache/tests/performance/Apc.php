<?php

/**
 * This file is part of the Cache project.
 *
 * Copyright (c)
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @author : Daniel González <daniel.gonzalez@freelancemadrid.es>
 * @file : Apc.php , UTF-8
 * @date : Nov 27, 2012 , 11:08:15 PM
 *
 */

require_once __DIR__ . '/../bootstrap.php';

use Desarrolla2\Cache\Cache;
use Desarrolla2\Cache\Adapter\Apc;

$cache = new Cache(new Apc());

require_once __DIR__ . '/common.php';
