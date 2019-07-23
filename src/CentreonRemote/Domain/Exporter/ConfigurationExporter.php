<?php
namespace CentreonRemote\Domain\Exporter;

use CentreonRemote\Infrastructure\Export\ExportManifest;
use CentreonRemote\Infrastructure\Service\ExporterServiceAbstract;
use Centreon\Domain\Repository;

require_once dirname(__FILE__) . '/../../../../bootstrap.php';
require_once 'config-generate-remote/generate.class.php';

use ConfigGenerateRemote\Generate;
use ConfigGenerateRemote\Manifest;

class ConfigurationExporter extends ExporterServiceAbstract
{

    const NAME = 'configuration';
    const MEDIA_PATH = _CENTREON_PATH_ . 'www/img/media';

    /**
     * Export data
     */
    public function export(int $remoteId): array
    {
        // create path
        $this->createPath();

        // call to ConfigGenerateRemote\Generate class
        $dependencyInjector = loadDependencyInjector();
        $config_generate = new Generate($dependencyInjector);
        $config_generate->configRemoteServerFromId($remoteId, 'user');

        return Manifest::getInstance($dependencyInjector)->getManifest();
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
            echo date("Y-m-d H:i:s") . " - INFO - Loading '" . $exportPathFile . "'.\n";
            $db->loadDataInfile($exportPathFile, $data[table], $import[infile_clauses][fields_clause],
                $import[infile_clauses][lines_clause], $data[columns]);
        }

        // restore foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // commit transaction
        $db->commit();
        
        // media copy
        $exportPathMedia = $this->commitment->getPath() . "/media";
        $mediaPath = static::MEDIA_PATH;
        $this->recursive_copy($exportPathMedia, $mediaPath);
    }

    /**
     * Copy directory recusively
     */
    private function recursive_copy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst, $this->commitment->getFilePermission(), true);
        while(( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->recursive_copy($src .'/'. $file, $dst .'/'. $file);
                }
                else {
                    echo date("Y-m-d H:i:s") . " - INFO - Copying '" . $src ."/". $file . "'.\n";
                    copy($src .'/'. $file, $dst .'/'. $file);
                    chmod($dst .'/'. $file, $this->commitment->getFilePermission());
                }
            }
        }
        closedir($dir);
    }

    public static function order(): int
    {
        return 40;
    }
}
