<?php
namespace CentreonRemote\Infrastructure\Export;

use CentreonRemote\Infrastructure\Export\ExportParserInterface;
use Symfony\Component\Yaml\Yaml;

class ExportParserYaml implements ExportParserInterface
{

    public static function parse(string $filename): array
    {
        $value = Yaml::parseFile($filename);

        return $value;
    }

    public static function dump(array $input, string $filename): void
    {
        $yaml = Yaml::dump($input);

        file_put_contents($filename, $yaml);
    }
}
