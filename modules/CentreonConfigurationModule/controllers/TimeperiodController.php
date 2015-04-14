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
use CentreonConfiguration\Models\Timeperiod;
use CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodincluded;
use CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodexcluded;
use Centreon\Controllers\FormController;

class TimeperiodController extends FormController
{
    protected $objectDisplayName = 'Timeperiod';
    public static $objectName = 'timeperiod';
    protected $objectBaseUrl = '/centreon-configuration/timeperiod';
    protected $datatableObject = '\CentreonConfiguration\Internal\TimeperiodDatatable';
    protected $objectClass = '\CentreonConfiguration\Models\Timeperiod';
    protected $repository = '\CentreonConfiguration\Repository\TimePeriodRepository';    
    public static $relationMap = array(
        'tp_include' => '\CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodincluded',
        'tp_exclude' => '\CentreonConfiguration\Models\Relation\Timeperiod\Timeperiodexcluded'
    );

    /**
     * 
     * @method get
     * @route /timeperiod/[i:id]/include
     */
    public function includedTimeperiodAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $includedTimeperiodList = Timeperiodincluded::getMergedParameters(
            array('tp_id', 'tp_name'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('timeperiod_include_relations.timeperiod_id' => $requestParam['id']),
            "AND"
        );

        $finalTimeperiodList = array();
        foreach ($includedTimeperiodList as $includedTimeperiod) {
            $finalTimeperiodList[] = array(
                "id" => $includedTimeperiod['tp_id'],
                "text" => $includedTimeperiod['tp_name']
            );
        }
        
        $router->response()->json($finalTimeperiodList);
    }
    
    /**
     * 
     * @method get
     * @route /timeperiod/[i:id]/exclude
     */
    public function excludedTimeperiodAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $excludedTimeperiodList = Timeperiodexcluded::getMergedParameters(
            array('tp_id', 'tp_name'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('timeperiod_exclude_relations.timeperiod_id' => $requestParam['id']),
            "AND"
        );

        $finalTimeperiodList = array();
        foreach ($excludedTimeperiodList as $excludedTimeperiod) {
            $finalTimeperiodList[] = array(
                "id" => $excludedTimeperiod['tp_id'],
                "text" => $excludedTimeperiod['tp_name']
            );
        }
        
        $router->response()->json($finalTimeperiodList);
    }
}
