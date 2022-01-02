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

namespace CentreonModule\Infrastructure\Service;

use Psr\Container\ContainerInterface;
use CentreonModule\Infrastructure\Source;
use CentreonModule\Infrastructure\Entity\Module;

class CentreonModuleService
{

    /**
     * @var array<string,mixed>
     */
    protected $sources = [];

    /**
     * Construct
     *
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->initSources($services);
    }

    /**
     * @param string|null $search
     * @param boolean|null $installed
     * @param boolean|null $updated
     * @param array<mixed>|null $typeList
     * @return array<string|int,\CentreonModule\Infrastructure\Entity\Module[]>
     */
    public function getList(
        string $search = null,
        bool $installed = null,
        bool $updated = null,
        array $typeList = null
    ): array {
        $result = [];

        if ($typeList !== null && $typeList) {
            $sources = [];

            foreach ($this->sources as $type => $source) {
                if (!in_array($type, $typeList)) {
                    continue;
                }

                $sources[$type] = $source;
            }
        } else {
            $sources = $this->sources;
        }

        foreach ($sources as $type => $source) {
            $list = $source->getList($search, $installed, $updated);

            $result[$type] = $this->sortList($list);
        }

        return $result;
    }

    /**
     * @param string $id
     * @param string $type
     * @return Module|null
     */
    public function getDetail(string $id, string $type): ?Module
    {
        if (!array_key_exists($type, $this->sources)) {
            return null;
        }

        $result = $this->sources[$type]->getDetail($id);

        return $result;
    }

    /**
     * @param string $id
     * @param string $type
     * @return Module|null
     */
    public function install(string $id, string $type): ?Module
    {
        if (!array_key_exists($type, $this->sources)) {
            return null;
        }

        $result = $this->sources[$type]->install($id);

        return $result;
    }

    /**
     * @param string $id
     * @param string $type
     * @return Module|null
     */
    public function update(string $id, string $type): ?Module
    {
        if (!array_key_exists($type, $this->sources)) {
            return null;
        }

        $result = $this->sources[$type]->update($id);

        return $result;
    }

    /**
     * @param string $id
     * @param string $type
     * @return bool|null
     */
    public function remove(string $id, string $type): ?bool
    {
        if (!array_key_exists($type, $this->sources)) {
            return null;
        }

        $this->sources[$type]->remove($id);

        return true;
    }

    /**
     * Init list of sources
     *
     * @param ContainerInterface $services
     */
    protected function initSources(ContainerInterface $services): void
    {
        $this->sources = [
            Source\ModuleSource::TYPE => new Source\ModuleSource($services),
            Source\WidgetSource::TYPE => new Source\WidgetSource($services),
        ];
    }

    /**
     * Sort list by:
     *
     * - To update (then by name)
     * - To install (then by name)
     * - Installed (then by name)
     *
     * @param \CentreonModule\Infrastructure\Entity\Module[] $list
     * @return \CentreonModule\Infrastructure\Entity\Module[]
     */
    protected function sortList(array $list): array
    {
        usort($list, function ($a, $b) {
            $aVal = $a->getName();
            $bVal = $b->getName();

            if ($aVal === $bVal) {
                return 0;
            }

            return ($aVal < $bVal) ? -1 : 1;
        });
        usort($list, function ($a, $b) {
            $sortByName = function ($a, $b) {
                $aVal = $a->isInstalled();
                $bVal = $b->isInstalled();

                if ($aVal === $bVal) {
                    return 0;
                }

                return ($aVal < $bVal) ? -1 : 1;
            };

            $aVal = $a->isInstalled() && !$a->isUpdated();
            $bVal = $b->isInstalled() && !$b->isUpdated();

            if ($aVal === $bVal) {
                return $sortByName($a, $b);
            }

            return ($aVal === true) ? -1 : 1;
        });

        return $list;
    }
}
