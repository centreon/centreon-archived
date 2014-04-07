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
 * @file : Mongo.php , UTF-8
 * @date : Nov 27, 2012 , 10:59:57 PM
 *
 */
require_once __DIR__ . '/../bootstrap.php';

use Desarrolla2\Cache\Cache;
use Desarrolla2\Cache\Adapter\Mongo;

$cache = new Cache(new Mongo('mongodb://localhost:27017'));

require_once __DIR__ . '/common.php';
