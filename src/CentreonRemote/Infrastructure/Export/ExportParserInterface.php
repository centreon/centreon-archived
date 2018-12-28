<?php
namespace CentreonRemote\Infrastructure\Export;

interface ExportParserInterface
{

    public function parse(string $filename, callable $macros = null): array;

    public function dump(array $input, string $filename): void;
}
