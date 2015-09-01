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

namespace Test\CentreonAdministration;

use \Test\Centreon\DbTestCase;
use CentreonAdministration\Internal\User;
use Centreon\Internal\Di;

class UserTest extends DbTestCase
{
    protected $user;
    protected $dataPath = '/modules/CentreonAdministrationModule/tests/data/json/';

    public function setUp()
    {
        parent::setUp();
        $this->user = new User(1);
    }

    public function testGetId()
    {
        $this->assertEquals(1, $this->user->getId());
    }

    public function testGetName()
    {
        $this->assertEquals('John Doe', $this->user->getName());
    }

    public function testGetLogin()
    {
        $this->assertEquals('jdoe', $this->user->getLogin());
    }

    public function testGetEmail()
    {
        $this->assertEquals('jdoe@localhost', $this->user->getEmail());
    }

    public function testIsAdmin()
    {
        $this->assertEquals(0, $this->user->isAdmin());
    }

    public function testGetHomePage()
    {
        $this->assertEquals('/centreon-customview', $this->user->getHomePage());
    }
}
