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

class MetaHost extends AbstractObject {
    protected $generate_filename = 'meta_host.cfg';
    protected $object_name = 'host';
    protected $attributes_write = array(
        'host_name',
        'alias',
        'address',
        'check_command',
        'max_check_attempts',
        'check_interval',
        'active_checks_enabled',
        'passive_checks_enabled',
        'check_period',
        'notification_interval',
        'notification_period',
        'notification_options',
        'notifications_enabled',
        'register',
    );
    protected $attributes_hash = array(
        'macros'
    );
    
    public function getHostIdByHostName($host_name) {
        $stmt = $this->backend_instance->db->prepare("SELECT 
              host_id
            FROM host
            WHERE host_name = :host_name
            ");
        $stmt->bindParam(':host_name', $host_name, PDO::PARAM_STR);
        $stmt->execute();
        return array_pop($stmt->fetchAll(PDO::FETCH_COLUMN));
    }
    
    public function generateObject($host_id) {
        if ($this->checkGenerate($host_id)) {
            return 0;
        }
        
        $object = array();
        $object['host_name'] = '_Module_Meta';
        $object['alias'] = 'Meta Service Calculate Module For Centreon';
        $object['address'] = '127.0.0.1';
        $object['check_command'] = 'check_meta_host_alive';
        $object['max_check_attempts'] = 3;
        $object['check_interval'] = 1;
        $object['active_checks_enabled'] = 0;
        $object['passive_checks_enabled'] = 0;
        $object['check_period'] = 'meta_timeperiod';
        $object['notification_interval'] = 60;
        $object['notification_period'] = 'meta_timeperiod';
        $object['notification_period'] = 'meta_timeperiod';
        $object['notification_options'] = 'd';
        $object['notifications_enabled'] = 0;
        $object['register'] = 1;
        $object['macros'] = array('_HOST_ID' => $host_id);
        
        $this->generateObjectInFile($object, $host_id);
    }
}
