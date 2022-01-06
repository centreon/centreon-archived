<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

namespace Centreon\Tests\Infrastructure\CentreonLegacyDB;

use PHPUnit\Framework\TestCase;
use Centreon\Infrastructure\Webservice\WebServiceAbstract;

class WebServiceAbstractTest extends TestCase
{
    /**
     * @var WebServiceAbstract|\PHPUnit\Framework\MockObject\MockObject
     */
    private $webservice;

    public function setUp(): void
    {
        $this->webservice = $this->getMockBuilder(WebServiceAbstract::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getName',
            ])
            ->getMockForAbstractClass();
    }

    public function testQuery()
    {
        $_GET = [
            'test1' => '1',
            'test2' => '2',
        ];

        $this->assertEquals($_GET, $this->webservice->query());
    }

    public function testQueryWithoutGet()
    {
        $_GET = null;
        
        $this->assertEquals([], $this->webservice->query());
    }

    public function testPayloadRaw()
    {
        $this->assertEquals('', $this->webservice->payloadRaw());
    }

    public function testPayload()
    {
        $webservice = $this->getMockBuilder(WebServiceAbstract::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'payloadRaw',
            ])
            ->getMockForAbstractClass();

        $webservice
             ->method('payloadRaw')
             ->will($this->returnValue('{"id":"1"}'));
        
        $this->assertEquals([
            'id' => '1',
        ], $webservice->payload());
    }

    public function testPayloadWithException()
    {
        $webservice = $this->getMockBuilder(WebServiceAbstract::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'payloadRaw',
            ])
            ->getMockForAbstractClass();

        $webservice
             ->method('payloadRaw')
             ->will($this->returnValue('{id":"1"}'));
        
        $this->assertEquals([], $webservice->payload());
    }
}
