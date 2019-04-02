<?php
namespace CentreonRemote\Tests\Infrastructure\Export;

use PHPUnit\Framework\TestCase;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Infrastructure\Export\ExportParserYaml;
use CentreonRemote\Infrastructure\Export\ExportParserInterface;

/**
 * @group CentreonRemote
 */
class ExportCommitmentTest extends TestCase
{

    protected $commitment;
    protected $remote = 1;
    protected $pollers = [2, 3];
    protected $meta = [
        '',
    ];
    protected $path = '/tmp';
    protected $exporters = [];

    protected function setUp()
    {
        $parser = $this->getMockBuilder(ExportParserYaml::class)
            ->setMethods(['parse', 'dump'])
            ->getMock()
        ;

        $this->commitment = new ExportCommitment($this->remote, $this->pollers, $this->meta, $parser, $this->path, $this->exporters);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getRemote
     */
    public function testGetRemote()
    {
        $this->assertEquals($this->remote, $this->commitment->getRemote());
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getPollers
     */
    public function testGetPollers()
    {
        $result = array_merge($this->pollers, [$this->remote]);
        $this->assertEquals($result, $this->commitment->getPollers());
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getMeta
     */
    public function testGetMeta()
    {
        $this->assertEquals($this->meta, $this->commitment->getMeta());
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getPath
     */
    public function testGetPath()
    {
        $this->assertEquals($this->path, $this->commitment->getPath());
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getExporters
     */
    public function testGetExporters()
    {
        $this->assertEquals($this->exporters, $this->commitment->getExporters());
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getFilePermission
     */
    public function testGetFilePermission()
    {
        $this->assertEquals(0777, $this->commitment->getFilePermission());
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportCommitment::getParser
     */
    public function testGetParser()
    {
        $this->assertInstanceOf(ExportParserInterface::class, $this->commitment->getParser());
    }
}
