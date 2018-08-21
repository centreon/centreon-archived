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
 * this program; if not, see <htcontact://www.gnu.org/licenses>.
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

require_once dirname(__FILE__) . '/centreon_configuration_objects.class.php';

class CentreonConfigurationRemote extends CentreonConfigurationObjects
{

    public function postGetWaitList()
    {
        $data = [
            ['ip' => '10.0.0.150', 'version' => '2.8.3'],
            ['ip' => '10.0.0.151', 'version' => '2.8.4'],
            ['ip' => '10.0.0.152', 'version' => '2.8.5'],
            ['ip' => '10.0.0.153', 'version' => '2.8.6'],
        ];

        return json_encode($data);
    }

    public function postLinkCentreonRemoteServer()
    {
        if (!isset($_POST['server_ip']) || !$_POST['server_ip']) {
            throw new \RestBadRequestException('You need to send \'server_ip\' in the request.');
        }

        if (!isset($_POST['centreon_central_ip']) || !$_POST['centreon_central_ip']) {
            throw new \RestBadRequestException('You need t send \'centreon_central_ip\' in the request.');
        }

        if (!isset($_POST['server_name']) || !$_POST['server_name']) {
            throw new \RestBadRequestException('You need t send \'server_name\' in the request.');
        }

        $shouldFail = rand(1, 4) == 1;

        if ($shouldFail) {
            return json_encode(['error' => true, 'message' => 'There was some error (random 1/4).']);
        }

        return json_encode(['success' => true]);
    }
}
