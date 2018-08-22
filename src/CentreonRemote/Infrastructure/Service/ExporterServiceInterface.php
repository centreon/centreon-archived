<?php
namespace CentreonRemote\Infrastructure\Service;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;

interface ExporterServiceInterface
{

    public function __construct(ContainerInterface $services);

    public function setCommitment(ExportCommitment $commitment): void;

    public function cleanup() : void;

    public function export(): void;

    public function import(): void;

    public static function getName(): string;
}
