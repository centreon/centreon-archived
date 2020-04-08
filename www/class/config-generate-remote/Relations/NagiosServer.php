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

namespace ConfigGenerateRemote\Relations;

use ConfigGenerateRemote\Abstracts\AbstractObject;

class NagiosServer extends AbstractObject
{
    protected $table = 'nagios_server';
    protected $generateFilename = 'nagios_server.infile';
    protected $attributesWrite = [
        'id',
        'name',
        'localhost',
        'is_default',
        'last_restart',
        'ns_ip_address',
        'ns_activate',
        'ns_status',
        'engine_start_command',
        'engine_stop_command',
        'engine_restart_command',
        'engine_reload_command',
        'nagios_bin',
        'nagiostats_bin',
        'nagios_perfdata',
        'broker_reload_command',
        'centreonbroker_cfg_path',
        'centreonbroker_module_path',
        'centreonconnector_path',
        'ssh_port',
        'gorgone_communication_type',
        'gorgone_port',
        'init_script_centreontrapd',
        'snmp_trapd_path_conf',
        'engine_name',
        'engine_version',
        'centreonbroker_logs_path',
        'remote_id',
        'remote_server_use_as_proxy'
    ];

    /**
     * Add relation
     *
     * @param array $object
     * @param integer $id
     * @return void
     */
    public function add(array $object, int $id)
    {
        if ($this->checkGenerate($id)) {
            return null;
        }

        $this->generateObjectInFile($object, $id);
    }
}
