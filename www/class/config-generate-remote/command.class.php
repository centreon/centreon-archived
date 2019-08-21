<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace ConfigGenerateRemote;

use \PDO;

class Command extends AbstractObject
{
    private $commands = null;
    protected $table = 'command';
    protected $generate_filename = 'commands.infile';
    protected $attributes_select = '
        command_id,
        command_name,
        command_line,
        command_type,
        enable_shell,
        graph_id
    ';
    protected $attributes_write = [
        'command_id',
        'command_name',
        'command_line',
        'command_type',
        'enable_shell',
        'graph_id'
    ];

    private function getCommands()
    {
        $query = "SELECT $this->attributes_select FROM command";
        $stmt = $this->backend_instance->db->prepare($query);
        $stmt->execute();
        $this->commands = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    public function generateFromCommandId($command_id)
    {
        $name = null;
        if (is_null($this->commands)) {
            $this->getCommands();
        }

        if (!isset($this->commands[$command_id])) {
            return null;
        }
        if ($this->checkGenerate($command_id)) {
            return $this->commands[$command_id]['command_name'];
        }

        graph::getInstance($this->dependencyInjector)->getGraphFromId($this->commands[$command_id]['graph_id']);
        $this->commands[$command_id]['command_id'] = $command_id;
        $this->generateObjectInFile(
            $this->commands[$command_id],
            $command_id
        );
        return $this->commands[$command_id]['command_name'];
    }
}
