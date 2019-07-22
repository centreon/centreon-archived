<?php
namespace CentreonRemote\Infrastructure\Export;

use CentreonRemote\Infrastructure\Export\ExportParserInterface;

class ExportParserJson implements ExportParserInterface
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

        // true to return array
        $value = json_decode($content, true);

        return $value;
    }

    public function dump(array $input, string $filename): void
    {
        if (!$input) {
            return;
        }

        $json = json_encode($input);

        file_put_contents($filename, $json);
    }
}
