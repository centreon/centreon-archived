<?php

namespace Tests\Centreon\Domain;

use Centreon\Domain\VersionHelper;
use PHPUnit\Framework\TestCase;

class VersionHelperTest extends TestCase
{

    public function testCompare()
    {
        $this->assertFalse(VersionHelper::compare('1', '2', VersionHelper::EQUAL));
        $this->assertTrue(VersionHelper::compare('1', '1.0', VersionHelper::EQUAL));
        $this->assertTrue(VersionHelper::compare('1.2', '1.0', VersionHelper::GT));
        $this->assertTrue(VersionHelper::compare('1.2', '1.0', VersionHelper::GE));
        $this->assertTrue(VersionHelper::compare('1.2', '1.2', VersionHelper::GE));
        $this->assertTrue(VersionHelper::compare('1.0', '2.0', VersionHelper::LT));
        $this->assertTrue(VersionHelper::compare('1.2', '2.0', VersionHelper::LE));
        $this->assertTrue(VersionHelper::compare('1.2', '2.0', VersionHelper::LE));
        $this->assertTrue(VersionHelper::compare('1.1.0', '1.1', VersionHelper::EQUAL));
    }

    public function testRegularizeDepthVersion()
    {
        $this->assertEquals('1.0.0', VersionHelper::regularizeDepthVersion('1', 2));
        $this->assertEquals('2.1.0', VersionHelper::regularizeDepthVersion('2.1', 2));
        $this->assertEquals('2.2', VersionHelper::regularizeDepthVersion('2.2', 1));
        $this->assertEquals('9', VersionHelper::regularizeDepthVersion('9.8.5', 0));
        $this->assertEquals('9.8', VersionHelper::regularizeDepthVersion('9.8.6', 1));
    }
}
