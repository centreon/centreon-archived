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

namespace Centreon\Tests\Infrastructure\Provider;

use PHPUnit\Framework\TestCase;
use Centreon\Infrastructure\Provider\AutoloadServiceProvider;
use Centreon\Tests\Resources\Mock\ServiceProvider;
use Centreon\Tests\Resources\CheckPoint;
use Pimple\Container;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class AutoloadServiceProviderTest extends TestCase
{
    public function setUp()
    {
        $this->checkPoint = (new CheckPoint)
            ->add('finder.files')
            ->add('finder.name')
            ->add('finder.depth')
            ->add('finder.in');

        $this->finder = $this->createMock(Finder::class);
        $this->finder->method('files')
            ->will($this->returnCallback(function () {
                $this->checkPoint->mark('finder.files');

                return $this->finder;
            }));
        $this->finder->method('name')
            ->will($this->returnCallback(function ($name) {
                $this->checkPoint->mark('finder.name');

                $this->assertEquals('ServiceProvider.php', $name);

                return $this->finder;
            }));
        $this->finder->method('depth')
            ->will($this->returnCallback(function () {
                $this->checkPoint->mark('finder.depth');

                return $this->finder;
            }));
        $this->finder->method('in')
            ->will($this->returnCallback(function () {
                $this->checkPoint->mark('finder.in');

                return $this->finder;
            }));
    }

    public function testRegister()
    {
        $this->checkPoint
            ->add('finder.getIterator')
            ->add('finder.getIterator.getRelativePath1')
            ->add('finder.getIterator.getRelativePath2');

        $this->finder->method('getIterator')
            ->will($this->returnCallback(function () {
                $this->checkPoint->mark('finder.getIterator');

                $fileInfo = $this->createMock(SplFileInfo::class);
                $fileInfo->method('getRelativePath')
                    ->will($this->returnCallback(function () {
                        $this->checkPoint->mark('finder.getIterator.getRelativePath1');

                        return 'Centreon\\Tests\\Resource\\Mock';
                    }));

                $fileInfo2 = $this->createMock(SplFileInfo::class);
                $fileInfo2->method('getRelativePath')
                    ->will($this->returnCallback(function () {
                        $this->checkPoint->mark('finder.getIterator.getRelativePath2');

                        return 'Centreon\\Tests\\Resource\\Mock\\NonExistent';
                    }));

                return new \ArrayIterator([
                    $fileInfo,
                    $fileInfo2,
                ]);
            }));

        $container = new Container;
        $container['finder'] = $this->finder;
        
        
        AutoloadServiceProvider::register($container);

        $this->checkPoint->assert($this);

        $this->assertArrayHasKey(ServiceProvider::DUMMY_SERVICE, $container);
        $this->assertTrue($container[ServiceProvider::DUMMY_SERVICE]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode \Centreon\Infrastructure\Provider\AutoloadServiceProvider::ERR_TWICE_LOADED
     */
    public function testRegisterWithException()
    {
        $this->finder->method('getIterator')
            ->will($this->returnCallback(function () {
                $fileInfo = $this->createMock(SplFileInfo::class);
                $fileInfo->method('getRelativePath')
                    ->willReturn('Centreon\\Tests\\Resource\\Mock');

                return new \ArrayIterator([
                    $fileInfo,
                    $fileInfo,
                ]);
            }));

        $container = new Container;
        $container['finder'] = $this->finder;
        
        
        AutoloadServiceProvider::register($container);
    }
}
