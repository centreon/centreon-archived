<?php
namespace CentreonRemote\Tests\Infrastructure\Export;

use PHPUnit\Framework\TestCase;
use CentreonRemote\Infrastructure\Export\ExportParserYaml;
use Vfs\FileSystem;
use Vfs\Node\Directory;
use Vfs\Node\File;

/**
 * @group CentreonRemote
 */
class ExportParserYamlTest extends TestCase
{

    public function setUp()
    {
        // mount VFS
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
        $this->fs->get('/')->add('tmp', new Directory([]));

        $this->parser = new ExportParserYaml;
    }

    public function tearDown()
    {
        // unmount VFS
        $this->fs->unmount();
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportParserYaml::parse
     */
    public function testParse()
    {
        // non-existent file
        $result = $this->parser->parse('vfs://tmp/test.yml');

        $this->assertEquals([], $result);

        // add file
        $this->fs->get('/tmp')->add('test1.yml', new File('key: val'));

        $result = $this->parser->parse('vfs://tmp/test1.yml');

        $this->assertEquals(['key' => 'val'], $result);
        
        // add file with macros
        $this->fs->get('/tmp')->add('test2.yml', new File('key: @val@'));

        $result = $this->parser->parse('vfs://tmp/test2.yml', function(&$result){
            $result = str_replace('@val@', 'val', $result);
        });

        $this->assertEquals(['key' => 'val'], $result);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportParserYaml::dump
     */
    public function testDump()
    {
        $this->parser->dump([], 'vfs://tmp/test.yml');

        $this->assertFileNotExists('vfs://tmp/test.yml');

        $this->parser->dump(['key' => 'val'], 'vfs://tmp/test.yml');

        $this->assertFileExists('vfs://tmp/test.yml');
    }
}
