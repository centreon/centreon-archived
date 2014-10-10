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

namespace CentreonConfiguration\Controllers;

use \Centreon\Form;
use \Centreon\Internal\Di;

class ServiceGroupController extends \CentreonConfiguration\Controllers\ObjectAbstract
{
    protected $objectDisplayName = 'Servicegroup';
    protected $objectName = 'servicegroup';
    protected $objectBaseUrl = '/configuration/servicegroup';
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
     * @route /configuration/servicegroup
     */
    public function listAction()
    {
        $router = Di::getDefault()->get('router');
        $this->tpl->addJs('centreon.tag.js', 'bottom', 'centreon-administration')
            ->addCss('centreon.tag.css', 'centreon-administration');
        $urls = array(
            'tag' => array(
                'add' => $router->getPathFor('/administration/tag/add'),
                'del' => $router->getPathFor('/administration/tag/delete')
            )
        );
        $this->tpl->append('jsUrl', $urls, true);
        parent::listAction();
    }

    /**
     * List service groups 
     *
     * @method get
     * @route /configuration/servicegroup/formlist
     */
    public function formListAction()
    {
        parent::formListAction();
    }

    /**
     * 
     * @method get
     * @route /configuration/servicegroup/list
     */
    public function datatableAction()
    {
        parent::datatableAction();
    }

    /**
     * Update a servicegroup
     *
     *
     * @method post
     * @route /configuration/servicegroup/update
     */
    public function updateAction()
    {
        parent::updateAction();
    }
    
    /**
     * Add a servicegroup
     *
     *
     * @method get
     * @route /configuration/servicegroup/add
     */
    public function addAction()
    {
        $this->tpl->assign('validateUrl', '/configuration/servicegroup/add');
        parent::addAction();
    }
    
    /**
     * Add a servicegroup
     *
     *
     * @method post
     * @route /configuration/servicegroup/add
     */
    public function createAction()
    {
        parent::createAction();
    }
    
    /**
     * Update a servicegroup
     *
     *
     * @method get
     * @route /configuration/servicegroup/[i:id]
     */
    public function editAction()
    {
        parent::editAction();
    }


    /**
     * Get the list of massive change fields
     *
     * @method get
     * @route /configuration/servicegroup/mc_fields
     */
    public function getMassiveChangeFieldsAction()
    {
        parent::getMassiveChangeFieldsAction();
    }

    /**
     * Get the html of attribute filed
     *
     * @method get
     * @route /configuration/servicegroup/mc_fields/[i:id]
     */
    public function getMcFieldAction()
    {
        parent::getMcFieldAction();
    }

    /**
     * Duplicate a hosts
     *
     * @method POST
     * @route /configuration/servicegroup/duplicate
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }

    /**
     * Apply massive change
     *
     * @method POST
     * @route /configuration/servicegroup/massive_change
     */
    public function massiveChangeAction()
    {
        parent::massiveChangeAction();
    }

    /**
     * Delete action for servicegroup
     *
     * @method post
     * @route /configuration/servicegroup/delete
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }
    
    /**
     * Enable action for service group
     * 
     * @method post
     * @route /configuration/servicegroup/enable
     */
    public function enableAction()
    {
        parent::enableAction('sg_activate');
    }
    
    /**
     * Disable action for service group
     * 
     * @method post
     * @route /configuration/servicegroup/disable
     */
    public function disableAction()
    {
        parent::disableAction('sg_activate');
    }

    /**
     * Get services for a specific service group
     *
     * @method get
     * @route /configuration/servicegroup/[i:id]/service
     */
    public function servicesForServicegroupAction()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $relObj = static::$relationMap['sg_services'];
        $listOfServices = $relObj::getHostIdServiceIdFromServicegroupId($requestParam['id']);
        
        //
        $finalList = array();
        foreach ($listOfServices as $obj) {
            $serviceDescription = \CentreonConfiguration\Models\Service::getParameters(
                $obj['service_id'],
                'service_description'
            );
            $hostName = \CentreonConfiguration\Models\Host::getParameters($obj['host_id'], 'host_name');
            $finalList[] = array(
                "id" => $obj['service_id'] . '_' . $obj['host_id'],
                "text" => $hostName['host_name'] . ' ' . $serviceDescription['service_description']
            );
        }
        $router->response()->json($finalList);
    }
}
