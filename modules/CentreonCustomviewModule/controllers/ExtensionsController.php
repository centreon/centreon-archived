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
 *
 */

namespace CentreonCustomview\Controllers;

class ExtensionsController extends \Centreon\Internal\Controller
{
    public static $objectName = 'WidgetModel';
    public static $objectDisplayName = 'WidgetModel';
    public static $moduleName = 'CentreonCustomview';
    private $di;
    private $tpl;

    /**
     * 
     * @method get
     * @route /administration/extensions/widgets
     */
    public function widgetAction()
    {
        $this->init();
        
        /* Display variable */
        $this->tpl->assign('objectName', self::$objectDisplayName);
        $this->tpl->assign('moduleName', self::$moduleName);
        $this->tpl->assign('objectListUrl', '/administration/extensions/widgets/list');
        $this->tpl->display('administration/module.tpl');
    }
    
    /**
     * 
     * @method get
     * @route /administration/extensions/widgets/[i:id]
     */
    public function displayWidgetAction()
    {
        $params = $this->getParams();
        $widget = \Centreon\Models\WidgetModel::get($params['id']);
        echo "<pre>"; var_dump($widget); echo "<pre>";
    }
    
    /**
     * 
     * @method get
     * @route /administration/extensions/widgets/[*:shortname]/install
     */
    public function installWidgetAction()
    {
        $router = $this->di->get('router');
        $config = $this->di->get('config');
        $centreonPath = $config->get('global', 'centreon_path');
        $params = $this->getParams();
        
        $commonName = str_replace(' ', '', ucwords(str_replace('-', ' ', $params['shortname'])));
        $widgetDirectory = $centreonPath . 'widgets/' . $commonName . '/';

        $jsonFile = $widgetDirectory . 'install/config.json';
        if (!file_exists(realpath($jsonFile))) {
            throw new \Exception("The widget is not valid because of a missing configuration file");
        }
        \CentreonCustomview\Repository\WidgetRepository::install($jsonFile);
        
        $backUrl = $router->getPathFor('/administration/extensions/widgets');
        $router->response()->redirect($backUrl, 200);
    }
    
    /**
     * 
     * @method get
     * @route /administration/extensions/widgets/[i:id]/uninstall
     */
    public function uninstallWidgetAction()
    {
        $router = $this->di->get('router');
        $params = $this->getParams();
        
        \CentreonCustomview\Repository\WidgetRepository::uninstall($params['id']);

        $backUrl = $router->getPathFor('/administration/extensions/widgets');
        $router->response()->redirect($backUrl, 200);
    }
    
    /**
     * 
     * @method get
     * @route /administration/extensions/widgets/[i:id]/enable
     */
    public function enableModuleAction()
    {
        $router = $this->di->get('router');
        
        $params = $this->getParams();
        \Centreon\Models\WidgetModel::update($params['id'], array('isactivated' => '1'));
        $backUrl = $router->getPathFor('/administration/extensions/widgets');
        $router->response()->redirect($backUrl, 200);
    }
    
    /**
     * 
     * @method get
     * @route /administration/extensions/widgets/[i:id]/disable
     */
    public function disableModuleAction()
    {
        $router = $this->di->get('router');
        
        $params = $this->getParams();
        \Centreon\Models\WidgetModel::update($params['id'], array('isactivated' => '0'));
        $backUrl = $router->getPathFor('/administration/extensions/widgets');
        $router->response()->redirect($backUrl, 200);
    }
    
    /**
     * 
     * @method get
     * @route /administration/extensions/widgets/list
     */
    public function datatableAction()
    {
        $router = $this->di->get('router');
        
        $router->response()->json(
            \Centreon\Internal\Datatable::getDatas(
                self::$moduleName,
                self::$objectName,
                $this->getParams('get')
            )
        );
    }
    
    /**
     * Initialize page
     *
     */
    protected function init()
    {
        $this->di = \Centreon\Internal\Di::getDefault();
        /* Init template */
        $this->tpl = $this->di->get('template');
        
        /* Load CssFile */
        $this->tpl->addCss('dataTables.css')
            ->addCss('dataTables.bootstrap.css')
            ->addCss('dataTables-TableTools.css')
            ->addCss('select2.css')
            ->addCss('select2-bootstrap.css')
            ->addCss('centreon-wizard.css');

        /* Load JsFile */
        $this->tpl->addJs('jquery.dataTables.min.js')
            ->addJs('jquery.dataTables.TableTools.min.js')
            ->addJs('bootstrap-dataTables-paging.js')
            ->addJs('jquery.dataTables.columnFilter.js')
            ->addJs('jquery.select2/select2.min.js')
            ->addJs('jquery.validate.min.js')
            ->addJs('additional-methods.min.js')
            ->addJs('centreon-wizard.js');

        /* Set Cookie */
        $token = \Centreon\Internal\Form::getSecurityToken();
        setcookie("ajaxToken", $token, time()+15, '/');
    }
}
