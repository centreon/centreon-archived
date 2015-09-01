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

namespace Test\Centreon;

use Centreon\Internal\Menu,
    \Centreon\Internal\Di,
    \Centreon\Internal\Config,
    \Centreon\Internal\Cache;

class MenuTest extends DbTestCase
{
    public function testGetMenuWithoutId()
    {
        $menu = new Menu();
        $this->assertGreaterThan(0, count($menu->getMenu()));
    }

    public function testGetMenuWithId()
    {
        $menu = new Menu();
        $this->assertGreaterThan(0, count($menu->getMenu(1)));
    }


    public function testGetMenuWithUnknownId()
    {
        $menu = new Menu();
        $this->assertEquals(0, count($menu->getMenu(9999)));
    }


    public function testGetMenuJsonWithoutId()
    {
        $menu = new Menu();
        $this->assertEquals(json_encode($menu->getMenu()), $menu->getMenuJson());
    }

    public function testGetMenuJsonWithId()
    {
        $menu = new Menu();
        $this->assertContains('"menu_id":"1"', $menu->getMenuJson(1));
    }

    public function testGetMenuJsonWithUnknownId()
    {
        $menu = new Menu();
        $this->assertEquals('[]', $menu->getMenuJson(9999));
    }
}
