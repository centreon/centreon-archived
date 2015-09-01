<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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

namespace CentreonConfiguration\Repository;

use Centreon\Internal\Module\Informations;
use Centreon\Internal\Di;
use CentreonConfiguration\Models\Node;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class NodeRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $objectName = 'Node';

    /**
     * Create a node
     *
     * @param array $params The parameters for create a node
     * @return int The id of node created
     */
    public static function create($params)
    {
        $di = Di::getDefault();
        return Node::insert(array(
            'name' => $params['name'],
            'ip_address' => $params['ip_address']
        ));
    }

    /**
     * Update a node
     *
     * @param array $params The parameters for create a node
     */
    public static function update($params)
    {
        if (isset($params['object_id'])) {
            $poller_id = $params['object_id'];
        } else {
            $poller_id = $params['poller_id'];
        }
        $result = PollerRepository::getNode($poller_id);
        if (!isset($result['node_id'])) {
            throw new Exception(sprintf('Could not find node id from poller id %s', $poller_id));
        }
        $nodeId = $result['node_id'];
        $nodeParams = array();
        if (isset($params['name'])) {
            $nodeParams['name'] = $params['name'];
        }
        if (isset($params['ip_address'])) {
            $nodeParams['ip_address'] = $params['ip_address'];
        }
        if (isset($params['enable'])) {
            $nodeParams['enable'] = $params['enable'];
        }
        Node::update($nodeId, $nodeParams);
    }
}
