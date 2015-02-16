<?php
/*
 * Copyright 2005-2014 CENTREON
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

use Centreon\Form;
use Centreon\Internal\Di;
use CentreonConfiguration\Models\Service;
use CentreonConfiguration\Models\Host;
use Centreon\Controllers\FormController;

class ServiceGroupController extends FormController
{
    protected $objectDisplayName = 'Servicegroup';
    public static $objectName = 'servicegroup';
    public static $enableDisableFieldName = 'sg_activate';
    protected $objectBaseUrl = '/centreon-configuration/servicegroup';
    protected $objectClass = '\CentreonConfiguration\Models\Servicegroup';
    protected $datatableObject = '\CentreonConfiguration\Internal\ServiceGroupDatatable';
    protected $repository = '\CentreonConfiguration\Repository\ServicegroupRepository';
    public static $relationMap = array(
        'sg_services' => '\CentreonConfiguration\Models\Relation\Servicegroup\Service'
    );
    
    public static $isDisableable = true;

    /**
     * List servicegroups
     *
     * @method get
     * @route /servicegroup
     */
    public function listAction()
    {
        $router = Di::getDefault()->get('router');
        $this->tpl->addJs('hogan-3.0.0.min.js')
            ->addJs('centreon.tag.js', 'bottom', 'centreon-administration')
            ->addCss('centreon.tag.css', 'centreon-administration');
        $urls = array(
            'tag' => array(
                'add' => $router->getPathFor('/centreon-administration/tag/add'),
                'del' => $router->getPathFor('/centreon-administration/tag/delete')
            )
        );
        $this->tpl->append('jsUrl', $urls, true);
        parent::listAction();
    }

    /**
     * Get services for a specific service group
     *
     * @method get
     * @route /servicegroup/[i:id]/service
     */
    public function servicesForServicegroupAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $relObj = static::$relationMap['sg_services'];
        $listOfServices = $relObj::getHostIdServiceIdFromServicegroupId($requestParam['id']);
        
        //
        $finalList = array();
        foreach ($listOfServices as $obj) {
            $serviceDescription = Service::getParameters(
                $obj['service_id'],
                'service_description'
            );
            $hostName = Host::getParameters($obj['host_id'], 'host_name');
            $finalList[] = array(
                "id" => $obj['service_id'] . '_' . $obj['host_id'],
                "text" => $hostName['host_name'] . ' ' . $serviceDescription['service_description']
            );
        }
        $router->response()->json($finalList);
    }
}
