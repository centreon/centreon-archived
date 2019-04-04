<?php
/**
 * Copyright 2005-2017 Centreon
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
 */

namespace CentreonLegacy\Core\Module;

use Psr\Container\ContainerInterface;
use CentreonLegacy\Core\Module\Information;
use CentreonLegacy\Core\Utils\Utils;
use CentreonLegacy\ServiceProvider;

class Module
{
    /**
     *
     * @var \CentreonLegacy\Core\Module\Information
     */
    protected $informationObj;
    
    /**
     *
     * @var string
     */
    protected $moduleName;
    
    /**
     *
     * @var int
     */
    protected $moduleId;
    
    /**
     *
     * @var \CentreonLegacy\Core\Utils\Utils
     */
    protected $utils;
    
    /**
     *
     * @var array
     */
    protected $moduleConfiguration;
    
    /**
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $services;
    
    /**
     *
     * @param \Psr\Container\ContainerInterface $services
     * @param \CentreonLegacy\Core\Module\Information $informationObj
     * @param string $moduleName
     * @param \CentreonLegacy\Core\Utils\Utils $utils
     * @param int $moduleId
     */
    public function __construct(
        ContainerInterface $services,
        Information $informationObj = null,
        $moduleName,
        Utils $utils = null,
        $moduleId = null
    ) {
        $this->moduleId = $moduleId;
        $this->services = $services;
        $this->informationObj = $informationObj ?? $services->get(ServiceProvider::CENTREON_LEGACY_MODULE_INFORMATION);
        $this->moduleName = $moduleName;
        $this->utils = $utils ?? $services->get(ServiceProvider::CENTREON_LEGACY_UTILS);

        $this->moduleConfiguration = $this->informationObj->getConfiguration($this->moduleName);
    }
    
    /**
     *
     * @param string $moduleName
     * @return string
     */
    public function getModulePath($moduleName = '')
    {
        return $this->utils->buildPath('/modules/' . $moduleName);
    }
}
