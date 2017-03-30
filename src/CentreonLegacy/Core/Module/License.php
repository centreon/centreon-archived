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

class License extends Module
{
    /**
     *
     * @var \Pimple\Container
     */
    protected $dependencyInjector;
    
    /**
     *
     * @param \Pimple\Container $dependencyInjector
     */
    public function __construct(\Pimple\Container $dependencyInjector)
    {
        $this->dependencyInjector = $dependencyInjector;
    }

    /**
     * Parsing a Zend license file
     *
     * @return array
     */
    private function parseLicenseFile()
    {
        $info = array();

        $lines = preg_split('/\n/', file_get_contents($this->licenseFile));
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
     * @return false|string
     */
    public function getLicenseExpiration($licenseFile)
    {
        if (function_exists('zend_loader_enabled') && file_exists($licenseFile)) {
            if (zend_loader_file_encoded($licenseFile)) {
                $zend_info = zend_loader_file_licensed($licenseFile);
            } else {
                $zend_info = $this->parseLicenseFile($licenseFile);
            }
            $licenseExpiration = date("d/m/Y", strtotime($zend_info['Expires']));
        } else {
            $licenseExpiration = _("N/A");
        }

        return $licenseExpiration;
    }
}
