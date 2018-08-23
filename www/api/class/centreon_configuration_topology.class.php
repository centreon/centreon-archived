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

class CentreonConfigurationTopology extends CentreonConfigurationObjects
{

    /**
     * @SWG\Post(
     *   path="/centreon/api/internal.php",
     *   operationId="getTopologyData",
     *   @SWG\Parameter(
     *       in="query",
     *       name="object",
     *       type="string",
     *       description="the name of the API object class",
     *       required=true,
     *       enum="centreon_configuration_topology",
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="action",
     *       type="string",
     *       description="the name of the action in the API class",
     *       required=true,
     *       enum="getTopologyData",
     *   ),
     *   @SWG\Parameter(
     *       in="formData",
     *       name="topology_id",
     *       description="the ID of the topology page",
     *       required=true,
     *       type="string",
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="JSON with flag is_react for the topology"
     *   )
     * )
     *
     * Get data for topology_id
     * @return string
     * @throws \RestBadRequestException
     */
    public function postGetTopologyData()
    {
        if (!isset($_POST['topology_id']) || !$_POST['topology_id']) {
            throw new \RestBadRequestException('You need to send \'topology_id\' in the request.');
        }

        $topologyID = (int) $_POST['topology_id'];
        $statement = $this->pearDB->prepare('SELECT `topology_url`, `is_react` FROM `topology` WHERE `topology_id` = :id');
        $statement->execute([':id' => $topologyID]);
        $result = $statement->fetch();

        if (!$result) {
            throw new \RestBadRequestException('No topology found.');
        }

        return json_encode([
            'url'      => $result['topology_url'],
            'is_react' => (bool) $result['is_react'],
        ]);
    }
}
