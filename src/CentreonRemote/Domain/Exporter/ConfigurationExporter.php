<?php
namespace CentreonRemote\Domain\Exporter;

use CentreonRemote\Infrastructure\Export\ExportManifest;
use CentreonRemote\Infrastructure\Service\ExporterServiceAbstract;
use Centreon\Domain\Repository;

class ConfigurationExporter extends ExporterServiceAbstract
{

    const NAME = 'configuration';

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

        $db = $this->db->getAdapter('configuration_db');

        // start transaction
        $db->beginTransaction();

        // allow insert records without foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=0;');
        
        $import = $manifest->get("import");
        foreach ($import[data] as $data) {
            // truncate table
            $db->query("TRUNCATE TABLE `" . $data[table] . "`;");
    
            // insert data
            $exportPathFile = $this->getFile($data[filename]);
            $db->loadDataInfile($exportPathFile, $data[table], $import[infile_clauses][fields_clause],
                $import[infile_clauses][lines_clause], $data[columns]);
        }

        // restore foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // commit transaction
        $db->commit();
    }

    public static function order(): int
    {
        return 40;
    }
}
