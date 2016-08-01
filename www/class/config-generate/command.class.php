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

class Command extends AbstractObject {
    private $commands = null;
    private $mail_bin = null;
    protected $generate_filename = 'commands.cfg';
    protected $object_name = 'command';
    protected $attributes_select = '
        command_id,
        command_name,
        command.command_line as command_line_base,
        connector.name as connector,
        enable_shell
    ';
    protected $attributes_write = array(
        'command_name',
        'command_line',
        'connector',
    );
    
    private function getCommands() {        
        $stmt = $this->backend_instance->db->prepare("SELECT 
              $this->attributes_select
            FROM command 
                LEFT JOIN connector ON connector.id = command.connector_id AND connector.enabled = '1' AND command.command_activate = '1'
            ");
        $stmt->execute();
        $this->commands = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
    }

    private function getMailBin() {
        $stmt = $this->backend_instance->db->prepare("SELECT
              options.value
            FROM options
                WHERE options.key = 'mailer_path_bin'
            ");
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->mail_bin = $row['value'];
        } else {
            $this->mail_bin = '';
        }
    }
    
    public function generateFromCommandId($command_id) {
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

        if (is_null($this->mail_bin)) {
            $this->getMailBin();
        }

        /*
         * enable_shell is 0 we remove it
         */
        $command_line = html_entity_decode($this->commands[$command_id]['command_line_base']);
        $command_line = str_replace('#BR#', "\\n", $command_line);
        $command_line = str_replace("@MAILER@", $this->mail_bin, $command_line);
        $command_line = str_replace("\n", " \\\n", $command_line);
        $command_line = str_replace("\r", "", $command_line);

        if (!is_null($this->commands[$command_id]['enable_shell']) && $this->commands[$command_id]['enable_shell'] == 1) {
            $command_line = '/bin/sh -c ' . escapeshellarg($command_line);
        }

        $this->generateObjectInFile(array_merge($this->commands[$command_id], array('command_line' => $command_line)), $command_id);
        return $this->commands[$command_id]['command_name'];
    }
}
