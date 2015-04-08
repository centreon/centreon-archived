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
 *
 */

namespace CentreonCustomview\Controllers;

use CentreonCustomview\Repository\WidgetRepository;
use Centreon\Internal\Controller;
use Centreon\Models\WidgetModel;
use Centreon\Internal\Exception;
use Centreon\Internal\Di;
use Centreon\Internal\Form;

class ExtensionsController extends Controller
{
    public static $objectName = 'WidgetModel';
    public static $objectDisplayName = 'WidgetModel';
    public static $moduleName = 'CentreonCustomview';
    protected $datatableObject = '\CentreonCustomview\Internal\WidgetDatatable';
    protected $objectClass = '\CentreonCustomview\Models\WidgetModel';
    protected $di;
    protected $tpl;

    /**
     * 
     * @method get
     * @route /extensions/widgets
     */
    public function widgetAction()
    {
        $this->init();

        $this->tpl->addJs('bootstrap-switch.min.js')
            ->addJs('centreon.search.js')
            ->addJs('bootstrap3-typeahead.js');
        $this->tpl->addCss('bootstrap-switch.min.css');
        
        /* Display variable */
        $this->tpl->assign('objectName', self::$objectDisplayName);
        $this->tpl->assign('moduleName', self::$moduleName);
        $this->tpl->assign('objectListUrl', '/centreon-customview/extensions/widgets/list');
        $this->tpl->display('administration/module.tpl');
    }
    
    /**
     * 
     * @method get
     * @route /extensions/widgets/[i:id]
     */
    public function displayWidgetAction()
    {
        $params = $this->getParams();
        $widget = WidgetModel::get($params['id']);
        echo "<pre>"; var_dump($widget); echo "<pre>";
    }
    
    /**
     * 
     * @method get
     * @route /extensions/widgets/[*:shortname]/install
     */
    public function installWidgetAction()
    {
        $router = $this->di->get('router');
        $config = $this->di->get('config');
        $centreonPath = $config->get('global', 'centreon_path');
        $params = $this->getParams();
        
        $commonName = str_replace(' ', '', ucwords(str_replace('-', ' ', $params['shortname'])));
        $dir = glob(rtrim($centreonPath, '/') . "/widgets/" . $commonName . "Widget/");
        if (!isset($dir[0])) {
            $dir = glob(rtrim($centreonPath, '/') . "/modules/*Module/widgets/" . $commonName . "Widget/");
        }
        if (!isset($dir[0])) {
            throw new Exception("Could not find widget directory");
        }
        $jsonFile = $dir[0] . 'install/config.json';
        
        
        preg_match('/\/([a-zA-Z]+Module)\//', $dir[0], $matches);
        $moduleRawName = trim($matches[0], '/');
        $moduleName = str_replace('Module', '', $moduleRawName);
        preg_match_all('/[A-Z]?[a-z]+/', $moduleName, $myMatches);
        $moduleShortName = strtolower(implode('-', $myMatches[0]));
        
        if (!file_exists(realpath($jsonFile))) {
            throw new Exception("The widget is not valid because of a missing configuration file");
        }
        try {
            WidgetRepository::install($jsonFile, $moduleShortName);
        } catch (\Exception $e) {
            throw new Exception("Could not install widget. Error: " . $e->getMessage(), 0, $e);
        }
        
        $backUrl = $router->getPathFor('/centreon-customview/extensions/widgets');
        $router->response()->redirect($backUrl, 200);
    }
    
    /**
     * 
     * @method get
     * @route /extensions/widgets/[i:id]/uninstall
     */
    public function uninstallWidgetAction()
    {
        $router = $this->di->get('router');
        $params = $this->getParams();
        
        WidgetRepository::uninstall($params['id']);

        $backUrl = $router->getPathFor('/centreon-customview/extensions/widgets');
        $router->response()->redirect($backUrl, 200);
    }
    
    /**
     * 
     * @method get
     * @route /extensions/widgets/[i:id]/enable
     */
    public function enableModuleAction()
    {
        $router = $this->di->get('router');
        
        $params = $this->getParams();
        WidgetModel::update($params['id'], array('isactivated' => '1'));
        $backUrl = $router->getPathFor('/centreon-customview/extensions/widgets');
        $router->response()->redirect($backUrl, 200);
    }
    
    /**
     * 
     * @method get
     * @route /extensions/widgets/[i:id]/disable
     */
    public function disableModuleAction()
    {
        $router = $this->di->get('router');
        
        $params = $this->getParams();
        WidgetModel::update($params['id'], array('isactivated' => '0'));
        $backUrl = $router->getPathFor('/centreon-customview/extensions/widgets');
        $router->response()->redirect($backUrl, 200);
    }
    
    /**
     * 
     * @method get
     * @route /extensions/widgets/list
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
            ->addCss('dataTables.fixedHeader.min.css')
            ->addCss('dataTables.bootstrap.css')
            ->addCss('select2.css')
            ->addCss('select2-bootstrap.css')
            ->addCss('centreon-wizard.css');

        /* Load JsFile */
        $this->tpl->addJs('jquery.dataTables.min.js')
            ->addJs('dataTables.tableTools.min.js')
            ->addJs('dataTables.colVis.min.js')
            ->addJs('dataTables.colReorder.min.js')
            ->addJs('dataTables.fixedHeader.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js')
            ->addJs('dataTables.bootstrap.js')
            ->addJs('jquery.select2/select2.min.js')
            ->addJs('jquery.validation/jquery.validate.min.js')
            ->addJs('jquery.validation/additional-methods.min.js')
            ->addJs('centreon-wizard.js');
        
        $this->tpl->assign('datatableObject', $this->datatableObject);
    }
}
