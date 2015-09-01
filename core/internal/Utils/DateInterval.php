<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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

namespace Centreon\Internal\Utils;

/**
 * Class for simulate DateInterval and fix bug https://bugs.php.net/bug.php?id=45545
 *
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Core
 */
class DateInterval
{
    public $y = 0;
    public $m = 0;
    public $d = 0;
    public $h = 0;
    public $i = 0;
    public $s = 0;

    /**
     * Constructor
     *
     * Load a interval from a time in second
     *
     * @param int $timestamp time in second
     */
    public function __construct($timestamp)
    {
        $this->y = intval($timestamp / (60 * 60 * 24 * 365));
        $timestamp -= $this->y * 60 * 60 * 24 * 365;
        $this->m = intval($timestamp / (60 * 60 * 24 * 30));
        $timestamp -= $this->m * 60 * 60 * 24 * 30;
        $this->d = intval($timestamp / (60 * 60 * 24));
        $timestamp -= $this->d * 60 * 60 * 24;
        $this->h = intval($timestamp / (60 * 60));
        $timestamp -= $this->h * 60 * 60;
        $this->i = intval($timestamp / 60);
        $timestamp -= $this->i * 60;
        $this->s = $timestamp;
    }
}
