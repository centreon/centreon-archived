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

class macroService extends AbstractObject
{
    private $use_cache = 1;
    private $done_cache = 0;
    private $macro_service_cache = array();
    protected $stmt_service = null;
    protected $table = 'on_demand_macro_service';
    protected $generate_filename = 'on_demand_macro_service.infile';
    protected $attributes_write = array(
        'svc_svc_id',
        'svc_macro_name',
        'svc_macro_value',
        'is_password',
        'description',
    );

    public function __construct(\Pimple\Container $dependencyInjector)
    {
        parent::__construct($dependencyInjector);
        $this->buildCache();
    }

    private function cacheMacroService()
    {
        $stmt = $this->backend_instance->db->prepare("SELECT 
              svc_macro_id, svc_svc_id, svc_macro_name, svc_macro_value, is_password, description
            FROM on_demand_macro_service
        ");
        $stmt->execute();
        while (($macro = $stmt->fetch(PDO::FETCH_ASSOC))) {
            if (!isset($this->macro_service_cache[$macro['svc_svc_id']])) {
                $this->macro_service_cache[$macro['svc_svc_id']] = array();
            }
            $this->macro_service_cache[$macro['svc_svc_id']][$macro['svc_macro_id']] = array(
                'svc_svc_id' => $macro['svc_svc_id'],
                'svc_macro_name' => $macro['svc_macro_name'],
                'svc_macro_value' => $macro['svc_macro_value'],
                'is_password' => $macro['is_password'],
                'description' => $macro['description'],
            );
        }
    }

    private function writeMacrosService($service_id) {
        if ($this->checkGenerate($service_id)) {
            return null;
        }
        
        foreach ($this->macro_service_cache[$service_id] as $svc_macro_id => $value) {
            $this->generateObjectInFile($value, $service_id);
        }
    }

    public function getServiceMacroByServiceId($service_id)
    {
        # Get from the cache
        if (isset($this->macro_service_cache[$service_id])) {
            $this->writeMacrosService($service_id);
            return $this->macro_service_cache[$service_id];
        }
        if ($this->done_cache == 1) {
            return null;
        }

        # We get unitary
        if (is_null($this->stmt_service)) {
            $this->stmt_service = $this->backend_instance->db->prepare("SELECT 
                    svc_macro_id, svc_macro_name, svc_macro_value, is_password, description
                FROM on_demand_macro_service
                WHERE svc_svc_id = :service_id
            ");
        }

        $this->stmt_service->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $this->stmt_service->execute();
        $this->macro_service_cache[$service_id] = array();
        while (($macro = $this->stmt_service->fetch(PDO::FETCH_ASSOC))) {
             $this->macro_service_cache[$service_id][$macro['svc_macro_id']] = array(
                'svc_svc_id' => $macro['svc_svc_id'],
                'svc_macro_name' => $macro['svc_macro_name'],
                'svc_macro_value' => $macro['svc_macro_value'],
                'is_password' => $macro['is_password'],
                'description' => $macro['description'],
            );
        }

        $this->writeMacrosService($service_id);
        return $this->macro_service_cache[$service_id];
    }

    private function buildCache()
    {
        if ($this->done_cache == 1) {
            return 0;
        }

        $this->cacheMacroService();
        $this->done_cache = 1;
    }
}
