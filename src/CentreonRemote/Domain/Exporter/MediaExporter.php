<?php
namespace CentreonRemote\Domain\Exporter;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use Centreon\Domain\Repository\ViewImgRepository;
use Centreon\Domain\Repository\ViewImgDirRepository;

class MediaExporter implements ExporterServiceInterface
{

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
     * Export media data
     * 
     * @todo add exceptions
     */
    public function export(): void
    {
        $dirs = $this->db
            ->getRepository(ViewImgDirRepository::class)
            ->export()
        ;

        $imgs = $this->db
            ->getRepository(ViewImgRepository::class)
            ->export()
        ;

        $exportPath = $this->commitment->getPath() . '/' . $this->getName();
        $exportPathMedia = "{$exportPath}/files";

        if (!is_dir($exportPathMedia)) {
            mkdir($exportPathMedia, $this->commitment->getFilePermission(), true);
        }

        // make copy of media files
        foreach ($imgs as $img) {
            $pathSuffix = '/' . ($img['imgDirs'] ? "{$img['imgDirs']}/" : '') .
                $img['imgPath']
            ;
            $imgPath = static::MEDIA_PATH . $pathSuffix;

            // prevent reading of non-exists files
            if (!is_file($imgPath)) {
                // @todo throw exception

                continue;
            }

            $imgPathExport = $exportPathMedia . $pathSuffix;

            $imgPathExportDir = dirname($imgPathExport);

            if (!is_dir($imgPathExportDir)) {
                mkdir($imgPathExportDir, $this->commitment->getFilePermission(), true);
            }

            copy($imgPath, $imgPathExport);
        }

        $this->commitment->getParser()::dump($dirs, "{$exportPath}/dirs.yaml");
        $this->commitment->getParser()::dump($imgs, "{$exportPath}/files.yaml");
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
