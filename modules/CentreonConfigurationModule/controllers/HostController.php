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

use Centreon\Internal\Di;
use CentreonConfiguration\Models\Host;
use CentreonConfiguration\Models\Relation\Host\Hostchildren;
use CentreonConfiguration\Models\Relation\Host\Hostparents;
use CentreonConfiguration\Models\Relation\Host\Poller;
use CentreonConfiguration\Models\Timeperiod;
use CentreonConfiguration\Models\Command;
use CentreonConfiguration\Internal\HostDatatable;
use CentreonConfiguration\Repository\HostRepository;
use CentreonConfiguration\Repository\HostTemplateRepository;
use CentreonConfiguration\Repository\CustomMacroRepository;
use Centreon\Controllers\FormController;

class HostController extends FormController
{
    protected $objectDisplayName = 'Host';
    public static $objectName = 'host';
    public static $enableDisableFieldName = 'host_activate';
    protected $datatableObject = '\CentreonConfiguration\Internal\HostDatatable';
    protected $objectBaseUrl = '/centreon-configuration/host';
    protected $objectClass = '\CentreonConfiguration\Models\Host';
    protected $repository = '\CentreonConfiguration\Repository\HostRepository';

    protected $inheritanceUrl = '/centreon-configuration/host/[i:id]/inheritance';
    protected $inheritanceTmplUrl = '/centreon-configuration/hosttemplate/inheritance';
    protected $tmplField = '#host_hosttemplates';
    
    public static $relationMap = array(
        'host_hostgroups' => '\CentreonConfiguration\Models\Relation\Host\Hostgroup',
        'host_hostcategories' => '\CentreonConfiguration\Models\Relation\Host\Hostcategory',
        'host_parents' => '\CentreonConfiguration\Models\Relation\Host\Hostparents',
        'host_childs' => '\CentreonConfiguration\Models\Relation\Host\Hostchildren',
        'host_hosttemplates' => '\CentreonConfiguration\Models\Relation\Host\Hosttemplate',
        'host_icon' => '\CentreonConfiguration\Models\Relation\Host\Icon'
    );
    
    public static $isDisableable = true;

    /**
     * List hosts
     *
     * @method get
     * @route /host
     */
    public function listAction()
    {
        $router = Di::getDefault()->get('router');
        $this->tpl->addJs('centreon.overlay.js')
            ->addJs('jquery.qtip.min.js')
            ->addJs('hogan-3.0.0.min.js')
            ->addJs('centreon.tag.js', 'bottom', 'centreon-administration')
            ->addCss('centreon.qtip.css')
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
     * 
     * @method get
     * @route /host/list
     */
    public function datatableAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $myDatatable = new HostDatatable($this->getParams('get'), $this->objectClass);
        $myDataForDatatable = $myDatatable->getDatas();
        
        $router->response()->json($myDataForDatatable);
    }
    
    /**
     * Create a new host
     *
     * @method post
     * @route /host/add
     */
    public function createAction()
    {
        $macroList = array();
        
        $givenParameters = $this->getParams('post');
        
        $givenParameters['host_register'] = 1;
        
        if (isset($givenParameters['macro_name']) && isset($givenParameters['macro_value'])) {
            
            $macroName = $givenParameters['macro_name'];
            $macroValue = $givenParameters['macro_value'];
            
            $macroHidden = $givenParameters['macro_hidden'];
            
            $nbMacro = count($macroName);
            for($i=0; $i<$nbMacro; $i++) {
                if (!empty($macroName[$i])) {
                    if (isset($macroHidden[$i])) {
                        $isPassword = '1';
                    } else {
                        $isPassword = '0';
                    }
                    
                    $macroList[$macroName[$i]] = array(
                        'value' => $macroValue[$i],
                        'ispassword' => $isPassword
                    );
                }
            }
        }
        
        if (!isset($givenParameters['host_alias']) && isset($givenParameters['host_name'])) {
            $givenParameters['host_alias'] = $givenParameters['host_name'];
        }
        $id = parent::createAction(false);
        
        if (count($macroList) > 0) {
            CustomMacroRepository::saveHostCustomMacro($id, $macroList);
        }
        
        Host::deployServices($id);
        
        $this->router->response()->json(array('success' => true));
    }

