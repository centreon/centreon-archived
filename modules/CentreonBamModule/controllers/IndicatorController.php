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
    public static $enableDisableFieldName = 'activate';
    protected $objectBaseUrl = '/centreon-bam/indicator';
    protected $objectClass = '\CentreonBam\Models\Indicator';
    protected $datatableObject = '\CentreonBam\Internal\IndicatorDatatable';
    protected $repository = '\CentreonBam\Repository\IndicatorRepository';     
    public static $relationMap = array(
        'indicator_service' => '\CentreonBam\Models\Relation\Indicator\Service',
    );

    public static $isDisableable = true;

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

        $aReturn = self::checkDatas($givenParameters);
        
        if (!$aReturn['success']) {
            $this->router->response()->json(array('success' => $aReturn['success'],'error' => $aReturn['error']));
            return;
        }
        
        $route = $this->getUri();

        try {
            IndicatorRepository::createIndicator($givenParameters, 'wizard', $route);
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
        
        $givenParameters = $this->getParams('post');
        $updateSuccessful = true;
        $updateErrorMessage = '';
        
        $aReturn = self::checkDatas($givenParameters);
        
        if (!$aReturn['success']) {
            $this->router->response()->json(array('success' => $aReturn['success'],'error' => $aReturn['error']));
            return;
        }

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

        $this->tpl->addCss('select2.css')
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
        if (isset($listOfServices[0]) && !empty($listOfServices[0]['service_id']) && !empty($listOfServices[0]['host_id'])) {
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
    
    private function checkDatas($aParams)
    {
        $updateSuccessful = true;
        $updateErrorMessage = "";
        switch ($aParams['kpi_type']):
            case '0':
                $iServiceId = trim($aParams['service_id']);
                if (empty($iServiceId)) {
                    $updateSuccessful = false;
                    $updateErrorMessage = _("The service is mandatory field");
                }
                break;
            case '2':
                $iIndicatorBa = trim($aParams['id_indicator_ba']);
                if (empty($iIndicatorBa)) {
                    $updateSuccessful = false;
                    $updateErrorMessage = _("The BA is mandatory field");
                }
                break;
            case '3':
                $sBooelanName = trim($aParams['boolean_name']);
                if (empty($sBooelanName)) {
                    $updateSuccessful = false;
                    $updateErrorMessage = _("The name of boolean is mandatory field");
                }
                break;
        endswitch;
 
        return array('success' => $updateSuccessful, 'error' => $updateErrorMessage);
    }
}
