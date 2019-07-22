<?php
namespace CentreonRemote\Domain\Exporter;

use CentreonRemote\Infrastructure\Export\ExportManifest;
use CentreonRemote\Infrastructure\Service\ExporterServiceAbstract;
use Centreon\Domain\Entity;
use Centreon\Domain\Repository;

class MediaExporter extends ExporterServiceAbstract
{

    const NAME = 'media';
    const MEDIA_PATH = _CENTREON_PATH_ . 'www/img/media';

    /**
     * Export data
     */
    public function export(): void
    {
        // create path
        $this->createPath();
        // Call to Quentin exporter        
    }

    /**
     * Import data
     * 
     * @param \CentreonRemote\Infrastructure\Export\ExportManifest $manifest
     */
    public function import(ExportManifest $manifest): void
    {
        // skip if no data
        if (!is_dir($this->getPath())) {
            return;
        }
        
        $import = $manifest->get("import");
        // foreach ($import[media] as $media) {
            // Import based on manifest
        // }
    }

    public static function order(): int
    {
        return 100;
    }
}
