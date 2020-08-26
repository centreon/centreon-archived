<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

require_once "Centreon/Object/Object.php";

/**
 * Used for interacting with dependencies
 *
 */
class Centreon_Object_DependencyServiceParent extends Centreon_Object
{
    protected $table = "dependency_serviceParent_relation";
    protected $primaryKey = "dependency_dep_id";

    public function removeRelationLastServiceDependency(int $serviceId): void
    {
        $query = 'SELECT count(dependency_dep_id) AS nb_dependency , dependency_dep_id AS id 
              FROM dependency_serviceParent_relation 
              WHERE dependency_dep_id = (SELECT dependency_dep_id FROM dependency_serviceParent_relation 
                                         WHERE service_service_id = ?)';
        $result = $this->getResult($query, array($serviceId), "fetch");

        //is last parent
        if ($result['nb_dependency'] == 1) {
            $this->db->query("DELETE FROM dependency WHERE dep_id = " . $result['id']);
        }
    }
}
