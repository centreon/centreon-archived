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
        $exportPath = $this->getPath($exportPath);

        // make directory if missing
        if (!is_dir($exportPath)) {
            mkdir($exportPath, $this->commitment->getFilePermission(), true);
        }

        return $exportPath;
    }

    /**
     * Get path of export
     * 
     * @param string $exportPath
     * @return string
     */
    public function getPath(string $exportPath = null): string
    {
        $exportPath = $exportPath ?? $this->commitment->getPath() . '/' . $this->getName();

        return $exportPath;
    }

    /**
     * Get exported file
     * 
     * @param string $filename
     * @return string
     */
    public function getFile(string $filename): string
    {
        $exportFilepath = $this->getPath() . '/' . $filename;

        return $exportFilepath;
    }
}
