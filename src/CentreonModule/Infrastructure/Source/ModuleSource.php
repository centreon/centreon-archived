<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

namespace CentreonModule\Infrastructure\Source;

use Psr\Container\ContainerInterface;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonModule\Domain\Repository\ModulesInformationsRepository;
use CentreonModule\Infrastructure\Source\SourceAbstract;

class ModuleSource extends SourceAbstract
{

    const TYPE = 'module';
    const PATH = 'www/modules/';
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

    public function getList(string $search = null, bool $installed = null, bool $updated = null) : array
    {
        $files = $this->finder
            ->files()
            ->name(static::CONFIG_FILE)
            ->depth('== 1')
            ->sortByName()
            ->in($this->getPath());

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

    public function getDetail(string $id): ?Module
    {
        $result = null;
        $path = $this->getPath() . $id;

        if (file_exists($path) === false) {
            return $result;
        }

        $files = $this->finder
            ->files()
            ->name(static::CONFIG_FILE)
            ->depth('== 0')
            ->sortByName()
            ->in($path)
        ;

        foreach ($files as $file) {
            $result = $this->createEntityFromConfig($file->getPathName());
        }

        return $result;
    }

    public function createEntityFromConfig(string $configFile): Module
    {
        $module_conf = [];

        $module_conf = $this->getModuleConf($configFile);

        $info = current($module_conf);
        $licenseFile = $this->getLicenseFile($configFile);

        $entity = new Module;
        $entity->setId(basename(dirname($configFile)));
        $entity->setPath(dirname($configFile));
        $entity->setType(static::TYPE);
        $entity->setName($info['rname']);
        $entity->setAuthor($info['author']);
        $entity->setVersion($info['mod_release']);
        $entity->setDescription($info['infos']);
        $entity->setKeywords($entity->getId());
        $entity->setLicense($this->license->getLicenseExpiration($licenseFile));

        if (array_key_exists($entity->getId(), $this->info)) {
            $entity->setVersionCurrent($this->info[$entity->getId()]);
            $entity->setInstalled(true);

            $isUpdated = $this->isUpdated($this->info[$entity->getId()], $entity->getVersion());
            $entity->setUpdated($isUpdated);
        }

        return $entity;
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    protected function getModuleConf(string $configFile): array
    {
        $module_conf = [];

        require $configFile;

        return $module_conf;
    }

    protected function getLicenseFile(string $configFile): string
    {
        $result = dirname($configFile) . '/' . static::LICENSE_FILE;

        return $result;
    }
}
