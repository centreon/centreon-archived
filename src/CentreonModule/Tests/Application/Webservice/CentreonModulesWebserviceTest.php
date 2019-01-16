<?php

namespace CentreonModule\Tests\Application\Webservice;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Centreon\Test\Mock\CentreonDB;
use Symfony\Component\Finder\Finder;
use CentreonModule\Application\Webservice\CentreonModulesWebservice;

class CentreonModulesWebserviceTest extends TestCase {

    public static $sqlQueriesWitoutData = [
        'SELECT * FROM modules_informations ' => [],
    ];

    public static $sqlQueries = [
        'SELECT * FROM modules_informations ' => [
            [
                'id' => '1',
                'name' => 'centreon-bam-server',
                'rname' => 'centreon-bam-server',
                'mod_release' => '',
                'is_removable' => '1',
                'infos' => '',
                'author' => '',
                'lang_files' => '1',
                'sql_files' => '1',
                'php_files' => '1',
                'svc_tools' => '0',
                'host_tools' => '0',
            ],
        ],
    ];

    protected function setUp() {
        $this->webservice = $this->createPartialMock(CentreonModulesWebservice::class, [
            'loadDb',
            'loadArguments',
            'loadToken',
        ]);
    }

    public function testPostGetBamModuleInfo() {
        // dependencies
        $container = new Container;
        $container['finder'] = new Finder;
        $container['configuration_db'] = new CentreonDB;

        // load dependencies
        $this->webservice->setDi($container);

        foreach (static::$sqlQueriesWitoutData as $query => $data) {
            $container['configuration_db']->addResultSet($query, []);
        }

        $result = $this->webservice->postGetBamModuleInfo();
        $this->assertArrayHasKey('enabled', $result);
        $this->assertFalse($result['enabled']);

        // reset
        $container['configuration_db']->resetResultSet();

        foreach (static::$sqlQueries as $query => $data) {
            $container['configuration_db']->addResultSet($query, $data);
        }

        $result = $this->webservice->postGetBamModuleInfo();
        $this->assertArrayHasKey('enabled', $result);
        $this->assertTrue($result['enabled']);
    }

    public function testAuthorize() {
        $result = $this->webservice->authorize(null, null);
        $this->assertTrue($result);
    }

    public function testGetName() {
        $this->assertEquals('centreon_modules_webservice', CentreonModulesWebservice::getName());
    }

}
