<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace CentreonRemote\Domain\Resources\RemoteConfig;

/**
 * Get broker configuration template
 */
class NagiosServer
{
    // ZMQ enum value
    public const ZMQ = '1';

    /**
     * Get template configuration
     * @todo move it as yml
     *
     * @param string $name the poller name
     * @param string $ip the poller ip address
     * @return array<string,int|string> the configuration template
     */
    public static function getConfiguration(string $name, string $ip): array
    {
        return [
            'name'                       => $name,
            'localhost'                  => '0',
            'is_default'                 => '0',
            'ns_ip_address'              => $ip,
            'ns_activate'                => '1',
            'ns_status'                  => '0',
            'engine_start_command'       => 'service centengine start',
            'engine_stop_command'        => 'service centengine stop',
            'engine_restart_command'     => 'service centengine restart',
            'engine_reload_command'      => 'service centengine reload',
            'nagios_bin'                 => '/usr/sbin/centengine',
            'nagiostats_bin'             => '/usr/sbin/centenginestats',
            'nagios_perfdata'            => '/var/log/centreon-engine/service-perfdata',
            'centreonbroker_cfg_path'    => '/etc/centreon-broker',
            'centreonbroker_module_path' => '/usr/share/centreon/lib/centreon-broker',
            'centreonconnector_path'     => '/usr/lib64/centreon-connector',
            'ssh_port'                   => 22,
            'gorgone_communication_type' => self::ZMQ,
            'gorgone_port'               => 5556,
            'init_script_centreontrapd'  => 'centreontrapd',
            'snmp_trapd_path_conf'       => '/etc/snmp/centreon_traps/',
            'centreonbroker_logs_path'   => '/var/log/centreon-broker/',
        ];
    }
}
