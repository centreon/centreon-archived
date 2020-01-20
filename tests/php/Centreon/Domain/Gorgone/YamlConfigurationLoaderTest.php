<?php

namespace Centreon\Domain\Gorgone;

use PHPUnit\Framework\TestCase;

class YamlConfigurationLoaderTest extends TestCase
{
    /**
     * This test is designed to test the ability to load files and test include calls.
     *
     * @throws \FileNotFoundException
     */
    public function testLoad()
    {
        $gcl = new YamlConfigurationLoader(__DIR__ . '/root_file.yaml');
        $configuration = $gcl->load();
        $this->assertArrayHasKey('name', $configuration);
        $this->assertEquals($configuration['name'], 'text1');

        $this->assertArrayHasKey('tab', $configuration);
        $this->assertIsArray($configuration['tab']);
        $this->assertArrayHasKey('key1', $configuration['tab']);
        $this->assertEquals($configuration['tab']['key1'], 'value1');
        $this->assertArrayHasKey('key2', $configuration['tab']);
        $this->assertEquals($configuration['tab']['key2'], 'value2');

        $this->assertArrayHasKey('child', $configuration);
        $this->assertIsArray($configuration['child']);
        $this->assertArrayHasKey('child_key', $configuration['child']);
        $this->assertEquals($configuration['child']['child_key'], 'value_child_key');
        $this->assertArrayHasKey('child_key2', $configuration['child']);
        $this->assertIsArray($configuration['child']['child_key2']);
        $this->assertArrayHasKey('loop', $configuration['child']['child_key2']);
        $this->assertEquals($configuration['child']['child_key2']['loop'], 'no loop');

        $this->assertArrayHasKey('extra', $configuration['child']['child_key2']);
        $this->assertIsArray($configuration['child']['child_key2']['extra']);
        $this->assertArrayHasKey(0, $configuration['child']['child_key2']['extra']);
        $this->assertArrayHasKey(1, $configuration['child']['child_key2']['extra']);
    }

    /**
     * This test is designed to detect a loop in file calls.
     *
     * @throws \FileNotFoundException
     */
    public function testLoadWithLoop()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp("/^Loop detected in file.*child5\.yaml$/");
        $gcl = new YamlConfigurationLoader(__DIR__ . '/root_file_with_loop.yaml');
        $configuration = $gcl->load();
    }

    /***
     * This test is designed to test the FileNotFound Exception
     *
     * @throws \FileNotFoundException
     */
    public function testFileNotFound()
    {
        $this->expectException(\FileNotFoundException::class);
        $this->expectExceptionMessageRegExp("/^The configuration file '.*no_file\.yaml' does not exists$/");
        $gcl = new YamlConfigurationLoader(__DIR__ . '/no_file.yaml');
        $configuration = $gcl->load();
    }
}
