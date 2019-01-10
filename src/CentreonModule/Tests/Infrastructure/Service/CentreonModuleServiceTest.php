<?php

namespace CentreonModule\Tests\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as ContainerWrap;
use Centreon\Test\Mock;
use Centreon\Test\Traits\TestCaseExtensionTrait;
use CentreonModule\Infrastructure\Service\CentreonModuleService;
use CentreonModule\Infrastructure\Source;
use CentreonModule\Tests\Infrastructure\Source\ModuleSourceTest;
use CentreonModule\Tests\Infrastructure\Source\WidgetSourceTest;

class CentreonModuleServiceTest extends TestCase {

    use TestCaseExtensionTrait;

    protected function setUp() {
        $this->service = $this->getMockBuilder(CentreonModuleService::class)
                ->setMethods([
                    '_initSources',
                ])
                ->setConstructorArgs([new ContainerWrap(new Container)])
                ->getMock()
        ;

        $sources = [];
        $sourcesTypes = [
            Source\ModuleSource::TYPE => Source\ModuleSource::class,
            Source\WidgetSource::TYPE => Source\WidgetSource::class,
        ];

        foreach ($sourcesTypes as $type => $class) {
            $sources[$type] = $this
                    ->getMockBuilder($class)
                    ->disableOriginalConstructor()
                    ->setMethods([
                        'getList',
                    ])
                    ->getMock()
            ;

            $sources[$type]
                    ->method('getList')
                    ->will($this->returnCallback(function() use ($type) {
                                return $type;
                            }))
            ;
        }

        // load sources
        $this->setProtectedProperty($this->service, 'sources', $sources);
    }

    public function testGetList() {
        (function() {
            $result = $this->service->getList();

            $this->assertArrayHasKey(Source\ModuleSource::TYPE, $result);
            $this->assertArrayHasKey(Source\WidgetSource::TYPE, $result);
        })();

        (function() {
            $result = $this->service->getList(null, null, null, [Source\ModuleSource::TYPE]);

            $this->assertArrayHasKey(Source\ModuleSource::TYPE, $result);
            $this->assertArrayNotHasKey(Source\WidgetSource::TYPE, $result);
        })();

        (function() {
            $result = $this->service->getList(null, null, null, ['missing-type']);

            $this->assertArrayNotHasKey(Source\ModuleSource::TYPE, $result);
            $this->assertArrayNotHasKey(Source\WidgetSource::TYPE, $result);
        })();
    }

    /**
     * @covers \CentreonModule\Infrastructure\Service\CentreonModuleService::_initSources
     */
    public function testInitSources() {
        $container = new Container;
        $container['finder'] = null;
        $container['centreon.legacy.license'] = null;
        $container['centreon.db-manager'] = new Mock\CentreonDBManagerService;

        // Data sets
        $queries = array_merge(ModuleSourceTest::$sqlQueryVsData, WidgetSourceTest::$sqlQueryVsData);
        foreach ($queries as $key => $result) {
            $container['centreon.db-manager']->addResultSet($key, $result);
        }

        $service = new CentreonModuleService(new ContainerWrap($container));

        $sources = $this->getProtectedProperty($service, 'sources');

        $this->assertArrayHasKey(Source\ModuleSource::TYPE, $sources);
        $this->assertArrayHasKey(Source\WidgetSource::TYPE, $sources);

        $this->assertInstanceOf(Source\ModuleSource::class, $sources[Source\ModuleSource::TYPE]);
        $this->assertInstanceOf(Source\WidgetSource::class, $sources[Source\WidgetSource::TYPE]);
    }

}
