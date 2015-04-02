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

use Centreon\Internal\Form;
use Centreon\Internal\Di;
use Centreon\Controllers\FormController;
use CentreonBam\Repository\IndicatorRepository;
use CentreonConfiguration\Models\Service;
use CentreonConfiguration\Models\Host;
use Centreon\Internal\Form\Generator\Web\Full as WebFormGenerator;

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
   //     'businessactivity_normalindicator' => '\CentreonBam\Models\Relation\BusinessActivity\NormalIndicator',
   //     'indicator_booleanindicator' => '\CentreonBam\Models\Relation\Indicator\BooleanIndicator',
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
        $updateSuccessful = true;
        $updateErrorMessage = '';

        try {
            IndicatorRepository::createIndicator($givenParameters);
            unset($_SESSION['form_token']);
            unset($_SESSION['form_token_time']);
            $this->router->response()->json(array('success' => true));
        } catch (\Centreon\Internal\Exception $e) {
            $updateSuccessful = false;
            $updateErrorMessage = $e->getMessage();
            $this->router->response()->json(array('success' => false,'error' => $updateErrorMessage));
        }
    }

    /**
     *
     * @method get
     * @route /indicator/[i:id]
     */
    public function editAction($additionnalParamsForSmarty = array())
    {
        $requestParam = $this->getParams('named');
        $objectFormUpdateUrl = $this->objectBaseUrl.'/update';
        $inheritanceUrl = null;
        if (false === is_null($this->inheritanceUrl)) {
            $inheritanceUrl = $this->router->getPathFor(
                $this->inheritanceUrl,
                array('id' => $requestParam['id'])
            );
        }

        $myForm = new WebFormGenerator($objectFormUpdateUrl, array('id' => $requestParam['id']));
        $myForm->getFormFromDatabase();
        $myForm->addHiddenComponent('object_id', $requestParam['id']);
        $myForm->addHiddenComponent('object', static::$objectName);


        // get object Current Values
        $myForm->setDefaultValues($this->objectClass, $requestParam['id']);

        $typeId = IndicatorRepository::getIndicatorType($requestParam['id']);
        if ($typeId === '3') {
            $paramsToUpdate = IndicatorRepository::getBooleanParameters($requestParam['id']);
            // set value for custom fields
            $myForm->setValues($this->objectClass, $requestParam['id'], $paramsToUpdate);
        }

        $formModeUrl = $this->router->getPathFor(
                            $this->objectBaseUrl.'/[i:id]',
                            array(
                                'id' => $requestParam['id']
                            )
                        );

        // Display page
        $this->tpl->assign('pageTitle', $this->objectDisplayName);
        $this->tpl->assign('form', $myForm->generate());
        $this->tpl->assign('advanced', $requestParam['advanced']);
        $this->tpl->assign('formModeUrl', $formModeUrl);
        $this->tpl->assign('formName', $myForm->getName());
        $this->tpl->assign('validateUrl', $objectFormUpdateUrl);

        foreach ($additionnalParamsForSmarty as $smartyVarName => $smartyVarValue) {
            $this->tpl->assign($smartyVarName, $smartyVarValue);
        }

        $this->tpl->assign('inheritanceUrl', $inheritanceUrl);

        if (isset($this->inheritanceTmplUrl)) {
            $this->tpl->assign(
                'inheritanceTmplUrl',
                $this->router->getPathFor(
                    $this->inheritanceTmplUrl
                )
            );
        }
        if (isset($this->tmplField)) {
            $this->tpl->assign('tmplField', $this->tmplField);
        }

        $this->tpl->display('file:[CentreonConfigurationModule]edit.tpl');
    }

    /**
     * Generic update function
     *
     * @method post
     * @route /indicator/update
     */
    public function updateAction()
    {
        $requestParam = $this->getParams();
        $typeId = $requestParam['kpi_type'];
        //if ($typeId === '0') {
            $givenParameters = $this->getParams('post');
            $updateSuccessful = true;
            $updateErrorMessage = '';

            try {
                IndicatorRepository::updateIndicator($givenParameters, 'form', $this->getUri());

                unset($_SESSION['form_token']);
                unset($_SESSION['form_token_time']);
                $this->router->response()->json(array('success' => true));
            } catch (\Centreon\Internal\Exception $e) {
                $updateSuccessful = false;
                $updateErrorMessage = $e->getMessage();
                $this->router->response()->json(array('success' => false,'error' => $updateErrorMessage));
            }
        /*} elseif ($typeId === '3') {
            $givenParameters = $this->getParams('post');
            $updateSuccessful = true;
            $updateErrorMessage = '';

            try {
                IndicatorRepository::updateBooleanIndicator($givenParameters, 'form', $this->getUri());
                parent::updateAction();

                unset($_SESSION['form_token']);
                unset($_SESSION['form_token_time']);
                $this->router->response()->json(array('success' => true));
            } catch (\Centreon\Internal\Exception $e) {
                $updateSuccessful = false;
                $updateErrorMessage = $e->getMessage();
                $this->router->response()->json(array('success' => false,'error' => $updateErrorMessage));
            }
        } else {
            parent::updateAction();
        }*/
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
    }

    /**
     * Get linked business activity for a specific kpi
     *
     *
     * @method get
     * @route /indicator/[i:id]/linkedbusinessactivity
     */
    public function linkedBusinessActivityForIndicatorAction()
    {
        parent::getSimpleRelation('id_ba', '\CentreonBam\Models\BusinessActivity');
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
