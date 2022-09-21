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
use VirtualFileSystem\FileSystem;

/**
 * @group CentreonRemote
 */
class ExportParserJsonTest extends TestCase
{
    /**
     * @var FileSystem
     */
    private $fs;

    /**
     * @var ExportParserJson
     */
    private $parser;

    public function setUp(): void
    {
        // mount VFS
        $this->fs = new FileSystem();
        $this->fs->createDirectory('/tmp');
        $this->parser = new ExportParserJson();
    }

    public function tearDown(): void
    {
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportParserJson::parse
     */
    public function testParse(): void
    {
        // non-existent file
        $result = $this->parser->parse($this->fs->path('/tmp/test.json'));

        $this->assertEquals([], $result);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportParserJson::parse
     */
    public function testParse2(): void
    {
        // add file
        $this->fs->createFile('/tmp/test1.json', '{"key":"val"}');
        $result = $this->parser->parse($this->fs->path('/tmp/test1.json'));

        $this->assertEquals(['key' => 'val'], $result);
    }

    /**
     * @covers \CentreonRemote\Infrastructure\Export\ExportParserJson::parse
     */
    public function testParse3(): void
    {
        // add file with macros
        $this->fs->createFile('/tmp/test2.json', '{"key":"@val@"}');

        $result = $this->parser->parse(
            $this->fs->path('/tmp/test2.json'),
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
        $this->parser->dump([], $this->fs->path('/tmp/test.json'));

        $this->assertFileDoesNotExist($this->fs->path('/tmp/test.json'));
    }
}
