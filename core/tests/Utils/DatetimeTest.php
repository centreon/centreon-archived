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

namespace Test\Centreon\Utils;

use Centreon\Internal\Utils\Datetime;

class DatetimeTest extends \PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $this->assertEquals('2012-12-23 22:47:56', Datetime::format(1356299276));
    }

    public function testHumanReadable()
    {
        /* Diff in seconds */
        $diff = 47492387;
        $this->assertEquals(
            '1 year 6 months 4 days 16 hours 19 min 47 sec',
            Datetime::humanReadable(47492387)
        );
        $this->assertEquals(
            '1 year 6 months',
            Datetime::humanReadable(47492387, Datetime::PRECISION_FORMAT, 2)
        );
        $this->assertEquals(
            '1 year 6 months 4 days',
            Datetime::humanReadable(47492387, Datetime::PRECISION_CHAR, 25)
        );
        $this->assertEquals(
            '1 year',
            Datetime::humanReadable(47492387, Datetime::PRECISION_CHAR, 2)
        );
    }
}
