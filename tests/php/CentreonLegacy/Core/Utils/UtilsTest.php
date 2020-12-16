<?php
/**
 * Copyright 2019 Centreon
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
 */

namespace CentreonLegacy\Core\Utils;

use PHPUnit\Framework\TestCase;
use Pimple\Psr11\Container;
use Vfs\FileSystem;
use Vfs\Node\Directory;
use Vfs\Node\File;
use Centreon\Test\Mock\DependencyInjector\ServiceContainer;
use CentreonLegacy\Core\Utils;
use CentreonLegacy\ServiceProvider;
use CentreonLegacy\Core\Configuration\Configuration;
use Centreon\Test\Mock;

/**
 * @group CentreonLegacy
 * @group CentreonLegacy\Utils
 */
class UtilsTest extends TestCase
{

    public function setUp(): void
    {
        // mount VFS
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
        $this->fs->get('/')
            ->add('tmp', new Directory([]));

        $this->container = new ServiceContainer();
        $this->container[ServiceProvider::CONFIGURATION] = $this->createMock(Configuration::class);
        $this->container['configuration_db'] = new Mock\CentreonDB;
        $this->container['configuration_db']->addResultSet("SELECT 'OK';", []);

        $this->service = new Utils\Utils(new Container($this->container));
    }

    public function tearDown(): void
    {
        // unmount VFS
        $this->fs->unmount();

        $this->container->terminate();
        $this->container = null;
    }

    /**
     * @covers CentreonLegacy\Core\Utils\Utils::objectIntoArray
     */
    public function testObjectIntoArray()
    {
        $object = new \stdClass();
        $object->message = 'test';
        $object->subMessage = ['test'];

        $value = [
            'message' => 'test',
            'subMessage' => [
                'test',
            ],
        ];

        $result = $this->service->objectIntoArray($object);

        $this->assertEquals($result, $value);
    }

    /**
     * @covers CentreonLegacy\Core\Utils\Utils::objectIntoArray
     */
    public function testObjectIntoArrayWithSkippedKeys()
    {
        $object = new \stdClass();
        $object->message = 'test';
        $object->subMessage = ['test'];

        $value = [
            'message' => 'test',
        ];

        $result = $this->service->objectIntoArray($object, ['subMessage']);

        $this->assertEquals($result, $value);
    }

    /**
     * @covers CentreonLegacy\Core\Utils\Utils::objectIntoArray
     */
    public function testObjectIntoArrayWithEmptyObject()
    {
        $result = $this->service->objectIntoArray(new \stdClass);

        $this->assertEmpty($result);
    }

//    /**
//     * @ covers CentreonLegacy\Core\Utils\Utils::objectIntoArray
//     */
//    public function testExecutePhpFile()
//    {
//    }

    public function testBuildPath()
    {
        $endPath = '.';

        $result = $this->service->buildPath($endPath);

        $this->assertStringEndsWith('www', $result);
    }

    public function testRequireConfiguration()
    {
        $configurationFile = '';
        $type = '';

        $result = $this->service->requireConfiguration($configurationFile, $type);

        $this->assertEmpty($result);
    }

    /**
     * Unable to find the wrapper "vfs" can't be tested
     *
     * @todo the method must be refactored
     */
    public function testExecutePhpFileWithUnexistsFile()
    {
        $fileName = 'vfs://tmp/conf2.php';
        $result = null;

        try {
            $result = $this->service->executePhpFile($fileName);
        } catch (\Exception $ex) {
            $result = $ex;
        }

        $this->assertInstanceOf(\Exception::class, $result);
    }

    public function testExecuteSqlFile()
    {
        $this->fs->get('/tmp')
            ->add('conf.sql', new File("SELECT 'OK';"));
        $fileName = 'vfs://tmp/conf.sql';

        $result = $this->service->executeSqlFile($fileName);

        $this->assertEmpty($result);
    }

    public function testExecuteSqlFileWithWithUnexistsFileAndRealtimeDb()
    {
        $fileName = 'vfs://tmp/conf2.sql';
        $result = null;

        try {
            $this->service->executeSqlFile($fileName, [], true);
        } catch (\Exception $ex) {
            $result = $ex;
        }

        $this->assertInstanceOf(\Exception::class, $result);
    }
}
