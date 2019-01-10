<?php
namespace CentreonModule\Tests\Infrastructure\Source;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as ContainerWrap;
use Symfony\Component\Finder\Finder;
use Vfs\FileSystem;
use Vfs\Node\Directory;
use Vfs\Node\File;
use Centreon\Test\Mock;
use Centreon\Test\Traits\TestCaseExtensionTrait;
use CentreonLegacy\Core\Module\License;
use CentreonModule\Infrastructure\Source\ModuleSource;
use CentreonModule\Infrastructure\Entity\Module;

class ModuleSourceTest extends TestCase
{

    use TestCaseExtensionTrait;

    public static $moduleName = 'test-module';
    public static $moduleInfo = [
        'rname' => 'Curabitur congue porta neque',
        'name' => 'test-module',
        'mod_release' => 'x.y.q',
        'infos' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent id ante neque.',
        'is_removeable' => '1',
        'author' => 'Centreon',
        'lang_files' => '0',
        'sql_files' => '1',
        'php_files' => '0',
    ];
    public static $sqlQueryVsData = [
        "SELECT `name` AS `id`, `mod_release` AS `version` FROM `modules_informations`" => [
            [
                'id' => 'test-module',
                'version' => 'x.y.z',
            ],
        ],
    ];

    protected function setUp()
    {
        // mount VFS
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
        $this->fs->get('/')->add('modules', new Directory([]));
        $this->fs->get('/modules')->add(static::$moduleName, new Directory([]));
        $this->fs->get('/modules/' . static::$moduleName)
            ->add(ModuleSource::CONFIG_FILE, new File(static::buildConfContent()))
        ;
        $this->fs->get('/modules/' . static::$moduleName)
            ->add(ModuleSource::LICENSE_FILE, new File(''))
        ;

        // provide services
        $container = new Container;
        $container['finder'] = new Finder;
        $container['centreon.legacy.license'] = $this->getMockBuilder(License::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        // DB service
        $container['centreon.db-manager'] = new Mock\CentreonDBManagerService;
        foreach (static::$sqlQueryVsData as $query => $data) {
            $container['centreon.db-manager']->addResultSet($query, $data);
        }
        
        $this->containerWrap = new ContainerWrap($container);

        $this->source = $this->getMockBuilder(ModuleSource::class)
            ->setMethods([
                '_getPath',
                '_getModuleConf',
                '_getLicenseFile',
            ])
            ->setConstructorArgs([
                $this->containerWrap,
            ])
            ->getMock()
        ;
        $this->source
            ->method('_getPath')
            ->will($this->returnCallback(function() {
                    $result = 'vfs://modules';

                    return $result;
                }))
        ;
        $this->source
            ->method('_getModuleConf')
            ->will($this->returnCallback(function() {
                    $result = [
                        ModuleSourceTest::$moduleName => ModuleSourceTest::$moduleInfo,
                    ];

                    return $result;
                }))
        ;
        $this->source
            ->method('_getLicenseFile')
            ->will($this->returnCallback(function() {
                    $result = 'vfs://modules/' .
                        ModuleSourceTest::$moduleName . '/' .
                        ModuleSource::LICENSE_FILE
                    ;

                    return $result;
                }))
        ;
    }

    public function tearDown()
    {
        // unmount VFS
        $this->fs->unmount();
    }

    public function testGetList()
    {
        $result = $this->source->getList();

        $this->assertTrue(is_array($result));

        $result2 = $this->source->getList('missing-module');
        $this->assertEquals([], $result2);
    }

    public function testCreateEntityFromConfig()
    {
        $configFile = static::getConfFilePath();
        $result = $this->source->createEntityFromConfig($configFile);

        $this->assertInstanceOf(Module::class, $result);
        $this->assertEquals(static::$moduleName, $result->getId());
        $this->assertEquals(ModuleSource::TYPE, $result->getType());
        $this->assertEquals(static::$moduleInfo['rname'], $result->getName());
        $this->assertEquals(static::$moduleInfo['author'], $result->getAuthor());
        $this->assertEquals(static::$moduleInfo['mod_release'], $result->getVersion());
        $this->assertEquals(true, $result->isInstalled());
        $this->assertEquals(true, $result->isUpdated());
    }
    
//    public function testGetModuleConf()
//    {
//        $moduleSource = new ModuleSource($this->containerWrap);
//        $result = $this->invokeMethod($moduleSource, '_getModuleConf', [static::getConfFilePath()]);
//        //'php://filter/read=string.rot13/resource=' . 
//    }
    
    public function testGetLicenseFile()
    {
        $moduleSource = new ModuleSource($this->containerWrap);
        $result = $this->invokeMethod($moduleSource, '_getLicenseFile', [static::getLicenseFilePath()]);
        
        $this->assertTrue(strpos($result, ModuleSource::LICENSE_FILE) > -1);
    }

    public static function getConfFilePath(): string
    {
        return 'vfs://modules/' . static::$moduleName . '/' . ModuleSource::CONFIG_FILE;
    }

    public static function getLicenseFilePath(): string
    {
        return 'vfs://modules/' . static::$moduleName . '/' . ModuleSource::LICENSE_FILE;
    }

    public static function buildConfContent(): string
    {
        $result = '<?php';
        $moduleName = static::$moduleName;

        foreach (static::$moduleInfo as $key => $data) {
            $result .= "\n\$module_conf['{$moduleName}']['{$key}'] = '{$data}'";
        }

        return $result;
    }
}
