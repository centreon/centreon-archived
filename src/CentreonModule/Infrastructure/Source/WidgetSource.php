<?php
namespace CentreonModule\Infrastructure\Source;

use Psr\Container\ContainerInterface;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonModule\Domain\Repository\WidgetModelsRepository;
use CentreonModule\Infrastructure\Source\SourceAbstract;

class WidgetSource extends SourceAbstract
{

    const TYPE = 'widget';
    const PATH = _CENTREON_PATH_ . 'www/widgets/';
    const CONFIG_FILE = 'configs.xml';

    /**
     * @var array
     */
    private $info;

    /**
     * Construct
     * 
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        parent::__construct($services);

        $this->info = $this->db
            ->getRepository(WidgetModelsRepository::class)
            ->getAllWidgetVsVersion()
        ;
    }

    public function getList(string $search = null, bool $installed = null, bool $updated = null)
    {
        $files = $this->finder
            ->files()
            ->name(static::CONFIG_FILE)
            ->depth('== 1')
            ->sortByName()
            ->in(static::PATH);

        $result = [];

        foreach ($files as $file) {

            $entity = $this->createEntityFromConfig($file->getPathName());

            if (!$this->isEligible($entity, $search, $installed, $updated)) {
                continue;
            }

            $result[] = $entity;
        }

        return $result;
    }

    public function createEntityFromConfig(string $configFile): Module
    {
        $xml = simplexml_load_file($configFile);

        $entity = new Module;
        $entity->setId(basename(dirname($configFile)));
        $entity->setPath(dirname($configFile));
        $entity->setType(static::TYPE);
        $entity->setName($xml->title->__toString());
        $entity->setAuthor($xml->author->__toString());
        $entity->setVersion($xml->version->__toString());
        $entity->setKeywords($xml->keywords->__toString());

        if (array_key_exists($entity->getId(), $this->info)) {
            $entity->setVersionCurrent($this->info[$entity->getId()]);
            $entity->setInstalled(true);

            if ($this->info[$entity->getId()] != $entity->getVersion()) {
                $entity->setUpdated(true);
            }
        }

        return $entity;
    }
}
