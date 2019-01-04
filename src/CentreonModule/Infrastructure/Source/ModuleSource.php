<?php
namespace CentreonModule\Infrastructure\Source;

use Psr\Container\ContainerInterface;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonModule\Domain\Repository\ModulesInformationsRepository;
use CentreonModule\Infrastructure\Source\SourceAbstract;

class ModuleSource extends SourceAbstract
{

    const TYPE = 'module';
    const PATH = _CENTREON_PATH_ . 'www/modules/';
    const CONFIG_FILE = 'conf.php';
    const LICENSE_FILE = 'license/merethis_lic.zl';

    /**
     * @var array
     */
    protected $info;

    /**
     * @var \CentreonLegacy\Core\Module\License
     */
    protected $license;

    /**
     * Construct
     * 
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->license = $services->get('centreon.legacy.license');

        parent::__construct($services);

        $this->info = $this->db
            ->getRepository(ModulesInformationsRepository::class)
            ->getAllModuleVsVersion()
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
        $module_conf = [];
        require $configFile;

        $info = current($module_conf);
        $licenseFile = dirname($configFile) . '/' . static::LICENSE_FILE;

        $entity = new Module;
        $entity->setId(basename(dirname($configFile)));
        $entity->setPath(dirname($configFile));
        $entity->setType(static::TYPE);
        $entity->setName($info['rname']);
        $entity->setAuthor($info['author']);
        $entity->setVersion($info['mod_release']);
        $entity->setKeywords($entity->getId());
        $entity->setLicense($this->license->getLicenseExpiration($licenseFile));

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
