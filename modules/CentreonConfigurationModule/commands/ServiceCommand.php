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

/**
 * Login controller
 * @authors Julien Mathis
 * @package Centreon
 * @subpackage Controllers
 */
class ServiceCommand extends \Centreon\Internal\Command\AbstractCommand
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
        $hostParams = array(
            'host_id', 'host_name'
        );
        $serviceParams = array(
            'service_id',
            'service_description',
            'service_normal_check_interval',
            'service_retry_check_interval',
            'service_max_check_attempts',
            'service_active_checks_enabled',
            'service_passive_checks_enabled',
            'service_activate'
        );
        
        $serviceList = \CentreonConfiguration\Models\Relation\Service\Host::getMergedParameters(
            $serviceParams,
            $hostParams
        );
        
        if (count($serviceList) > 0) {
            $result = "host id;host name;id;description;command check;"
                . "normal check interval;retry check interval;max check attempts;"
                . "active checks enabled;passive checks enabled;activate\n";
            foreach ($serviceList as $service) {
                $command = \CentreonConfiguration\Models\Command::getParameters($service['command_command_id'], array('command_name'));
                $result .= "$service[host_id];$service[host_name];"
                    . "$service[service_description];$command[command_name];$service[service_normal_check_interval];"
                    . "$service[service_retry_check_interval];$service[service_max_check_attempts];"
                    . "$service[service_active_checks_enabled];$service[service_passive_checks_enabled];"
                    . "$service[service_activate]\n";
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
    public function showAction($host)
    {
        /*
         * Query parameter
         */
        $params = array(
            "service_register" => '1'
        );
        
        if (is_numeric($host)) {
            $params['service_id'] = $host;
        } else {
            $params['service_name'] = $host;
        }
        
        
        /*
         * Get host informations
         */
        $hostList = \CentreonConfiguration\Models\Service::getList('*', -1, 0, null, "ASC", $params, "AND");
        
        if (count($hostList) > 0) {
            $result = "id;name;alias;address;activate\n";
            foreach ($hostList as $host) {
                $result .= "$host[service_id];$host[service_name];"
                    . "$host[service_alias];$host[service_address];$host[service_activate]\n";
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
