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

class MetaCommand extends AbstractObject {
    protected $generate_filename = 'meta_commands.cfg';
    protected $object_name = 'command';
    protected $attributes_write = array(
        'command_name',
        'command_line',
    );
    
    public function generateObjects() {
        if ($this->checkGenerate(0)) {
            return 0;
        }
        
        $object = array();
        $object['command_name'] = 'check_meta';
        $object['command_line'] = '$CENTREONPLUGINS$/centreon_centreon_central.pl --plugin=apps::centreon::local::plugin --mode=metaservice --centreon-config=/etc/centreon/conf.pm --meta-id $ARG1$';
        $this->generateObjectInFile($object, 0);
        
        $object['command_name'] = 'check_meta_host_alive';
        $object['command_line'] = '$USER1$/check_ping -H $HOSTADDRESS$ -w 3000.0,80% -c 5000.0,100% -p 1';        
        $this->generateObjectInFile($object, 0);
    }
}