    /**
     * Update a host
     *
     *
     * @method post
     * @route /host/update
     */
    public function updateAction()
    {
        $givenParameters = $this->getParams('post');
        $macroList = array();
        
        if (isset($givenParameters['macro_name']) && isset($givenParameters['macro_value'])) {
            
            $macroName = $givenParameters['macro_name'];
            $macroValue = $givenParameters['macro_value'];
            $macroHidden = $givenParameters['macro_hidden'];
            
            $nbMacro = count($macroName);
            for($i=0; $i<$nbMacro; $i++) {
                if (!empty($macroName[$i])) {
                    if (isset($macroHidden[$i])) {
                        $isPassword = '1';
                    } else {
                        $isPassword = '0';
                    }
                    
                    $macroList[$macroName[$i]] = array(
                        'value' => $macroValue[$i],
                        'ispassword' => $isPassword
                    );
                }
            }
        }
        
        if (!isset($givenParameters['host_alias']) && isset($givenParameters['host_name'])) {
            $givenParameters['host_alias'] = $givenParameters['host_name'];
        }
        
        if (count($macroList) > 0) {
            CustomMacroRepository::saveHostCustomMacro($givenParameters['object_id'], $macroList);
        }
        
        parent::updateAction();
        if ($givenParameters['host_create_services_from_template']) {
            Host::deployServices($givenParameters['object_id']);
        }
    }
    
    /**
     * Get list of hostgroups for a specific host
     *
     *
     * @method get
     * @route /host/[i:id]/hostgroup
     */
    public function hostgroupForHostAction()
    {
        parent::getRelations(static::$relationMap['host_hostgroups']);
    }
    
    /**
     * Get list of hostcategories for a specific host
     *
     *
     * @method get
     * @route /host/[i:id]/hostcategory
     */
    public function hostcategoryForHostAction()
    {
        parent::getRelations(static::$relationMap['host_hostcategories']);
    }
    
    /**
     * Get list of hostcategories for a specific host
     *
     *
     * @method get
     * @route /host/[i:id]/icon
     */
    public function iconForHostAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $objCall = static::$relationMap['host_icon'];
        $icon = $objCall::getIconForHost($requestParam['id']);
        $finalIconList = array();
        if (count($icon) > 0) {
            $filenameExploded = explode('.', $icon['filename']);
            $nbOfOccurence = count($filenameExploded);
            $fileFormat = $filenameExploded[$nbOfOccurence-1];
            $filenameLength = strlen($icon['filename']);
            $routeAttr = array(
                'image' => substr($icon['filename'], 0, ($filenameLength - (strlen($fileFormat) + 1))),
                'format' => '.'.$fileFormat
            );
            $imgSrc = $router->getPathFor('/uploads/[*:image][png|jpg|gif|jpeg:format]', $routeAttr);
            $finalIconList = array(
                "id" => $icon['binary_id'],
                "text" => $icon['filename'],
                "theming" => '<img src="'.$imgSrc.'" style="width:20px;height:20px;"> '.$icon['filename']
            );
        }
        
