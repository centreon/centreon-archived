<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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
 *
 * For more information : contact@centreon.com
 *
 */

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
    /**
     *
     * @var FileSystem
     */
    private $fs;

    /**
     *
     * @var ExportParserJson
     */
    private $parser;

    public function setUp(): void
    {
        // mount VFS
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
        $this->fs->get('/')->add('tmp', new Directory([])); /** @phpstan-ignore-line */
        $this->parser = new ExportParserJson();
    }

    public function tearDown(): void
    {
        // unmount VFS
        $this->fs->unmount();
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportParserJson::parse
     */
    public function testParse(): void
    {
        // non-existent file
        $result = $this->parser->parse('vfs://tmp/test.json');

        $this->assertEquals([], $result);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportParserJson::parse
     */
    public function testParse2(): void
    {
        // add file
        $this->fs->get('/tmp')->add('test1.json', new File('{"key":"val"}')); /** @phpstan-ignore-line */

        $result = $this->parser->parse('vfs://tmp/test1.json');

        $this->assertEquals(['key' => 'val'], $result);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportParserJson::parse
     */
    public function testParse3(): void
    {
        // add file with macros
        $this->fs->get('/tmp')->add('test2.json', new File('{"key":"@val@"}')); /** @phpstan-ignore-line */

        $result = $this->parser->parse(
            'vfs://tmp/test2.json',
            function (&$result) {
                $result = str_replace('@val@', 'val', $result);
            }
        );

        $this->assertEquals(['key' => 'val'], $result);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportParserJson::dump
     */
    public function testDump(): void
    {
        $this->parser->dump([], 'vfs://tmp/test.json');

        $this->assertFileDoesNotExist('vfs://tmp/test.json');
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportParserJson::dump
     */
    public function testDump2(): void
    {
        $this->parser->dump(['key' => 'val'], 'vfs://tmp/test.json');

        $this->assertFileExists('vfs://tmp/test.json');
    }
}
