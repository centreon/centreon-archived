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
use CentreonModule\Domain\Repository\WidgetModelsRepository;
use CentreonModule\Infrastructure\Source\SourceAbstract;
use CentreonLegacy\ServiceProvider as ServiceProviderLegacy;

class WidgetSource extends SourceAbstract
{
    public const TYPE = 'widget';
    public const PATH = 'www/widgets/';
    public const CONFIG_FILE = 'configs.xml';

    /**
     * @var string[]
     */
    private $info;

    /**
     * Construct
     *
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->installer = $services->get(ServiceProviderLegacy::CENTREON_LEGACY_WIDGET_INSTALLER);
        $this->upgrader = $services->get(ServiceProviderLegacy::CENTREON_LEGACY_WIDGET_UPGRADER);
        $this->remover = $services->get(ServiceProviderLegacy::CENTREON_LEGACY_WIDGET_REMOVER);

        parent::__construct($services);
    }

    public function initInfo(): void
    {
        $this->info = $this->db
            ->getRepository(WidgetModelsRepository::class)
            ->getAllWidgetVsVersion()
        ;
    }

    /**
     * @param string|null $search
     * @param boolean|null $installed
     * @param boolean|null $updated
     * @return array<int,\CentreonModule\Infrastructure\Entity\Module>
     */
    public function getList(string $search = null, bool $installed = null, bool $updated = null): array
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

    /**
     * @param string $id
     * @return Module|null
     */
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

    /**
     * @param string $configFile
     * @return Module
     */
    public function createEntityFromConfig(string $configFile): Module
    {
        // force linux path format
        $configFile = str_replace(DIRECTORY_SEPARATOR, '/', $configFile);

        $xml = simplexml_load_file($configFile);

        $entity = new Module;
        $entity->setId(basename(dirname($configFile)));
        $entity->setPath(dirname($configFile));
        $entity->setType(static::TYPE);
        $entity->setName($xml->title->__toString());
        $entity->setDescription($xml->description->__toString());
        $entity->setAuthor($xml->author->__toString());
        $entity->setVersion($xml->version->__toString());
        $entity->setKeywords($xml->keywords->__toString());

        if ($xml->stability) {
            $entity->setStability($xml->stability->__toString());
        }

        if ($xml->last_update) {
            $entity->setLastUpdate($xml->last_update->__toString());
        }

        if ($xml->release_note) {
            $entity->setReleaseNote($xml->release_note->__toString());
        }

        if ($xml->screenshot) {
            foreach ($xml->screenshot as $image) {
                if (!empty($image->__toString())) {
                    $entity->addImage($image->__toString());
                }
            }
            unset($image);
        }

        if ($xml->screenshots) {
            foreach ($xml->screenshots as $image) {
                if (!empty($image->screenshot['src']->__toString())) {
                    $entity->addImage($image->screenshot['src']->__toString());
                }
            }
            unset($image);
        }

        // load information about installed modules/widgets
        if ($this->info === null) {
            $this->initInfo();
        }

        if (array_key_exists($entity->getId(), $this->info)) {
            $entity->setVersionCurrent($this->info[$entity->getId()]);
            $entity->setInstalled(true);

            $isUpdated = $this->isUpdated($this->info[$entity->getId()], $entity->getVersion());
            $entity->setUpdated($isUpdated);
        }

        return $entity;
    }
}
