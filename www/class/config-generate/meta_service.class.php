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

class MetaService extends AbstractObject {
    private $has_meta_services = 0;
    private $meta_services = array();
    private $generated_services = array(); # for index_data build
    protected $generate_filename = 'meta_services.cfg';
    protected $object_name = 'service';
    protected $attributes_select = '
        meta_id,
        meta_name as display_name,
        check_period as check_period_id,
        max_check_attempts,
        normal_check_interval,
        retry_check_interval,
        notification_interval,
        notification_period as notification_period_id,
        notification_options,
        notifications_enabled
    ';
    protected $attributes_write = array(
        'service_description',
        'display_name',
        'host_name',
        'check_command',
        'max_check_attempts',
        'normal_check_interval',
        'retry_check_interval',
        'active_checks_enabled',
        'passive_checks_enabled',
        'check_period',
        'notification_interval',
        'notification_period',
        'notification_options',
        'register',
    );
    protected $attributes_default = array(
        'notifications_enabled',
    );
    protected $attributes_hash = array(
        'macros'
    );
    protected $attributes_array = array(
        'contact_groups','contacts'
    );
    private $stmt_cg = null;
    private $stmt_contact = null;

    private function getCtFromMetaId($meta_id) {
        if (is_null($this->stmt_contact)) {
            $this->stmt_contact = $this->backend_instance->db->prepare("SELECT 
                    contact_id
                FROM meta_contact
                WHERE meta_id = :meta_id
                ");
        }
        $this->stmt_contact->bindParam(':meta_id', $meta_id);
        $this->stmt_contact->execute();
        $this->meta_services[$meta_id]['contacts'] = array();
        foreach ($this->stmt_contact->fetchAll(PDO::FETCH_COLUMN) as $ct_id) {
            $this->meta_services[$meta_id]['contacts'][] = Contact::getInstance()->generateFromContactId($ct_id);
        }
    }

    private function getCgFromMetaId($meta_id) {
        if (is_null($this->stmt_cg)) {
            $this->stmt_cg = $this->backend_instance->db->prepare("SELECT 
                    cg_cg_id
                FROM meta_contactgroup_relation
                WHERE meta_id = :meta_id
                ");
        }
        $this->stmt_cg->bindParam(':meta_id', $meta_id);
        $this->stmt_cg->execute();
        $this->meta_services[$meta_id]['contact_groups'] = array();
        foreach ($this->stmt_cg->fetchAll(PDO::FETCH_COLUMN) as $cg_id) {
            $this->meta_services[$meta_id]['contact_groups'][] = Contactgroup::getInstance()->generateFromCgId($cg_id);
        }
    }

    private function getServiceIdFromMetaId($meta_id, $meta_name) {
        $composed_name = 'meta_' . $meta_id;
        $stmt = $this->backend_instance->db->prepare("SELECT
                service_id
            FROM service
            WHERE service_register = '2'
            AND service_description = :meta_composed_name
            AND display_name = :meta_name"
        );
        $stmt->bindParam(':meta_composed_name', $composed_name);
        $stmt->bindParam(':meta_name', $meta_name);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $service_id = $row['service_id'];
        }

        if (!isset($service_id)) {
            throw new Exception('Service id of Meta Module could not be found');
        }

        return $service_id;
        
    }
    
    private function buildCacheMetaServices() {
        $stmt = $this->backend_instance->db->prepare("SELECT $this->attributes_select FROM meta_service WHERE meta_activate = '1'");
        $stmt->execute();
        $this->meta_services = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
        foreach ($this->meta_services as $meta_id => $meta_infos) {
            $this->meta_services[$meta_id]['service_id'] = $this->getServiceIdFromMetaId($meta_id, $meta_infos['display_name']);
        }
    }
    
    public function generateObjects() {
        $this->buildCacheMetaServices();
        if (count($this->meta_services) == 0) {
            return 0;
        }
        
        $host_id = MetaHost::getInstance()->getHostIdByHostName('_Module_Meta');
        if (is_null($host_id)) {
            return 0;
        }
        MetaCommand::getInstance()->generateObjects();
        MetaTimeperiod::getInstance()->generateObjects();
        MetaHost::getInstance()->generateObject($host_id);
        
        $this->has_meta_services = 1;
        
        foreach ($this->meta_services as $meta_id => &$meta_service) {
            $meta_service['macros'] = array('_SERVICE_ID' => $meta_service['service_id']);
            $this->getCtFromMetaId($meta_id);
            $this->getCgFromMetaId($meta_id);            
            $meta_service['check_period'] = Timeperiod::getInstance()->generateFromTimeperiodId($meta_service['check_period_id']);
            $meta_service['notification_period'] = Timeperiod::getInstance()->generateFromTimeperiodId($meta_service['notification_period_id']);
            $meta_service['register'] = 1;
            $meta_service['active_checks_enabled'] = 1;
            $meta_service['passive_checks_enabled'] = 0;
            $meta_service['host_name'] = '_Module_Meta';
            $meta_service['service_description'] = 'meta_' . $meta_id;
            $meta_service['display_name'] = $meta_service['display_name'];
            $meta_service['check_command'] = 'check_meta!' . $meta_id;
            
            $this->generated_services[] = $meta_id;
            $this->generateObjectInFile($meta_service, $meta_id);
        }
    }

    public function getMetaServices() {
        return $this->meta_services;
    }

    public function hasMetaServices() {
        return $this->has_meta_services;
    }
    
    public function getGeneratedServices() {
        return $this->generated_services;
    }
}
