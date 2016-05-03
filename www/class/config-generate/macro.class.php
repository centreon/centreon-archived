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

class Macro extends AbstractObject {
    private $use_cache = 1;
    private $done_cache = 0;
    
    private $macro_service_cache = array();
    
    protected $generate_filename = null;
    protected $object_name = null;
    protected $stmt_service = null;
    
    public function __construct() {
        parent::__construct();
        $this->buildCache();
    }
    
    private function cacheMacroService() {
        $stmt = $this->backend_instance->db->prepare("SELECT 
              svc_svc_id, svc_macro_name, svc_macro_value
            FROM on_demand_macro_service
        ");
        $stmt->execute();
        while (($macro = $stmt->fetch(PDO::FETCH_ASSOC))) {
            if (!isset($this->macro_service_cache[$macro['svc_svc_id']])) {
                $this->macro_service_cache[$macro['svc_svc_id']] = array();
            }
            $this->macro_service_cache[$macro['svc_svc_id']][preg_replace('/\$_SERVICE(.*)\$/', '_$1', $macro['svc_macro_name'])] = $macro['svc_macro_value'];
        }
    }
    
    public function getServiceMacroByServiceId($service_id) {
        # Get from the cache
        if (isset($this->macro_service_cache[$service_id])) {
            return $this->macro_service_cache[$service_id];
        }
        if ($this->done_cache == 1) {
            return null;
        }
        
        # We get unitary
        if (is_null($this->stmt_service)) {
            $this->stmt_service = $this->backend_instance->db->prepare("SELECT 
                    svc_macro_name, svc_macro_value
                FROM on_demand_macro_service
                WHERE svc_svc_id = :service_id
            ");
        }
        
        $this->stmt_service->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $this->stmt_host->execute();
        $this->macro_service_cache[$service_id] = array();
        while (($macro = $stmt->fetch(PDO::FETCH_ASSOC))) {
            $this->macro_service_cache[$service_id][preg_replace('/\$_SERVICE(.*)\$/', '_$1', $macro['svc_macro_name'])] = $macro['svc_macro_value'];
        }
        
        return $this->macro_service_cache[$service_id];
    }
    
    private function buildCache() {
        if ($this->done_cache == 1) {
            return 0;
        }
        
        $this->cacheMacroService();
        $this->done_cache = 1;
    }
}
