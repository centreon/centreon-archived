<?php

namespace CentreonModule\Tests\Application\Webservice;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use CentreonModule\Application\Webservice\CentreonModuleWebservice;
use CentreonModule\Infrastructure\Service\CentreonModuleService;
use CentreonModule\Infrastructure\Source\ModuleSource;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonModule\Tests\Infrastructure\Source\ModuleSourceTest;

class CentreonModuleWebserviceTest extends TestCase {

    protected function setUp() {
        // dependencies
        $container = new Container;
        $container['centreon.module'] = $this->createMock(CentreonModuleService::class, [
            'getList',
        ]);
        $container['centreon.module']
                ->method('getList')
                ->will($this->returnCallback(function() {
                            $funcArgs = func_get_args();

                            // prepare filters
                            $funcArgs[0] = $funcArgs[0] === null ? '-' : $funcArgs[0];
                            $funcArgs[1] = $funcArgs[1] === true ? '1' : ($funcArgs[1] !== false ? '-' : '0');
                            $funcArgs[2] = $funcArgs[2] === true ? '1' : ($funcArgs[2] !== false ? '-' : '0');
                            $funcArgs[3] = $funcArgs[3] ? implode('|', $funcArgs[3]) : '-';
                            $name = implode(',', $funcArgs);

                            $module = new Module;
                            $module->setId(ModuleSourceTest::$moduleName);
                            $module->setName($name);
                            $module->setAuthor('');
                            $module->setVersion('');
                            $module->setType(ModuleSource::TYPE);
                            return [
                                ModuleSource::TYPE => [
                                    $module,
                                ],
                            ];
                        }))
        ;

        $this->webservice = $this->createPartialMock(CentreonModuleWebservice::class, [
            'loadDb',
            'loadArguments',
            'loadToken',
            'query',
        ]);

        // load dependencies
        $this->webservice->setDi($container);
    }

    public function testGetList() {
        $filters = [];
        $this->webservice
                ->method('query')
                ->will($this->returnCallback(function() use (&$filters) {
                            return $filters;
                        }))
        ;
        $executeTest = function($controlJson) {
            $result = $this->webservice->getList();
            $this->assertInstanceOf(\JsonSerializable::class, $result);

            $json = json_encode($result);
            $this->assertEquals($controlJson, $json);
        };

        // without applied filters
        $executeTest('{"status":true,"result":{"module":{"pagination":{"total":1,"offset":0,"limit":1},"entities":[{"id":"test-module","type":"module","description":"-,-,-,-","label":"","version":{"current":null,"available":"","outdated":true},"license":null}]}}}');

        // with search, installed, updated, and selected type filter
        $filters['search'] = 'test';
        $filters['installed'] = 'true';
        $filters['updated'] = 'true';
        $filters['types'] = [ModuleSource::TYPE];
        $executeTest('{"status":true,"result":{"module":{"pagination":{"total":1,"offset":0,"limit":1},"entities":[{"id":"test-module","type":"module","description":"test,1,1,module","label":"","version":{"current":null,"available":"","outdated":true},"license":null}]}}}');

        // with not installed, not updated and not selected type filter
        unset($filters['search']);
        $filters['installed'] = 'false';
        $filters['updated'] = 'false';
        $filters['types'] = [];
        $executeTest('{"status":true,"result":{"module":{"pagination":{"total":1,"offset":0,"limit":1},"entities":[{"id":"test-module","type":"module","description":"-,0,0,-","label":"","version":{"current":null,"available":"","outdated":true},"license":null}]}}}');

        // with wrong values of installed and updated filters
        unset($filters['types']);
        $filters['installed'] = 'ture';
        $filters['updated'] = 'folse';
        $executeTest('{"status":true,"result":{"module":{"pagination":{"total":1,"offset":0,"limit":1},"entities":[{"id":"test-module","type":"module","description":"-,-,-,-","label":"","version":{"current":null,"available":"","outdated":true},"license":null}]}}}');
    }

    public function testAuthorize() {
        (function() {
            $result = $this->webservice->authorize(null, null, true);
            $this->assertTrue($result);
        })();

        (function() {
            $result = $this->webservice->authorize(null, null);
            $this->assertFalse($result);
        })();

        (function() {
            $user = $this->createMock(\CentreonUser::class);
            $user
                    ->method('hasAccessRestApiConfiguration')
                    ->will($this->returnCallback(function() {
                                return true;
                            }))
            ;

            $result = $this->webservice->authorize(null, $user);
            $this->assertTrue($result);
        })();
    }

    public function testGetName() {
        $this->assertEquals('centreon_module', CentreonModuleWebservice::getName());
    }

}
