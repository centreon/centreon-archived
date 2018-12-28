<?php
namespace CentreonRemote\Tests\Infrastructure\Export;

use PHPUnit\Framework\TestCase;
use Centreon\Test\Traits\TestCaseExtensionTrait;
use CentreonRemote\Infrastructure\Export\ExportParserYaml;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Infrastructure\Export\ExportManifest;
use DateTime;

/**
 * @group CentreonRemote
 */
class ExportManifestTest extends TestCase
{

    use TestCaseExtensionTrait;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportCommitment
     */
    protected $commitment;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportManifest
     */
    protected $manifest;

    /**
     * @var string
     */
    protected $version = '18.10';

    /**
     * @var array
     */
    protected $dumpData = [];

    /**
     * Set up datasets and mocks
     */
    protected function setUp()
    {
        $parser = $this->getMockBuilder(ExportParserYaml::class)
            ->setMethods([
                'parse',
                'dump',
            ])
            ->getMock()
        ;
        $parser->method('parse')
            ->will($this->returnCallback(function() {
                    $args = func_get_args();
                    $file = $args[0];

                    return [];
                }))
        ;
        $parser->method('dump')
            ->will($this->returnCallback(function() {
                    $args = func_get_args();

                    $this->dumpData[$args[1]] = $args[0];
                }))
        ;

        $this->commitment = new ExportCommitment(1, [2, 3], null, $parser);

        $this->manifest = $this
            ->getMockBuilder(ExportManifest::class)
            ->setMethods([
                'removePath',
                'getFile',
            ])
            ->setConstructorArgs([
                $this->commitment,
                $this->version
            ])
            ->getMock()
        ;

        //->expects($this->any())
        $this->manifest
            ->method('removePath')
            ->will($this->returnCallback(function() {
                    $args = func_get_args();
                    $file = $args[0];

                    if (!file_exists($file)) {
                        return null;
                    }

                    return $file;
                }))
        ;

        $this->manifest
            ->method('getFile')
            ->will($this->returnCallback(function() {
                    return __FILE__;
                }))
        ;
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportManifest::__construct
     */
    public function testConstruct()
    {
        $this->assertAttributeInstanceOf(ExportCommitment::class, 'commitment', $this->manifest);
        $this->assertAttributeEquals($this->version, 'version', $this->manifest);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportManifest::addExporter
     */
    public function testAddExporter()
    {
        $value = 'TestExporter';
        $this->manifest->addExporter($value);

        $this->assertAttributeEquals([$value], 'exporters', $this->manifest);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportManifest::addFile
     */
    public function testAddFile()
    {
        $this->manifest->addFile('missing-file.txt');

        $this->assertAttributeEquals(null, 'files', $this->manifest);

        // check case if _removePath return null
        $result = $this->manifest->addFile(__FILE__ . __FILE__);

        $this->assertAttributeEquals(null, 'files', $this->manifest);
        $this->assertNull($result);

        // ExportManifest::_removePath is mocked
        $this->manifest->addFile(__FILE__);

        // chech $this->files
        $this->assertAttributeEquals([
            __FILE__ => md5_file(__FILE__),
            ], 'files', $this->manifest)
        ;
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportManifest::get
     */
    public function testGet()
    {
        $this->assertNull($this->manifest->get('missing-data'));
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportManifest::validate
     * @expectedException \Exception
     */
    public function testValidate()
    {
        $this->manifest->validate();

        // chech $this->files
        $this->assertAttributeEquals([], 'data', $this->manifest);
    }

    /**
     * @ covers \CentreonRemote\Infrastructure\Export\ExportManifest::dump
     */
    public function testDump()
    {
        $this->manifest->dump();

        $this->assertEquals([
            $this->manifest->getFile() => [
                'version' => $this->version,
                'datetime' => (new DateTime())->format(DateTime::W3C),
                'remote-poller' => $this->commitment->getRemote(),
                'pollers' => $this->commitment->getPollers(),
                'meta' => $this->commitment->getMeta(),
                'exporters' => null,
                'exports' => null,
            ],
            ], $this->dumpData);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportManifest::getFile
     */
    public function testGetFile()
    {
        $manifest = new ExportManifest($this->commitment, $this->version);
        $result = $manifest->getFile();

        $this->assertNotNull($result);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportManifest::removePath
     */
    public function testRemovePath()
    {
        $manifest = new ExportManifest($this->commitment, $this->version);
        $result = $manifest->removePath(__FILE__);

        $this->assertNull($result);

        $result = $manifest->removePath($this->commitment->getPath()
            . ExportManifest::EXPORT_FILE)
        ;

        $this->assertEquals(ExportManifest::EXPORT_FILE, $result);
    }
}