        $router->response()->json($finalIconList);
        
    }

    /**
     * Get host template for a specific host
     *
     * @method get
     * @route /host/[i:id]/hosttemplate
     */
    public function hostTemplateForHostAction()
    {
        parent::getRelations(static::$relationMap['host_hosttemplates']);
    }

    /**
     * 
     * @method get
     * @route /host/[i:id]/parent
     */
    public function parentForHostAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $HostparentsList = Hostparents::getMergedParameters(
            array('host_id', 'host_name'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('cfg_hosts_hostparents_relations.host_host_id' => $requestParam['id']),
            "AND"
        );

        $finalHostList = array();
        foreach ($HostparentsList as $Hostparents) {
            $finalHostList[] = array(
                "id" => $Hostparents['host_id'],
                "text" => $Hostparents['host_name'],
                "theming" => HostRepository::getIconImage(
                    $Hostparents['host_name']
                ).' '.$Hostparents['host_name']
            );
        }
        
        $router->response()->json($finalHostList);
    }

    /**
     * 
     * @method get
     * @route /host/[i:id]/child
     */
    public function childForHostAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $requestParam = $this->getParams('named');
        
        $HostchildrenList = Hostchildren::getMergedParameters(
            array('host_id', 'host_name'),
            array(),
            -1,
            0,
            null,
            "ASC",
            array('cfg_hosts_hostparents_relations.host_parent_hp_id' => $requestParam['id']),
            "AND"
        );

        $finalHostList = array();
        foreach ($HostchildrenList as $Hostchildren) {
            $finalHostList[] = array(
                "id" => $Hostchildren['host_id'],
                "text" => $Hostchildren['host_name'],
                "theming" => HostRepository::getIconImage(
                    $Hostchildren['host_name']
                ).' '.$Hostchildren['host_name']
            );
        }
        
        $router->response()->json($finalHostList);
    }
    
    /**
     * Get list of Environment for a specific host
     *
     *
     * @method get
     * @route /host/[i:id]/environment
     */
    public function checkEnvironmentHostAction()
    {
        parent::getSimpleRelation('environment_id', '\CentreonConfiguration\Models\Environment');
    }
    
    /**
     * Get list of Timeperiods for a specific host
     *
     *
     * @method get
     * @route /host/[i:id]/checkperiod
     */
    public function checkPeriodForHostAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id', '\CentreonConfiguration\Models\Timeperiod');
    }
    
    /**
     * Get list of Timeperiods for a specific host
     *
     *
     * @method get
     * @route /host/[i:id]/notificationperiod
     */
    public function notificationPeriodForHostAction()
    {
        parent::getSimpleRelation('timeperiod_tp_id2', '\CentreonConfiguration\Models\Timeperiod');
    }

    /**
     * Get check command for a specific host
     *
     * @method get
     * @route /host/[i:id]/checkcommand
     */
    public function checkcommandForHostAction()
    {
        parent::getSimpleRelation('command_command_id', '\CentreonConfiguration\Models\Command');
    }

    /**
     * Get list of Commands for a specific host
     *
     *
     * @method get
     * @route /host/[i:id]/eventhandler
     */
    public function eventHandlerForHostAction()
    {
        parent::getSimpleRelation('command_command_id2', '\CentreonConfiguration\Models\Command');
    }

    /**
     * Get list of pollers for a specific host
     *
     *
     * @method get
     * @route /host/[i:id]/poller
     */
    public function pollerForHostAction()
    {
        parent::getSimpleRelation('poller_id', '\CentreonConfiguration\Models\Poller');
    }
    
    /**
     * Display the configuration snapshot of a host
     * with template inheritance
     *
     * @method get
     * @route /host/snapshot/[i:id]
     */
    public function snapshotAction()
    {
        $params = $this->getParams();
        $data = HostRepository::getConfigurationData($params['id']);
        list($checkdata, $notifdata) = HostRepository::formatDataForTooltip($data);
        $this->tpl->assign('checkdata', $checkdata);
        $this->tpl->display('file:[CentreonConfigurationModule]host_conf_tooltip.tpl');
    }

    /**
     * Get inheritance value
     *
     * @method get
     * @route /host/[i:id]/inheritance
     */
    public function getInheritanceAction()
    {
        $router = Di::getDefault()->get('router');
        $requestParam = $this->getParams('named');

        $inheritanceValues = HostRepository::getInheritanceValues($requestParam['id']);
        array_walk($inheritanceValues, function(&$item, $key) {
            if (false === is_null($item)) {
                $item = HostTemplateRepository::getTextValue($key, $item);
            }
        });
        $router->response()->json(array(
            'success' => true,
            'values' => $inheritanceValues));
    }
}
