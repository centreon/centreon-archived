<?php
namespace CentreonRemote\Tests\Infrastructure\Export;

use PHPUnit\Framework\TestCase;
use CentreonRemote\Infrastructure\Export\ExportParserJson;
use Vfs\FileSystem;
use Vfs\Node\Directory;
use Vfs\Node\File;

/**
 * @group CentreonRemote
 */
class ExportParserJsonTest extends TestCase
{

    public function setUp()
    {
        // mount VFS
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
        $this->fs->get('/')->add('tmp', new Directory([]));

        $this->parser = new ExportParserJson;
    }

    public function tearDown()
    {
        // unmount VFS
        $this->fs->unmount();
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportParserJson::parse
     */
    public function testParse()
    {
        // non-existent file
        $result = $this->parser->parse('vfs://tmp/test.json');

        $this->assertEquals([], $result);

        // add file
        $this->fs->get('/tmp')->add('test1.json', new File('"key":"val"'));

        $result = $this->parser->parse('vfs://tmp/test1.json');

        $this->assertEquals(['key' => 'val'], $result);
        
        // add file with macros
        $this->fs->get('/tmp')->add('test2.json', new File('"key":"@val@"'));

        $result = $this->parser->parse('vfs://tmp/test2.json', function (&$result) {
            $result = str_replace('@val@', 'val', $result);
        });

        $this->assertEquals(['key' => 'val'], $result);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportParserJson::dump
     */
    public function testDump()
    {
        $this->parser->dump([], 'vfs://tmp/test.json');

        $this->assertFileNotExists('vfs://tmp/test.json');

        $this->parser->dump(['key' => 'val'], 'vfs://tmp/test.json');

        $this->assertFileExists('vfs://tmp/test.json');
    }
}
