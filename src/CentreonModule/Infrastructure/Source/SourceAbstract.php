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
use CentreonLegacy\Core\Configuration\Configuration;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonModule\Infrastructure\Source\SourceInterface;
use CentreonLegacy\ServiceProvider as ServiceProviderLegacy;

abstract class SourceAbstract implements SourceInterface
{

    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    protected $db;

    /**
     * @var \Symfony\Component\Finder\Finder
     */
    protected $finder;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var \CentreonLegacy\Core\Widget\Upgrader
     */
    protected $upgrader;

    /**
     * @var \CentreonLegacy\Core\Module\License
     */
    protected $license;

    /**
     * @var \CentreonLegacy\Core\Widget\Remover
     */
    protected $remover;

    /**
     * @var \CentreonLegacy\Core\Widget\Installer
     */
    protected $installer;

    /**
     * Construct
     *
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->db = $services->get(\Centreon\ServiceProvider::CENTREON_DB_MANAGER);
        $this->finder = $services->get('finder');
        $this->path = $services->get('configuration')
            ->get(Configuration::CENTREON_PATH)
        ;
    }

    public function install(string $id): ?Module
    {
        ($this->installer)($id)->install();

        $this->initInfo();

        return $this->getDetail($id);
    }

    public function update(string $id): ?Module
    {
        ($this->upgrader)($id)->update();

        $this->initInfo();

        return $this->getDetail($id);
    }

    public function remove(string $id): void
    {
        ($this->remover)($id)->remove();
    }

    public function isEligible(
        Module $entity,
        string $search = null,
        bool $installed = null,
        bool $updated = null
    ): bool {
        if ($search !== null && stripos($entity->getKeywords() . $entity->getName(), $search) === false) {
            return false;
        } elseif ($installed !== null && $entity->isInstalled() !== $installed) {
            return false;
        } elseif ($updated !== null && ($entity->isInstalled() !== true || $entity->isUpdated() !== $updated)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $installedVersion
     * @param string $codeVersion
     * @return boolean
     */
    public function isUpdated($installedVersion, $codeVersion): bool
    {
        $result = false;
        $installedVersion = trim(strtolower($installedVersion));
        $codeVersion = trim(strtolower($codeVersion));

        if ($installedVersion == $codeVersion) {
            $result = true;
        }

        return $result;
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    protected function getPath(): string
    {
        return $this->path . self::PATH;
    }
}
