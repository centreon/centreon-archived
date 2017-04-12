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

    public function getConfiguration()
    {
        $file = __DIR__ . '/../../../../../www/install/tmp/configuration.json';
        if (!$this->dependencyInjector['filesystem']->exists($file)) {
            throw new \Exception('Cannot find configuration file configuration.json');
        }

        return json_decode(file_get_contents($file), true);
    }

    public function setConfiguration()
    {
        $configurationFile = __DIR__ . "/../../../../../www/install/install.conf.php";

        if (!$this->dependencyInjector['filesystem']->exists($configurationFile)) {
            throw new \Exception('Configuration file "install.conf.php" does not exist.');
        }

        $conf_centreon =  array();
        require $configurationFile;

        $tmpDir = __DIR__ . '/../../../../../www/install/tmp';
        if (!$this->dependencyInjector['filesystem']->exists($tmpDir)) {
            $this->dependencyInjector['filesystem']->mkdir($tmpDir);
        }

        file_put_contents($tmpDir . '/configuration.json', json_encode($conf_centreon));
    }

    public function getDatabaseConfiguration()
    {
        $configuration = array(
            'address' => '',
            'port' => '',
            'root_password' => '',
            'db_configuration' => 'centreon',
            'db_storage' => 'centreon_storage',
            'db_user' => 'centreon',
            'db_password' => '',
            'db_password_confirm' => ''
        );

        $configurationFile = __DIR__ . "/../../../../../www/install/tmp/database.json";
        if ($this->dependencyInjector['filesystem']->exists($configurationFile)) {
            $configuration =  json_decode(file_get_contents($configurationFile), true);
        }

        return $configuration;
    }

    public function setDatabaseConfiguration($parameters)
    {
        $configurationFile = __DIR__ . "/../../../../../www/install/tmp/database.json";
        file_put_contents($configurationFile, json_encode($parameters));
    }

    public function getVersion()
    {
        $version = '1.0.0';
        $versionFile = __DIR__ . "/../../../../../www/install/tmp/version.json";
        if ($this->dependencyInjector['filesystem']->exists($versionFile)) {
            $version =  json_decode(file_get_contents($versionFile), true);
        }

        return $version;
    }

    public function setVersion($version)
    {
        $configurationFile = __DIR__ . "/../../../../../www/install/tmp/version.json";
        file_put_contents($configurationFile, json_encode($version));
    }
}
