<?php
/*
 * Copyright 2005-2015 Centreon
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

namespace CentreonLegacy\Core\Install\Step;

abstract class AbstractStep implements StepInterface
{
    protected $dependencyInjector;

    public function __construct($dependencyInjector)
    {
        $this->dependencyInjector = $dependencyInjector;
    }

    /**
     * @param $file
     * @param array $configuration
     * @return array|string
     */
    private function getConfiguration($file, $configuration = array())
    {
        if ($this->dependencyInjector['filesystem']->exists($file)) {
            $configuration = json_decode(file_get_contents($file), true);
            foreach ($configuration as $key => $configurationValue) {
                $configuration[$key] = filter_var($configurationValue, FILTER_SANITIZE_STRING, ENT_QUOTES);
            }
        }

        return $configuration;
    }

    public function getBaseConfiguration()
    {
        return $this->getConfiguration(__DIR__ . '/../../../../../www/install/tmp/configuration.json');
    }

    public function getDatabaseConfiguration()
    {
        $configuration = array(
            'address' => '',
            'port' => '',
            'root_user' => 'root',
            'root_password' => '',
            'db_configuration' => 'centreon',
            'db_storage' => 'centreon_storage',
            'db_user' => 'centreon',
            'db_password' => '',
            'db_password_confirm' => ''
        );

        return $this->getConfiguration(__DIR__ . "/../../../../../www/install/tmp/database.json", $configuration);
    }

    public function getAdminConfiguration()
    {
        $configuration = array(
            'admin_password' => '',
            'confirm_password' => '',
            'firstname' => '',
            'lastname' => '',
            'email' => ''
        );

        return $this->getConfiguration(__DIR__ . "/../../../../../www/install/tmp/admin.json", $configuration);
    }

    public function getEngineConfiguration()
    {
        return $this->getConfiguration(__DIR__ . "/../../../../../www/install/tmp/engine.json");
    }

    public function getBrokerConfiguration()
    {
        return $this->getConfiguration(__DIR__ . "/../../../../../www/install/tmp/broker.json");
    }

    public function getVersion()
    {
        return $this->getConfiguration(__DIR__ . "/../../../../../www/install/tmp/version.json", '1.0.0');
    }
}
