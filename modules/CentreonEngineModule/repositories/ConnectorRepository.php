<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonEngine\Repository;

use Centreon\Internal\Di;
use CentreonConfiguration\Internal\Poller\WriteConfigFile;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class ConnectorRepository
{
    /**
     * 
     * @param array $filesList
     * @param int $poller_id
     * @param string $path
     * @param string $filename
     */
    public function generate(& $filesList, $poller_id, $path, $filename)
    {
        $di = Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Init Content Array */
        $content = array();
        
        /* Get information into the database. */
        $query = "SELECT name AS connector_name, command_line AS connector_line "
            . "FROM cfg_connectors WHERE enabled = 1 "
            . "ORDER BY name";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tmp = array("type" => "connector");
            $tmpData = array();
            foreach ($row as $key => $value) {
                $tmpData[$key] = $value;
            }
            $tmp["content"] = $tmpData;
            $content[] = $tmp;
        }

        /* Write Check-Command configuration file */
        WriteConfigFile::writeObjectFile($content, $path . $poller_id . "/objects.d/" . $filename, $filesList, $user = "API");
        unset($content);
    }
}
