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
