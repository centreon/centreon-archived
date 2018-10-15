<?php
namespace CentreonRemote\Infrastructure\Export;

interface ExportParserInterface
{

    public static function parse(string $filename, callable $macros = null): array;

    public static function dump(array $input, string $filename): void;
}
