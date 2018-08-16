<?php
namespace CentreonRemote\Domain\Exporter\Traits;

/**
 * Export path method for the expoters
 */
trait ExportPathTrait
{

    /**
     * Create path for export
     * 
     * @param string $exportPath
     * @return string
     */
    public function createPath(string $exportPath = null): string
    {
        // Create export path
        $exportPath = $exportPath ?? $this->commitment->getPath() . '/' . $this->getName();

        if (!is_dir($exportPath)) {
            mkdir($exportPath, $this->commitment->getFilePermission(), true);
        }

        return $exportPath;
    }
}
