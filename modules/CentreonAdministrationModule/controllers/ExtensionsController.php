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
 * 
 */

namespace CentreonAdministration\Controllers;

use Centreon\Internal\Controller;
use Centreon\Internal\Di;
use Centreon\Models\Module;
use Centreon\Internal\Form;
use Centreon\Internal\Utils\String;

class ExtensionsController extends Controller
{
    /**
     *
     * @var string
     */
    public static $objectName = 'Module';
    
    /**
     *
     * @var string
     */
    public static $objectDisplayName = 'Module';
    
    /**
     *
     * @var string
     */
    public static $moduleName = 'Centreon';
    
    /**
     *
     * @var string
     */
    protected $datatableObject = '\Centreon\Internal\Datatable\ModuleDatatable';
    
    /**
     *
     * @var string
     */
    protected $objectClass = '\Centreon\Models\Module';
    
    /**
     *
     * @var \Centreon\Internal\Di
     */
    protected $di;
    
    /**
     *
     * @var \Centreon\Internal\Template
     */
    protected $tpl;

    /**
     *
     * @var type
     */
    public static $displaySearchBar = false;
    
    /**
     *
     * @var type
     */
    public static $isDisableable = true;

    /**
     * 
     * @method get
     * @route /extensions/module
     */
    public function moduleAction()
    {
        $this->init();

        $this->tpl->addJs('bootstrap-switch.min.js')
            ->addJs('centreon.search.js')
            ->addJs('bootstrap3-typeahead.js');
        $this->tpl->addCss('bootstrap-switch.min.css');
        
        /* Display variable */
        $this->tpl->assign('objectName', self::$objectDisplayName);
        $this->tpl->assign('moduleName', self::$moduleName);
        $this->tpl->assign('isDisableable', static::$isDisableable);
        $this->tpl->assign('displaySearchBar', static::$displaySearchBar);
        
        $this->tpl->assign('objectListUrl', '/centreon-administration/extensions/module/list');
        $this->tpl->display('administration/module.tpl');
    }
    
    /**
     * 
     * @method get
     * @route /extensions/module/[i:id]
     */
    public function displayModuleAction()
    {
        $params = $this->getParams();
        $module = Module::get($params['id']);
        echo "<pre>";
            var_dump($module);
        echo "<pre>";
    }
    
    /**
     * 
     * @method get
     * @route /extensions/module/[*:shortname]/install
     */
    public function installModuleAction()
    {
        $router = $this->di->get('router');
        $config = $this->di->get('config');
        $centreonPath = rtrim($config->get('global', 'centreon_path'), '/');
        $params = $this->getParams();
        
        $commonName = str_replace(' ', '', ucwords(str_replace('-', ' ', $params['shortname'])));
        
        $moduleDirectory = $centreonPath
            . '/modules/'
            . $commonName
            . 'Module/';
        
        if (!file_exists(realpath($moduleDirectory . 'install/config.json'))) {
            throw new \Exception("The module is not valid because of a missing configuration file");
        }
        $moduleInfo = json_decode(file_get_contents($moduleDirectory . 'install/config.json'), true);
        // Launched Install
        $classCall = '\\'.$commonName.'\\Install\\Installer';
        $moduleInstaller = new $classCall($moduleDirectory, $moduleInfo, 'web');
        $moduleInstaller->install();

        $backUrl = $router->getPathFor('/centreon-administration/extensions/module');
        $router->response()->redirect($backUrl, 200);
    }
    
    /**
     * 
     * @method get
     * @route /extensions/module/[i:id]/uninstall
     */
    public function uninstallModuleAction()
    {
        $router = $this->di->get('router');
        $params = $this->getParams();
        $module = Module::get($params['id']);
        $config = $this->di->get('config');
        $centreonPath = $config->get('global', 'centreon_path');
        
        $commonName = str_replace(' ', '', ucwords(str_replace('-', ' ', $module['name'])));
        
        $moduleDirectory = $centreonPath
            . 'modules/'
            . $commonName
            . 'Module/';
        
        $classCall = '\\'.$commonName.'\\Install\\Installer';
        $moduleInstaller = new $classCall($moduleDirectory, $module);
        $moduleInstaller->remove();
        
        $backUrl = $router->getPathFor('/centreon-administration/extensions/module');
        $router->response()->redirect($backUrl, 200);
    }
    
    /**
     * 
     * @method get
     * @route /extensions/module/[i:id]/enable
     */
    public function enableModuleAction()
    {
        $router = $this->di->get('router');
        
        $params = $this->getParams();
        try {
            Module::update($params['id'], array('isactivated' => '1'));
        } catch (\Exception $e) {
            $router->response()->json(array('success' => false));
            return;
        }
        $router->response()->json(array('success' => true));
    }
    
    /**
     * 
     * @method get
     * @route /extensions/module/[i:id]/disable
     */
    public function disableModuleAction()
    {
        $router = $this->di->get('router');
        
        $params = $this->getParams();
        try {
            Module::update($params['id'], array('isactivated' => '0'));
        } catch (\Exception $e) {
            $router->response()->json(array('success' => false));
            return;
        }
        $router->response()->json(array('success' => true));
    }
    
    /**
     * 
     * @method get
     * @route /extensions/module/list
     */
    public function datatableAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        
        $myDatatable = new $this->datatableObject($this->getParams('get'), $this->objectClass);
        $myDataForDatatable = $myDatatable->getDatas();
        /* Secure strings */
        for ($i = 0; $i < count($myDataForDatatable['data']); $i++) {
            foreach ($myDataForDatatable['data'][$i] as $key => $value) {
                if (is_string($value)) {
                    $myDataForDatatable['data'][$i][$key] = String::escapeSecure($value);
                }
            }
        }
        
        $router->response()->json($myDataForDatatable);
    }

    /**
     * Initialize page
     *
     */
    protected function init()
    {
        $this->di = Di::getDefault();
        /* Init template */
        $this->tpl = $this->di->get('template');
        
        /* Load CssFile */
        $this->tpl->addCss('jquery.dataTables.min.css')
            ->addCss('dataTables.tableTools.min.css')
            ->addCss('dataTables.colVis.min.css')
            ->addCss('dataTables.colReorder.min.css')
            ->addCss('dataTables.bootstrap.css')
            ->addCss('select2.css')
            ->addCss('select2-bootstrap.css')
            ->addCss('centreon-wizard.css');

        /* Load JsFile */
        $this->tpl->addJs('jquery.dataTables.min.js')
            ->addJs('dataTables.tableTools.min.js')
            ->addJs('dataTables.colVis.min.js')
            ->addJs('dataTables.colReorder.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js')
            ->addJs('dataTables.bootstrap.js')
            ->addJs('jquery.select2/select2.min.js')
            ->addJs('jquery.validation/jquery.validate.min.js')
            ->addJs('jquery.validation/additional-methods.min.js')
            ->addJs('centreon-wizard.js');
        
        $this->tpl->assign('datatableObject', $this->datatableObject);
        
        parent::init();
    }
}
