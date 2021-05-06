<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once dirname(__FILE__) . "/webService.class.php";
require_once _CENTREON_PATH_ . 'www/class/centreonCustomView.class.php';

class CentreonHomeCustomview extends CentreonWebService
{
    /**
     * CentreonHomeCustomview constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     */
    public function getListSharedViews()
    {
        global $centreon;
        $views = array();
        $q = array();
        if (isset($this->arguments['q']) && $this->arguments['q'] != '') {
            $q[] = '%' . $this->arguments['q'] . '%';
        }

        $query = 'SELECT custom_view_id, name FROM (' .
            'SELECT cv.custom_view_id, cv.name FROM custom_views cv ' .
            'INNER JOIN custom_view_user_relation cvur ON cv.custom_view_id = cvur.custom_view_id ' .
            'WHERE (cvur.user_id = ' . $centreon->user->user_id . ' ' .
            'OR cvur.usergroup_id IN ( ' .
            'SELECT contactgroup_cg_id ' .
            'FROM contactgroup_contact_relation ' .
            'WHERE contact_contact_id = ' . $centreon->user->user_id . ' ' .
            ') ' .
            ') ' .
            'UNION ' .
            'SELECT cv2.custom_view_id, cv2.name FROM custom_views cv2 ' .
            'WHERE cv2.public = 1 ) as d ' .
            'WHERE d.custom_view_id NOT IN (' .
            'SELECT cvur2.custom_view_id FROM custom_view_user_relation cvur2 ' .
            'WHERE cvur2.user_id = ' . $centreon->user->user_id . ' ' .
            'AND cvur2.is_consumed = 1) ' .
            (count($q) > 0 ? 'AND d.name like ? ' : '') .
            'ORDER BY name';

        $stmt = $this->pearDB->prepare($query);
        $stmt->execute($q);

        while ($row = $stmt->fetch()) {
            $views[] = array(
                'id' => $row['custom_view_id'],
                'text' => $row['name']
            );
        }
        return array(
            'items' => $views,
            'total' => count($views)
        );
    }

    /**
     * @return array
     * @throws RestBadRequestException
     */
    public function getLinkedUsers()
    {
        // Check for select2 'q' argument
        if (isset($this->arguments['q'])) {
            if (!is_numeric($this->arguments['q'])) {
                throw new \RestBadRequestException('Error, custom view id must be numerical');
            }
            $customViewId = $this->arguments['q'];
        } else {
            $customViewId = 0;
        }

        global $centreon;
        $viewObj = new CentreonCustomView($centreon, $this->pearDB);

        return $viewObj->getUsersFromViewId($customViewId);
    }

    /**
     * @return array
     * @throws RestBadRequestException
     */
    public function getLinkedUsergroups()
    {
        // Check for select2 'q' argument
        if (isset($this->arguments['q'])) {
            if (!is_numeric($this->arguments['q'])) {
                throw new \RestBadRequestException('Error, custom view id must be numerical');
            }
            $customViewId = $this->arguments['q'];
        } else {
            $customViewId = 0;
        }

        global $centreon;
        $viewObj = new CentreonCustomView($centreon, $this->pearDB);

        return $viewObj->getUsergroupsFromViewId($customViewId);
    }

    /**
     * Get the list of views
     *
     * @return array
     */
    public function getListViews()
    {
        global $centreon;
        $viewObj = new CentreonCustomView($centreon, $this->pearDB);

        $tabs = array();
        $tabsDb = $viewObj->getCustomViews();
        foreach ($tabsDb as $key => $tab) {
            $tabs[] = array(
                'default' => false,
                'name' => $tab['name'],
                'custom_view_id' => $tab['custom_view_id'],
                'public' => $tab['public'],
                'nbCols' => $tab['layout']
            );
        }
        return array(
            'current' => $viewObj->getCurrentView(),
            'tabs' => $tabs
        );
    }

