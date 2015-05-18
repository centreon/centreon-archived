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
 *
 */

namespace CentreonAdministration\Controllers;

use Centreon\Internal\Controller;
use Centreon\Internal\Di;
use Centreon\Models\Module;
use Centreon\Internal\Form;

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
        $moduleInstaller = new $classCall($moduleDirectory, $moduleInfo);

        // Check if all dependencies are satisfied
        $dependenciesCheckResult = $moduleInstaller->isDependenciesSatisfied();
        if ($dependenciesCheckResult['success']) {
            $moduleInstaller->install();
        } else {
            throw new \Exception("Cannot install module due to one or more missing dependencies");
        }

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
