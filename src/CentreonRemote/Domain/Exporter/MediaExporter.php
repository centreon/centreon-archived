<?php
namespace CentreonRemote\Domain\Exporter;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Domain\Exporter\Traits\ExportPathTrait;
use Centreon\Domain\Entity;
use Centreon\Domain\Repository;

class MediaExporter implements ExporterServiceInterface
{

    use ExportPathTrait;

    const EXPORT_FILES = 'files';
    const EXPORT_FILE_DIR = 'dirs.yaml';
    const EXPORT_FILE_IMG = 'files.yaml';
    const MEDIA_PATH = _CENTREON_PATH_ . 'www/img/media';

    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    private $db;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportCommitment
     */
    private $commitment;

    /**
     * Construct
     * 
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->db = $services->get('centreon.db-manager');
    }

    /**
     * Cleanup database
     */
    public function cleanup(): void
    {
        
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

        $this->commitment->getParser()::dump($dirs, $exportPath . '/' . static::EXPORT_FILE_DIR);
        $this->commitment->getParser()::dump($imgs, $exportPath . '/' . static::EXPORT_FILE_IMG);
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
        $db->getRepository(Repository\ViewImgDirRepository::class)->truncate();
        $db->getRepository(Repository\ViewImgRepository::class)->truncate();

        // insert directories
        $exportPathDir = $exportPath . '/' . static::EXPORT_FILE_DIR;
        $dirs = $this->commitment->getParser()::parse($exportPathDir);
        $dirMap = [];

        foreach ($dirs as $data) {
            $dirMap[$dir['dir_alias']] = $db->insert(Entity\ViewImgDir::TABLE, $data);
        }

        // cleanup memory
        unset($dirs);

        // insert images
        $exportPathMedia = $exportPath . '/' . static::EXPORT_FILES;
        $exportPathImg = $exportPath . '/' . static::EXPORT_FILE_IMG;
        $imgs = $this->commitment->getParser()::parse($exportPathImg);

        foreach ($imgs as $data) {
            unset($data['img_dirs']);

            $db->insert(Entity\ViewImg::TABLE, $data);

            // add relation img to dir
            $imgDirs = explode(',', $img['img_dirs']);
            foreach ($imgDirs as $imgDir) {
                // skip if dir is undefined
                if (!array_key_exists($imgDir, $dirMap)) {
                    continue;
                }

                $data = [
                    'dir_dir_parent_id' => $dirMap[$imgDir],
                    'img_img_id' => $img['img_id'],
                ];

                $db->insert('view_img_dir_relation', $data);
            }

            // copy img from export to media dir
            $pathSuffix = '/' . ($img['img_dirs'] ? "{$img['img_dirs']}/" : '') .
                $img['img_path']
            ;
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

    public function setCommitment(ExportCommitment $commitment): void
    {
        $this->commitment = $commitment;
    }

    public static function getName(): string
    {
        return 'media';
    }
}
