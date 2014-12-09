<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Lionel Assepo and Romain Le Merlus under
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

namespace CentreonConfiguration\Api\Rest;

use Centreon\Api\Rest\BasicFormApi;

/**
 * Login controller
 * @authors Lionel Assepo
 * @package Centreon
 * @subpackage Controllers
 */
class ServiceApi extends BasicFormApi
{
    public static $moduleShortName = 'centreon-configuration';
    protected $objectName = 'service';
    protected $objectBaseUrl = '/centreon-configuration/service';
    protected $objectClass = '\CentreonConfiguration\Models\Service';
    protected $repository = '\CentreonConfiguration\Repository\ServiceRepository';
    public static $relationMap = array(
        'host' => '\CentreonConfiguration\Models\Relation\Host\Service',
        'servicegroup' => '\CentreonConfiguration\Models\Relation\Service\Servicegroup',
        'servicecategory' => '\CentreonConfiguration\Models\Relation\Service\Servicecategory',
        'parents' => '\CentreonConfiguration\Models\Relation\Service\Serviceparents',
        'children' => '\CentreonConfiguration\Models\Relation\Service\Servicechildren',
    );
    
    public static $simpleRelationMap = array(
        'servicetemplate' => 'service_template_model_stm_id'
    );
    
    /**
     * Action for listing services
     *
     * @method GET
     * @route /service
     */
    public function listAction()
    {
        $set = 'service_id,service_description,service_activate';
        parent::listAction($set);
    }

    /**
     * Action to get info a specific service
     *
     * @method GET
     * @route /service/[:id]
     */
    public function viewAction()
    {
        parent::viewAction();
    }
    
    /**
     * Action to get info a specific service with relations
     *
     * @method GET
     * @route /service/[:id]/links/[a:object]
     */
    public function viewWithRelationAction()
    {
        parent::viewAction();
    }

    /**
     * Action for update 
     *
     * @method PUT
     * @route /service/[i:id]
     */
    public function updateAction()
    {
        parent::updateAction();
    }

    /**
     * Action for add
     *
     * @method POST
     * @route /service
     */
    public function addAction()
    {
        parent::createAction();
    }

    /**
     * Action for delete
     *
     * @method DELETE
     * @route /service/[:id]
     */
    public function deleteAction()
    {
        parent::deleteAction();
    }

    /**
     * Action for duplicate
     *
     * @method POST
     * @route /service/[i:id]
     */
    public function duplicateAction()
    {
        parent::createAction();
    }
}
