<?php

namespace Test\Centreon;

use \Centreon\Internal\Menu,
    \Centreon\Internal\Di,
    \Centreon\Internal\Config,
    \Centreon\Internal\Cache;

class MenuTest extends DbTestCase
{
    private $menu;

    public function setUp()
    {
        parent::setUp();
        $this->menu = new Menu();
    }

    public function testGetMenu()
    {
        $menus = $this->menu->getMenu();
        $this->assertGreaterThan(0, count($menus));
    }
}
