<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonConfiguration\Commands;

use Centreon\Internal\Command\AbstractCommand;
use CentreonConfiguration\Models\Host;

/**
 * Login controller
 * @authors Julien Mathis
 * @package Centreon
 * @subpackage Controllers
 */
class HostCommand extends AbstractCommand
{
    /**
     * Action for listing hosts
     *
     */
    public function listAction()
    {
        /*
         * Fields that we want to display
         */
        $params = 'host_id,host_name,host_alias,host_address,host_activate';
        
        $hostList = Host::getList(
            $params,
            -1,
            0,
            null,
            "ASC"
        );
        
        if (count($hostList) > 0) {
            $result = "id;name;alias;address;activate\n";
            foreach ($hostList as $host) {
                $result .= "$host[host_id];$host[host_name];"
                    . "$host[host_alias];$host[host_address];$host[host_activate]\n";
            }
        } else {
            $result = "No result found";
        }
        
        echo $result;
    }

    /**
     * Action to get info a specific host
     * @param    int   $id    ID of object
     * @param    mixed   $host    Name of object
     */
    public function showAction($host, $linkedObject = '')
    {
        if (is_numeric($host)) {
            $params['host_id'] = $host;
        } else {
            $params['host_name'] = $host;
        }
        
        /*
         * Get host informations
         */
        $hostList = Host::getList('*', -1, 0, null, "ASC", $params, "AND");
        
        if (count($hostList) > 0) {
            $result = "id;name;alias;address;activate\n";
            foreach ($hostList as $host) {
                $result .= "$host[host_id];$host[host_name];"
                    . "$host[host_alias];$host[host_address];$host[host_activate]\n";
            }
        } else {
            $result = "No result found";
        }
        
        echo $result;
    }

    /**
     * Action for update 
     *
     */
    public function updateAction()
    {
        echo "Not implemented yet";
    }

    /**
     * Action for add
     *
     */
    public function addAction()
    {
        echo "Not implemented yet";
    }

    /**
     * Action for delete
     *
     */
    public function deleteAction()
    {
        echo "Not implemented yet";
    }

    /**
     * Action for duplicate
     *
     */
    public function duplicateAction()
    {
        echo "Not implemented yet";
    }
}
