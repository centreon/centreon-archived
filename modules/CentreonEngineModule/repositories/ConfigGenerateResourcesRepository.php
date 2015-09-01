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
 * @author Julien Mathis <jmathis@centreon.com>
 * @version 3.0.0
 */
class ConfigGenerateResourcesRepository
{
    /** 
     * Generate Resources.cfg
     * @param array $filesList
     * @param int $pollerId
     * @param string $path
     * @param string $filename
     * @return value
     */
    public function generate(& $filesList, $pollerId, $path, $filename)
    {
        $di = Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Init Content Array */
        $content = array();
        
        /* Get information into the database. */
        $query = "SELECT resource_name, resource_line 
                  FROM cfg_resources r, cfg_resources_instances_relations rr 
                  WHERE r.resource_id = rr.resource_id 
                  AND r.resource_activate = '1' 
                  AND rr.instance_id = ? 
                  ORDER BY resource_name";
        $stmt = $dbconn->prepare($query);
        $stmt->execute(array($pollerId));
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $content[$row["resource_name"]] = $row["resource_line"];
        }

        /* Write Check-Command configuration file */
        WriteConfigFile::writeParamsFile($content, $path.$pollerId . "/conf.d/" . $filename, $filesList, $user = "API");
        unset($content);
    }
}
