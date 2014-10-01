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

namespace CentreonConfiguration\Repository;

use \Centreon\Internal\Module\Informations;
use \Centreon\Internal\Di;
use \CentreonConfiguration\Events\EngineFormSave;
use \CentreonConfiguration\Models\Poller;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class PollerRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $objectName = 'Poller';

    /**
     *
     * Check if a service or an host has been
     * changed for a specific poller.
     * @param unknown_type $poller_id
     * @param unknown_type $last_restart
     * @return number
     */
    public static function checkChangeState($poller_id, $last_restart)
    {
        if (!isset($last_restart) || $last_restart == "") {
            return 0;
        }

        // Get centreon DB and centreon storage DB connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconnStorage = $di->get('db_storage');

        $request = "SELECT *
            FROM log_action
            WHERE
                action_log_date > $last_restart AND
                ((object_type = 'host' AND
                object_id IN (
                    SELECT host_host_id
                        FROM centreon.cfg_engine_hosts_relations
                        WHERE engine_server_id = '$poller_id'
                )) OR
                    (object_type = 'service') AND
                        object_id IN (
                    SELECT service_service_id
                    FROM centreon.cfg_engine_hosts_relations nhr, centreon.cfg_hosts_services_relations hsr
                    WHERE engine_server_id = '$poller_id' AND hsr.host_host_id = nhr.host_host_id
        ))";
        $DBRESULT = $dbconnStorage->query($request);
        if ($DBRESULT->rowCount()) {
            return 1;
        }
        return 0;
    }
    
    /**
     * 
     * @param array $params
     * @return integer
     */
    public static function getTotalRecordsForDatatable($params)
    {
        // Get centreon DB and centreon storage DB connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        //
        $sqlCalengineServer = "SELECT COUNT(`id`) as nb_poller FROM `engine_server`";
        $stmtCalengineServer = $dbconn->query($sqlCalengineServer);
        $resultCalengineServer = $stmtCalengineServer->fetchAll(\PDO::FETCH_ASSOC);
        
        return $resultCalengineServer[0]['nb_poller'];
    }
    
    /**
     * 
     * @return array
     */
    public static function getPollerTemplates()
    {
        $di = Di::getDefault();
        
        $rawTemplatesList = array();
        $moduleList = Informations::getModuleList();
        foreach ($moduleList as $module) {
            $modulePath = Informations::getModulePath($module);
            $pollerTemplatesPath = $modulePath . '/pollers/*.json';
            $rawTemplatesList = array_merge($rawTemplatesList, glob($pollerTemplatesPath));
        }
        
        $templatesList = array();
        foreach ($rawTemplatesList as $template) {
            $tplName = basename($template, '.json');
            if (!in_array($tplName, array_keys($templatesList))) {
                $templatesList [$tplName] = $template;
            }
        }
        
        $di->set(
            'pollerTemplate',
            function() use ($templatesList) {
                return $templatesList;
            }
        );
    }

    /**
     * Create a poller
     *
     * @param array $params The parameters for create a poller
     * @return int The id of poller created
     */
    public static function create($params)
    {
        $di = Di::getDefault();
        $orgId = $di->get('organization');
        $nodeId = NodeRepository::create($params);
        $pollerId = Poller::insert(array(
            'node_id' => $nodeId,
            'name' => $params['poller_name'],
            'organization_id' => $orgId,
            'port' => 0,
            'tmpl_name' => $params['poller_tmpl']
        ));
        $values = new EngineFormSave($nodeId, $params);
        $di->get('events')->emit('centreon-configuration.form.save', array($values));
        return $pollerId;
    }
}
