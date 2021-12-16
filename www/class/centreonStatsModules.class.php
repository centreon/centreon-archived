<?php

/*
 * Copyright 2005-2020 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/centreonDB.class.php';

use Psr\Log\LoggerInterface;

class CentreonStatsModules
{
    /**
     * @var \CentreonDB
     */
    private $db;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * Constructor
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->db = new centreonDB();
        $this->logger = $logger;
    }

    /**
     * Get list of installed modules
     *
     * @return array Return the names of installed modules [['name' => string], ...]
     */
    private function getInstalledModules()
    {
        $installedModules = array();
        $stmt = $this->db->prepare("SELECT name FROM modules_informations");
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $value) {
            $installedModules[] = $value['name'];
        }

        return $installedModules;
    }

    /**
     * Get statistics module objects
     *
     * @param array $installedModules Names of installed modules for which you want
     * to retrieve statistics module [['name' => string], ...]
     *
     * @return array Return a list of statistics module found
     * @see    CentreonStatsModules::getInstalledModules()
     */
    private function getModuleObjects(array $installedModules)
    {
        $moduleObjects = array();

        foreach ($installedModules as $module) {
            if ($files = glob(_CENTREON_PATH_ . 'www/modules/' . $module . '/statistics/*.class.php')) {
                foreach ($files as $fullFile) {
                    try {
                        include_once $fullFile;
                        $fileName = str_replace('.class.php', '', basename($fullFile));
                        if (class_exists(ucfirst($fileName))) {
                            $moduleObjects[] = ucfirst($fileName);
                        }
                    } catch (\Throwable $e) {
                        $this->logger->error('Cannot get stats of module ' . $module);
                    }
                }
            }
        }

        return $moduleObjects;
    }

    /**
     * Get statistics from module
     *
     * @return array The statistics of each module
     */
    public function getModulesStatistics()
    {
        $data = [];
        $moduleObjects = $this->getModuleObjects(
            $this->getInstalledModules()
        );
        if (is_array($moduleObjects)) {
            foreach ($moduleObjects as $moduleObject) {
                try {
                    $oModuleObject = new $moduleObject();
                    $data[] = $oModuleObject->getStats();
                } catch (\Throwable $e) {
                    $this->logger->error($e->getMessage, ['context' => $e]);
                }
            }
        }
        return $data;
    }
}
