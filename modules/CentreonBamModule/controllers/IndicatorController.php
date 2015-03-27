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

namespace CentreonBam\Controllers;

use Centreon\Internal\Di;
use Centreon\Controllers\FormController;
use CentreonBam\Repository\IndicatorRepository;
use CentreonConfiguration\Models\Service;
use CentreonConfiguration\Models\Host;

class IndicatorController extends FormController
{
    protected $objectDisplayName = 'Indicator';
    public static $objectName = 'indicator';
    protected $objectBaseUrl = '/centreon-bam/indicator';
    protected $objectClass = '\CentreonBam\Models\Indicator';
    protected $datatableObject = '\CentreonBam\Internal\IndicatorDatatable';
    protected $repository = '\CentreonBam\Repository\IndicatorRepository';     
    public static $relationMap = array(
        'indicator_service' => '\CentreonBam\Models\Relation\Indicator\Service',
        'businessactivity_normalindicator' => '\CentreonBam\Models\Relation\BusinessActivity\NormalIndicator'
    );

    /**
    * Create a new indicator
    *
    * @method post
    * @route /indicator/add
    */
    public function createAction()
    {
        $givenParameters = $this->getParams('post');

        IndicatorRepository::createIndicator($givenParameters);

        $this->router->response()->json(array('success' => true));
        //$this->router->response()->json(array('success' => false, 'error' => 'problem'));
    }

    /**
     *
     * @method get
     * @route /indicator/[i:id]
     */
    public function editAction($additionnalParamsForSmarty = array())
    {
        $params = $this->getParams('named');
        $typeId = IndicatorRepository::getType($params['id']);
       
        $additionnalParams = array(
            'boolean_expression' => 'toto'
        );
 
        parent::editAction($additionnalParams);
    }

    /**
    *
    * @method get
    * @route /indicator
    */
    public function listAction()
    {
        $router = Di::getDefault()->get('router');
        $this->tpl->addJs('hogan-3.0.0.min.js')
            ->addJs('centreon.tag.js', 'bottom', 'centreon-administration')
            ->addJs('jquery.select2/select2.min.js');

        $this->tpl->addCss('centreon.tag.css', 'centreon-administration')
                  ->addCss('select2.css')
                  ->addCss('select2-bootstrap.css');

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
     * Get service for a specific kpi
     *
     *
     * @method get
     * @route /indicator/[i:id]/service
     */
    public function serviceForIndicatorAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');

        $requestParam = $this->getParams('named');

        $relObj = static::$relationMap['indicator_service'];
        $listOfServices = $relObj::getHostIdServiceIdFromKpiId($requestParam['id']);

        $finalList = array();
        if (isset($listOfServices[0])) {
            $serviceDescription = Service::getParameters(
                $listOfServices[0]['service_id'],
                'service_description'
            );
            $hostName = Host::getParameters($listOfServices[0]['host_id'], 'host_name');
            $finalList = array(
                "id" => $listOfServices[0]['service_id'] . '_' . $listOfServices[0]['host_id'],
                "text" => $hostName['host_name'] . ' ' . $serviceDescription['service_description']
            );
        }
        $router->response()->json($finalList);
    }

    /**
     * Get business activity for a specific kpi
     *
     *
     * @method get
     * @route /indicator/[i:id]/businessactivity
     */
    public function businessActivityForIndicatorAction()
    {
        parent::getSimpleRelation('id_indicator_ba', '\CentreonBam\Models\BusinessActivity');
        //parent::getRelations(static::$relationMap['service_traps']);
    }

    /**
     *
     * @method get
     * @route /indicator/formlist
     */
    public function formListIndicatorAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $finalList = IndicatorRepository::getIndicatorsName();

        $router->response()->json($finalList);
    }
}
