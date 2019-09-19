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
use CentreonLegacy\ServiceProvider;

/**
 * License service provide information about module licenses
 */
class License extends Module
{

    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $services;

    /**
     * Construct
     *
     * @param \Psr\Container\ContainerInterface
     */
    public function __construct(ContainerInterface $services)
    {
        $this->services = $services;
    }

    /**
     * Parsing a license file
     *
     * @param type $licenseFile
     * @return array
     */
    private function parseLicenseFile($licenseFile)
    {
        $info = array();

        $lines = preg_split('/\n/', file_get_contents($licenseFile));
        foreach ($lines as $line) {
            if (preg_match('/^([^= ]+)\s*=\s*(.+)$/', $line, $match)) {
                $info[$match[1]] = $match[2];
            }
        }

        return $info;
    }

    /**
     * Get license expiration date
     *
     * @param string $module
     * @return string
     */
    public function getLicenseExpiration($module): ?string
    {
        $healthcheck = $this->services->get(ServiceProvider::CENTREON_LEGACY_MODULE_HEALTHCHECK);

        try {
            $healthcheck->check($module);
        } catch (\Exception $ex) {
        }

        if ($expiration = $healthcheck->getLicenseExpiration()) {
            return $expiration->format(\DateTime::ISO8601);
        }

        return null;
    }
}
