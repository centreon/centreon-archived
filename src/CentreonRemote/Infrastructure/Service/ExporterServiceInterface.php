<?php
namespace CentreonRemote\Infrastructure\Service;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Infrastructure\Export\ExportManifest;

interface ExporterServiceInterface
{

    public function __construct(ContainerInterface $services);

    public function setCommitment(ExportCommitment $commitment): void;

    public function setManifest(ExportManifest $manifest): void;

    public function export(int $remoteId): void;

    public function import(ExportManifest $manifest): void;

    public static function getName(): string;

    public static function order(): int;
}
