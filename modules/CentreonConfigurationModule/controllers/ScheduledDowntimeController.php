<?php
/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonConfiguration\Controllers;

use Centreon\Internal\Di;
use Centreon\Controllers\FormController;
use CentreonConfiguration\Repository\ScheduledDowntimeRepository;

/**
 * Configure scheduled downtime
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @package CentreonConfiguration
 * @subpackage Controller
 * @version 3.0.0
 */
class ScheduledDowntimeController extends FormController
{
    protected $objectDisplayName = 'ScheduledDowntime';
    public static $objectName = 'scheduled-downtime';
    public static $enableDisableFieldName = 'dt_activate';
    protected $objectBaseUrl = '/centreon-configuration/scheduled-downtime';
    protected $datatableObject = '\CentreonConfiguration\Internal\ScheduledDowntimeDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\ScheduledDowntime';
    protected $repository = '\CentreonConfiguration\Repository\ScheduledDowntimeRepository';
    public static $isDisableable = true;

    public static $relationMap = array(
        'dt_hosts' => '\CentreonConfiguration\Models\Relation\ScheduledDowntime\Hosts',
        'dt_hosts_tags' => '\CentreonConfiguration\Models\Relation\ScheduledDowntime\HostsTags',
        'dt_services' => '\CentreonConfiguration\Models\Relation\ScheduledDowntime\Services',
        'dt_services_tags' => '\CentreonConfiguration\Models\Relation\ScheduledDowntime\ServicesTags'
    );

    /**
     * Create a new period
     *
     * @method post
     * @route /scheduled-downtime/add
     */
    public function createAction()
    {
        $givenParameters = $this->getParams('post');
    }

    /**
     * Update a period
     *
     * @method post
     * @route /scheduled-downtime/update
     */
    public function updateAction()
    {
        $givenParameters = $this->getParams('post');
        $periods = json_decode($givenParameters['periods'], true);
        $dbconn = Di::getDefault()->get('db_centreon');

        /* Update the periods */
        ScheduledDowntimeRepository::updatePeriods($givenParameters['object_id'], $periods);

        parent::updateAction();
    }

    /**
     * Get host relation for a scheduled downtime
     *
     * @method get
     * @route /scheduled-downtime/[i:id]/host
     */
    public function getHostRelationAction()
    {
        $router = Di::getDefault()->get('router');

        $params = $this->getParams('named');
        $hostList = ScheduledDowntimeRepository::getHostRelation($params['id']);

        $router->response()->json($hostList);
    }

    /**
     * Get host tag relation for a scheduled downtime
     *
     * @method get
     * @route /scheduled-downtime/[i:id]/host/tag
     */
    public function getHostTagRelationAction()
    {
        $router = Di::getDefault()->get('router');

        $params = $this->getParams('named');
        $tagList = ScheduledDowntimeRepository::getHostTagRelation($params['id']);

        $router->response()->json($tagList);
    }

    /**
     * Get service relation for a scheduled downtime
     *
     * @method get
     * @route /scheduled-downtime/[i:id]/service
     */
    public function getServiceRelationAction()
    {
        $router = Di::getDefault()->get('router');

        $params = $this->getParams('named');
        $serviceList = ScheduledDowntimeRepository::getServiceRelation($params['id']);

        $router->response()->json($serviceList);
    }

    /**
     * Get service tag relation for a scheduled downtime
     *
     * @method get
     * @route /scheduled-downtime/[i:id]/service/tag
     */
    public function getServiceTagRelationAction()
    {
        $router = Di::getDefault()->get('router');

        $params = $this->getParams('named');
        $tagList = ScheduledDowntimeRepository::getServiceTagRelation($params['id']);

        $router->response()->json($tagList);
    }
}
