<?php
namespace CentreonRemote\Infrastructure\Export;

use CentreonRemote\Infrastructure\Export\ExportParserInterface;
use Symfony\Component\Yaml\Yaml;

class ExportParserYaml implements ExportParserInterface
{

    public function parse(string $filename, callable $macros = null): array
    {
        if (!file_exists($filename)) {
            return [];
        }

        $content = file_get_contents($filename);

        if ($macros !== null) {
            $macros($content);
        }

        $value = Yaml::parse($content);

        return $value;
    }

    public function dump(array $input, string $filename): void
    {
        if (!$input) {
            return;
        }

        $yaml = Yaml::dump($input);

        file_put_contents($filename, $yaml);
    }
}
