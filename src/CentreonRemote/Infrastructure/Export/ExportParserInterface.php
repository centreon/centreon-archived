<?php
namespace CentreonRemote\Infrastructure\Export;

interface ExportParserInterface
{

    public static function parse(string $filename): array;

    public static function dump(array $input, string $filename): void;
}
