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

use Centreon\Internal\Utils\Status;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    public function testNumToString()
    {
        /* Test get status for host */
        $this->assertEquals(
            'Up',
            Status::numToString(0, Status::TYPE_HOST)
        );
        /* Test get status for service */
        $this->assertEquals(
            'Critical',
            Status::numToString(2, Status::TYPE_SERVICE)
        );
        /* Test get status for event */
        $this->assertEquals(
            'Unknown',
            Status::numToString(3, Status::TYPE_EVENT)
        );
        /* Test get status translated */
        $this->assertEquals(
            'Unknown',
            Status::numToString(3, Status::TYPE_EVENT, true)
        );
    }

    public function testNumToStringExceptionBadType()
    {
        $this->setExpectedException(
            '\OutOfBoundsException',
            "Status type or status number does not exists"
        );
        Status::numToString(1, 42);
    }

    public function testNumToStringExceptionBadStatus()
    {
        $this->setExpectedException(
            '\OutOfBoundsException',
            "Status type or status number does not exists"
        );
        Status::numToString(42, Status::TYPE_HOST);
    }

    public function testStringToNum()
    {
        /* Test get status for host */
        $this->assertEquals(
            0,
            Status::stringToNum('Up', Status::TYPE_HOST)
        );
        /* Test get status for service */
        $this->assertEquals(
            2,
            Status::stringToNum('Critical', Status::TYPE_SERVICE)
        );
        /* Test get status for event */
        $this->assertEquals(
            5,
            Status::stringToNum('Information', Status::TYPE_EVENT)
        );
    }

    public function testStringToNumExceptionBadType()
    {
        $this->setExpectedException(
            '\OutOfBoundsException',
            "Status type does not exists"
        );
        Status::stringToNum('Critical', 42);
    }

    public function testStringToNumExceptionBadStatus()
    {
        $this->setExpectedException(
            '\OutOfBoundsException',
            "Status text does not exists"
        );
        Status::stringToNum('BadStatus', Status::TYPE_HOST);
    }
}
