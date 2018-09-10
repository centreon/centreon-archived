<?php
namespace CentreonRemote\Domain\Exporter;

use CentreonRemote\Infrastructure\Service\ExporterServiceAbstract;
use Centreon\Domain\Entity;
use Centreon\Domain\Repository;

class MediaExporter extends ExporterServiceAbstract
{

    const NAME = 'media';
    const EXPORT_FILES = 'files';
    const EXPORT_FILE_DIR = 'dirs.yaml';
    const EXPORT_FILE_IMG = 'files.yaml';
    const MEDIA_PATH = _CENTREON_PATH_ . 'www/img/media';

    /**
     * Cleanup database
     */
    public function cleanup(): void
    {
        $db = $this->db->getAdapter('configuration_db');

        $db->getRepository(Repository\ViewImgRepository::class)->truncate();
    }

    /**
     * Export data
     */
    public function export(): void
    {
        // create path
        $this->createPath();
        $pollerIds = $this->commitment->getPollers();

        $hostTemplateChain = $this->db
            ->getRepository(Repository\HostTemplateRelationRepository::class)
            ->getChainByPoller($pollerIds)
        ;

        $serviceTemplateChain = $this->db
            ->getRepository(Repository\ServiceRepository::class)
            ->getChainByPoller($pollerIds)
        ;

        $imgList = $this->db
            ->getRepository(Repository\ViewImgRepository::class)
            ->getChainByPoller($pollerIds, $hostTemplateChain, $serviceTemplateChain)
        ;

        $imgs = $this->db
            ->getRepository(Repository\ViewImgRepository::class)
            ->export($imgList)
        ;

        $exportPath = $this->commitment->getPath() . '/' . $this->getName();
        $exportPathMedia = $exportPath . '/' . static::EXPORT_FILES;

        if (!is_dir($exportPathMedia)) {
            mkdir($exportPathMedia, $this->commitment->getFilePermission(), true);
        }

        // make copy of media files
        foreach ($imgs as $img) {
            $pathSuffix = '/' . ($img['img_dirs'] ? "{$img['img_dirs']}/" : '') .
                $img['img_path']
            ;
            $imgPath = static::MEDIA_PATH . $pathSuffix;

            // prevent reading of non-exists files
            if (!is_file($imgPath)) {
                continue;
            }

            $imgPathExport = $exportPathMedia . $pathSuffix;

            $imgPathExportDir = dirname($imgPathExport);

            if (!is_dir($imgPathExportDir)) {
                mkdir($imgPathExportDir, $this->commitment->getFilePermission(), true);
            }

            copy($imgPath, $imgPathExport);
        }

        $dirs = $this->db
            ->getRepository(Repository\ViewImgDirRepository::class)
            ->export($imgList)
        ;

        $this->_dump($dirs, $exportPath . '/' . static::EXPORT_FILE_DIR);
        $this->_dump($imgs, $exportPath . '/' . static::EXPORT_FILE_IMG);
    }

    /**
     * Import data
     */
    public function import(): void
    {
        $exportPath = $this->getPath();

        // skip if no data
        if (!is_dir($exportPath)) {
            return;
        }

        $db = $this->db->getAdapter('configuration_db');

        // start transaction
        $db->beginTransaction();

        // allow insert records without foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=0;');

        // truncate tables
        $this->cleanup();

        // insert directories
        $exportPathDir = $exportPath . '/' . static::EXPORT_FILE_DIR;
        $dirs = $this->_parse($exportPathDir);
        $dirMap = [];

        foreach ($dirs as $data) {
            $dirMap[$data['dir_alias']] = $db->insert(Entity\ViewImgDir::TABLE, $data);
        }

        // cleanup memory
        unset($dirs);

        // insert images
        $exportPathMedia = $exportPath . '/' . static::EXPORT_FILES;
        $exportPathImg = $exportPath . '/' . static::EXPORT_FILE_IMG;
        $imgs = $this->_parse($exportPathImg);

        foreach ($imgs as $data) {
            $imgDirs = explode(',', $data['img_dirs']);
            $pathSuffix = '/' . ($data['img_dirs'] ? "{$data['img_dirs']}/" : '') .
                $data['img_path']
            ;
            unset($data['img_dirs']);

            $db->insert(Entity\ViewImg::TABLE, $data);

            // add relation img to dir
            foreach ($imgDirs as $imgDir) {
                // skip if dir is undefined
                if (!array_key_exists($imgDir, $dirMap)) {
                    continue;
                }

                $data = [
                    'dir_dir_parent_id' => $dirMap[$imgDir],
                    'img_img_id' => $data['img_id'],
                ];

                $db->insert('view_img_dir_relation', $data);
            }

            // copy img from export to media dir
            $imgPath = static::MEDIA_PATH . $pathSuffix;
            $imgPathDir = dirname($imgPath);
            $imgPathExport = $exportPathMedia . $pathSuffix;

            if (!is_dir($imgPathDir)) {
                mkdir($imgPathDir, $this->commitment->getFilePermission(), true);
            }

            if (is_file($imgPath)) {
                unlink($imgPath);
            }

            copy($imgPathExport, $imgPath);
        }

        // cleanup memory
        unset($dirMap, $imgs);

        // restore foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // commit transaction
        $db->commit();
    }
}
