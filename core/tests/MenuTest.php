<?php

namespace Test\Centreon;

use \Centreon\Internal\Menu,
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
