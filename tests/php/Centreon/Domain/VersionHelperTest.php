<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

namespace Tests\Centreon\Domain;

use Centreon\Domain\VersionHelper;
use PHPUnit\Framework\TestCase;

class VersionHelperTest extends TestCase
{
    public function testCompareWithPoint()
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

    public function testRegularizeDepthVersionWithPoint()
    {
        $this->assertEquals('1.0.0', VersionHelper::regularizeDepthVersion('1', 2));
        $this->assertEquals('2.1.0', VersionHelper::regularizeDepthVersion('2.1', 2));
        $this->assertEquals('2.2', VersionHelper::regularizeDepthVersion('2.2', 1));
        $this->assertEquals('9', VersionHelper::regularizeDepthVersion('9.8.5', 0));
        $this->assertEquals('9.8', VersionHelper::regularizeDepthVersion('9.8.6', 1));
    }

    public function testCompareWithComma()
    {
        $this->assertFalse(VersionHelper::compare('1', '2', VersionHelper::EQUAL));
        $this->assertTrue(VersionHelper::compare('1', '1,0', VersionHelper::EQUAL));
        $this->assertTrue(VersionHelper::compare('1,2', '1,0', VersionHelper::GT));
        $this->assertTrue(VersionHelper::compare('1,2', '1,0', VersionHelper::GE));
        $this->assertTrue(VersionHelper::compare('1,2', '1,2', VersionHelper::GE));
        $this->assertTrue(VersionHelper::compare('1,0', '2,0', VersionHelper::LT));
        $this->assertTrue(VersionHelper::compare('1,2', '2,0', VersionHelper::LE));
        $this->assertTrue(VersionHelper::compare('1,2', '2,0', VersionHelper::LE));
        $this->assertTrue(VersionHelper::compare('1,1,0', '1,1', VersionHelper::EQUAL));
    }

    public function testRegularizeDepthVersionWithComma()
    {
        $this->assertEquals('1,0,0', VersionHelper::regularizeDepthVersion('1', 2, ','));
        $this->assertEquals('2,1,0', VersionHelper::regularizeDepthVersion('2,1', 2, ','));
        $this->assertEquals('2,2', VersionHelper::regularizeDepthVersion('2,2', 1, ','));
        $this->assertEquals('9', VersionHelper::regularizeDepthVersion('9,8,5', 0, ','));
        $this->assertEquals('9,8', VersionHelper::regularizeDepthVersion('9,8,6', 1, ','));
    }
}
