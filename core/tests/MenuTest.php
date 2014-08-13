<?php

namespace Test\Centreon;

use \Centreon\Internal\Menu,
    \Centreon\Internal\Di,
    \Centreon\Internal\Config,
    \Centreon\Internal\Cache;

class MenuTest extends DbTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testGetMenu()
    {
        $menu = new Menu();
        $this->assertGreaterThan(0, count($menu->getMenu()));
    }
}
