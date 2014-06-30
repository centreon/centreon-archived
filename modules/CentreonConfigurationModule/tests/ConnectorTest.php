<?php

namespace Test\CentreonConfiguration;

require_once './tests/DbTestCase.php';

use \Test\Centreon\DbTestCase;

class ConnectorTest extends DbTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testListing()
    {
        $this->assertEquals(1, $this->getConnection()->getRowCount('connector'));
    }
}
