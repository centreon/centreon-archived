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

use Phinx\Migration\AbstractMigration;


/**
 * Description of RenameDependenciesColumns
 *
 * @author kevin duret <kduret@centreon.com>
 */
class RenameDependenciesColumns extends AbstractMigration
{
    public function change()
    {
        $cfg_dependencies_servicechildren_relations = $this->table('cfg_dependencies_servicechildren_relations');
        $cfg_dependencies_servicechildren_relations->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => false))
            ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
            ->save();

        $cfg_dependencies_serviceparents_relations = $this->table('cfg_dependencies_serviceparents_relations');
        $cfg_dependencies_serviceparents_relations->addColumn('host_host_id', 'integer', array('signed' => false, 'null' => false))
            ->addForeignKey('host_host_id', 'cfg_hosts', 'host_id', array('delete'=> 'CASCADE', 'update'=> 'RESTRICT'))
            ->save();
    }
}
