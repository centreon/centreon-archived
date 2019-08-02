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

class nagiosServer extends AbstractObject
{
    protected $table = 'nagios_server';
    protected $generate_filename = 'nagios_server.infile';
    protected $attributes_write = array(
        'id',
        'name',
        'localhost',
        'is_default',
        'last_restart',
        'ns_ip_address',
        'ns_activate',
        'ns_status',
        'init_script',
        'init_system',
        'monitoring_engine',
        'nagios_bin',
        'nagiostats_bin',
        'nagios_perfdata',
        'centreonbroker_cfg_path',
        'centreonbroker_module_path',
        'centreonconnector_path',
        'ssh_port',
        'ssh_private_key',
        'init_script_centreontrapd',
        'snmp_trapd_path_conf',
        'engine_name',
        'engine_version',
        'centreonbroker_logs_path',
        'remote_id',
        'remote_server_centcore_ssh_proxy'
    );

    public function add($object, $id)
    {
        if ($this->checkGenerate($id)) {
            return null;
        }

        $this->generateObjectInFile($object, $id);
    }
}