    /**
     * Get the list of preferences
     * @return array
     * @throws Exception
     */
    public function getPreferences()
    {
        if (
            filter_var(($widgetId = $this->arguments['widgetId'] ?? false), FILTER_VALIDATE_INT) === false
            || filter_var(($viewId = $this->arguments['viewId'] ?? false), FILTER_VALIDATE_INT) === false
        ) {
            throw new \InvalidArgumentException('Bad argument format');
        }

        require_once _CENTREON_PATH_ . "www/class/centreonWidget.class.php";
        require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Boolean.class.php";
        require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Hidden.class.php";
        require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/List.class.php";
        require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Password.class.php";
        require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Range.class.php";
        require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Text.class.php";
        require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Compare.class.php";
        require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Sort.class.php";
        require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Date.class.php";
        $smartyDir = __DIR__ . '/../../../vendor/smarty/smarty/';
        require_once $smartyDir . 'libs/Smarty.class.php';

        global $centreon;

        $action = "setPreferences";

        $viewObj = new CentreonCustomView($centreon, $this->pearDB);
        $widgetObj = new CentreonWidget($centreon, $this->pearDB);
        $title = "";
        $defaultTab = array();

        $widgetTitle = $widgetObj->getWidgetTitle($widgetId);
        if ($widgetTitle != '') {
            $title = sprintf(_("Widget Preferences for %s"), $widgetTitle);
        } else {
            $title = _("Widget Preferences");
        }

        $info = $widgetObj->getWidgetDirectory($widgetObj->getWidgetType($widgetId));
        $title .= " [" . $info . "]";

        $defaultTab['custom_view_id'] = $viewId;
        $defaultTab['widget_id'] = $widgetId;
        $defaultTab['action'] = $action;
        $url = $widgetObj->getUrl($widgetId);

        /*
         * Smarty template Init
         */
        $libDir = __DIR__ . "/../../../GPL_LIB";
        $tpl = new \SmartyBC();
        $tpl->setTemplateDir(_CENTREON_PATH_ . '/www/include/home/customViews/');
        $tpl->setCompileDir($libDir . '/SmartyCache/compile');
        $tpl->setConfigDir($libDir . '/SmartyCache/config');
        $tpl->setCacheDir($libDir . '/SmartyCache/cache');
        $tpl->addPluginsDir($libDir . '/smarty-plugins');
        $tpl->loadPlugin('smarty_function_eval');
        $tpl->setForceCompile(true);
        $tpl->setAutoLiteral(false);

        $form = new HTML_QuickFormCustom('Form', 'post', "?p=103");
        $form->addElement('header', 'title', $title);
        $form->addElement('header', 'information', _("General Information"));

        /* Prepare list of installed modules and have widget connectors */
        $loadConnectorPaths = array();
        /* Add core path */
        $loadConnectorPaths[] = _CENTREON_PATH_ . "www/class/centreonWidget/Params/Connector";
        $query = 'SELECT name FROM modules_informations ORDER BY name';
        $res = $this->pearDB->query($query);
        while ($module = $res->fetchRow()) {
            $dirPath = _CENTREON_PATH_ . 'www/modules/' . $module['name'] . '/widgets/Params/Connector';
            if (is_dir($dirPath)) {
                $loadConnectorPaths[] = $dirPath;
            }
        }

        try {
            $permission = $viewObj->checkPermission($viewId);
            $params = $widgetObj->getParamsFromWidgetId($widgetId, $permission);
            foreach ($params as $paramId => $param) {
                if ($param['is_connector']) {
                    $paramClassFound = false;
                    foreach ($loadConnectorPaths as $path) {
                        $filename = $path . '/' . ucfirst($param['ft_typename'] . ".class.php");
                        if (is_file($filename)) {
                            require_once $filename;
                            $paramClassFound = true;
                            break;
                        }
                    }
                    if (false === $paramClassFound) {
                        throw new Exception('No connector found for ' . $param['ft_typename']);
                    }
                    $className = "CentreonWidgetParamsConnector" . ucfirst($param['ft_typename']);
                } else {
                    $className = "CentreonWidgetParams" . ucfirst($param['ft_typename']);
                }
                if (class_exists($className)) {
                    $currentParam = call_user_func(
                        array($className, 'factory'),
                        $this->pearDB,
                        $form,
                        $className,
                        $centreon->user->user_id
                    );
                    $param['custom_view_id'] = $viewId;
                    $param['widget_id'] = $widgetId;
                    $currentParam->init($param);
                    $currentParam->setValue($param);
                    $params[$paramId]['trigger'] = $currentParam->getTrigger();
                    $element = $currentParam->getElement();
                } else {
                    throw new Exception('No class name found');
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage() . "<br/>";
        }

        $tpl->assign('params', $params);

        /**
         * Submit button
         */
        $form->addElement(
            'button',
            'submit',
            _("Apply"),
            array("class" => "btc bt_success", "onClick" => "submitData();")
        );
        $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
        $form->addElement('hidden', 'custom_view_id');
        $form->addElement('hidden', 'widget_id');
        $form->addElement('hidden', 'action');
        $form->setDefaults($defaultTab);


        /*
         * Apply a template definition
         */
        $renderer = new HTML_QuickForm_Renderer_ArraySmarty($template, true);
        $renderer->setRequiredTemplate('{$label}&nbsp;<i class="red">*</i>');
        $renderer->setErrorTemplate('<i class="red">{$error}</i><br />{$html}');
        $form->accept($renderer);
        $tpl->assign('form', $renderer->toArray());
        $tpl->assign('viewId', $viewId);
        $tpl->assign('widgetId', $widgetId);
        $tpl->assign('url', $url);

        return $tpl->fetch("widgetParam.html");
    }

    /**
     * Get preferences by widget id
     *
     * @return array The widget preferences
     * @throws \Exception When missing argument
     */
    public function getPreferencesByWidgetId()
    {
        global $centreon;

        if (!isset($this->arguments['widgetId'])) {
            throw new \Exception('Missing argument : widgetId');
        }
        $widgetId = $this->arguments['widgetId'];
        $widgetObj = new CentreonWidget($centreon, $this->pearDB);

        return $widgetObj->getWidgetPreferences($widgetId);
    }

    /**
     * Authorize to access to the action
     *
     * @param string $action The action name
     * @param array $user The current user
     * @param boolean $isInternal If the api is call in internal
     * @return boolean If the user has access to the action
     */
    public function authorize($action, $user, $isInternal = false)
    {
        if (
            parent::authorize($action, $user, $isInternal)
            || ($user && $user->hasAccessRestApiConfiguration())
        ) {
            return true;
        }

        return false;
    }
}
