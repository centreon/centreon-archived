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

use Centreon\Internal\Di;
use CentreonConfiguration\Internal\Poller\Template\Manager as TemplateManager;
use CentreonConfiguration\Events\EngineFormSave;
use CentreonConfiguration\Events\BrokerFormSave;
use CentreonConfiguration\Models\Poller;
use CentreonConfiguration\Models\Node;
use CentreonConfiguration\Repository\Repository;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class PollerRepository extends Repository
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
     *
     * @param int $pollerId
     * @param int $lastRestart
     * @return int
     */
    public static function checkChangeState($pollerId, $lastRestart)
    {
        if (!isset($lastRestart) || !$lastRestart) {
            return 0;
        }

        // Get centreon DB and centreon storage DB connection
        $di = Di::getDefault();
        $db = $di->get('db_centreon');

        $request = "SELECT *
            FROM log_action
            WHERE
                action_log_date > ? AND
                ((object_type = 'host' AND
                object_id IN (
                    SELECT host_id
                        FROM cfg_pollers_hosts_relations
                        WHERE poller_id = ?
                )) OR
                    (object_type = 'service') AND
                        object_id IN (
                    SELECT service_service_id
                    FROM cfg_pollers_hosts_relations nhr, cfg_hosts_services_relations hsr
                    WHERE nhr.poller_id = ? AND hsr.host_host_id = nhr.host_id
        ))";
        $stmt = $db->prepare($request);
        $stmt->execute(array($lastRestart, $pollerId, $pollerId));
        if ($stmt->rowCount()) {
            return 1;
        }
        return 0;
    }
    
    /**
     * 
     * @return array
     */
    public static function getPollerTemplates()
    {
        $di = Di::getDefault();
        
        $templatesList = array_map(
            function($t) {
                return serialize($t);
            },
            TemplateManager::buildTemplatesList()
        );
        
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
        $engineEvent = new EngineFormSave($pollerId, $params);
        $di->get('events')->emit('centreon-configuration.engine.form.save', array($engineEvent));

        $brokerEvent = new BrokerFormSave($pollerId, $params);
        $di->get('events')->emit('centreon-configuration.broker.form.save', array($brokerEvent));

        return $pollerId;
    }


    /**
     * Poller update function
     *
     * @param array $givenParameters The parameters for update a poller
     */
    public static function update($params)
    {
        $di = Di::getDefault();
        NodeRepository::update($params);
        Poller::update(
            $params['poller_id'],
            array(
                'name' => $params['poller_name'],
                'tmpl_name' => $params['poller_tmpl']
            )
        );
        $engineEvent = new EngineFormSave($params['poller_id'], $params);
        $di->get('events')->emit('centreon-configuration.engine.form.save', array($engineEvent));
        
        $brokerEvent = new BrokerFormSave($params['poller_id'], $params);
        $di->get('events')->emit('centreon-configuration.broker.form.save', array($brokerEvent));
    }


    /**
     * Delete a poller
     *
     * @param array $ids | array of ids to delete
     */
    public static function delete($ids)
    {
        foreach ($ids as $id) {
            if ($id) {
                $node = static::getNode($id);
                Node::delete($node['node_id']);
            }
        }
        /* Poller will also get deleted due to delete cascade */
    }

    /**
     * Get the node information
     *
     * @return array
     */
    public static function getNode($pollerId)
    {
        $poller = Poller::get($pollerId);
        return Node::get($poller['node_id']);
    }
    
    /**
     * 
     * @param integer $pollerId
     * @return type
     * @throws Exception
     */
    public static function getTemplate($pollerId)
    {
        $paramsPoller = Poller::get($pollerId, 'tmpl_name');
        if (!isset($paramsPoller['tmpl_name']) || is_null($paramsPoller['tmpl_name'])) {
            throw new Exception('Not template defined');
        }
        $tmplName = $paramsPoller['tmpl_name'];

        /* Load template information for poller */
        $listTpl = TemplateManager::buildTemplatesList();
        if (!isset($listTpl[$tmplName])) {
            throw new Exception('The template is not found on list of templates');
        }
        
        return $listTpl[$tmplName];
    }
}
