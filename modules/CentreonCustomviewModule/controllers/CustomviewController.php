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
namespace CentreonCustomview\Controllers;

use \Centreon\Internal\Form\Wizard,
    \CentreonCustomview\Repository\CustomviewRepository,
    \CentreonCustomview\Repository\WidgetRepository;

/**
 * Custom view controller
 * @authors Sylvestre Ho
 * @package Centreon
 * @subpackage Controllers
 */
class CustomviewController extends \Centreon\Internal\Controller
{
    /**
     * Action for custom view
     *
     * @method get
     * @route /customview
     */
    public function customviewAction()
    {
        $template = \Centreon\Internal\Di::getDefault()->get('template');
        $template->addCss('jquery.gridster.min.css')
            ->addCss('centreon-widget.css')
            ->addCss('centreon-wizard.css')
            ->addCss('select2.css')
            ->addCss('select2-bootstrap.css');
        $template->addJs('jquery.gridster.min.js')
            ->addJs('jquery.gridster.with-extras.min.js')
            ->addJs('centreon-wizard.js')
            ->addJs('bootbox.min.js')
            ->addJs('jquery.select2/select2.min.js');
        $currentView = 1;
        $user = $_SESSION['user'];
        $customViews = CustomviewRepository::getCustomViewsOfUser($user->getId());
        $jsonPosition = "[]";
        if (isset($customViews[$currentView])) {
            $jsonPosition = $customViews[$currentView]['position'];
        }
        $widgets = WidgetRepository::getWidgetsFromViewId($currentView);
        $jsonWidgets = "[]";
        if (is_array($widgets)) {
            $jsonWidgets = json_encode($widgets);
        }
        $gridJs = '
            $(function() {
                '.$this->getJsFunctionSavePos().'
                '.$this->getJsFunctionRemoveWidget().'
                '.$this->getJsInitGrid($jsonPosition, $jsonWidgets).'
                '.$this->getJsEditView("#view_add", "/customview/updateview").'
                '.$this->getJsEditView("#view_settings", "/customview/updateview/1").'
                '.$this->getJsDeleteView().'
                '.$this->getJsDefault().'
                '.$this->getJsBookmark().'
                '.$this->getJsWidgetList().'
                '.$this->getJsRemoveWidget().'
            });';
        $template->addCustomJs($gridJs);
        $template->display('file:[CentreonCustomview]customview.tpl');
    }

    /**
     * Save position
     *
     * @method post
     * @route /customview/saveposition
     */
    public function savePositionAction()
    {
        $params = $this->getParams('post');
        CustomviewRepository::updatePosition($params['view_id'], json_encode($params['pos']));
    }

    /**
     * Update preference
     *
     * @method post
     * @route /customview/updatewidgetpreference
     */
    public function updatePreferencesAction()
    {
    
    }

    /**
     * Display widget preference window
     *
     * @method get
     * @route /customview/displaywidgetpreference
     */
    public function displayWidgetPreferenceAction()
    {

    }

    /**
     * Add a new widget
     *
     * @method post
     * @route /customview/addwidget
     */
    public function addWidgetAction()
    {
        $givenParameters = $this->getParams('post');
        $params = array();
        $user = $_SESSION['user'];
        foreach ($givenParameters as $k => $v) {
            $params[$k] = $v;
        }
        WidgetRepository::addWidget($params);
        $router = \Centreon\Internal\Di::getDefault()->get('router');
        $router->response()->json(array('success' => true));
    }

    /**
     * Return a list of widget for selectbox
     *
     * @method get
     * @route /customview/widgetformlist
     */
    public function widgetformlistAction()
    {
        $widgets = WidgetRepository::getWidgetInfo();
        $list = array();
        foreach ($widgets as $id => $info) {
            $list[] = array(
                'id' => $id,
                'text' => $info['title']
            );
        }
        \Centreon\Internal\Di::getDefault()
            ->get('router')
            ->response()
            ->json($list);
    }

    /**
     * Display list of widgets
     *
     * @method get
     * @route /customview/widgetlist/[i:view_id]
     */
    public function widgetListAction()
    {
        $template = \Centreon\Internal\Di::getDefault()->get('template');
        $template->assign('validateUrl', '/customview/addwidget');
        $template->assign('modalTitle', _('Add a new widget'));
        $widgets = json_encode(WidgetRepository::getWidgetInfo());
        $params = $this->getParams('named');
        $form = new Wizard('/customview/addwidget', array('id' => 0));
        $form->addHiddenComponent('custom_view_id', $params['view_id']);
        $template->addCustomJs('
            var widgets = '.$widgets.';

            $("#widget").change(function() {
                $("div#widget_info").remove();
                $("div.active").append($("<div>", { id: "widget_info" }));
                
                $("div#widget_info").append($("<div>", { id: "widget-desc", class: "form-group" }));
                $("div#widget-desc").append("<div class=\"col-sm-3\" style=\"text-align: right\"><label>Description</label></div>");
                $("div#widget-desc").append("<div class=\"col-sm-8\">" + widgets[$(this).val()].description + "</div>");

                $("div#widget_info").append($("<div>", { id: "widget-vers", class: "form-group" }));
                $("div#widget-vers").append("<div class=\"col-sm-3\" style=\"text-align: right\"><label>Version</label></div>");
                $("div#widget-vers").append("<div class=\"col-sm-8\">" + widgets[$(this).val()].version + "</div>");

                $("div#widget_info").append($("<div>", { id: "widget-auth", class: "form-group" }));
                $("div#widget-auth").append("<div class=\"col-sm-3\" style=\"text-align: right\"><label>Author</label></div>");
                $("div#widget-auth").append("<div class=\"col-sm-8\">" + widgets[$(this).val()].author + "</div>");
                
                $("div#widget_info").append($("<div>", { id: "widget-image", class: "form-group" }));
                $("div#widget-image").append("<div class=\"col-sm-11\" style=\"text-align: center\"><img src=\"" + widgets[$(this).val()].thumbnail  + "\"></div>");
            });
        ');
        echo str_replace(
            array('alertMessage', 'alertClose'),
            array('alertModalMessage', 'alertModalClose'),
            $form->generate()
        );
    }

    /**
     * Remove widget from view
     *
     * @method post
     * @route /customview/removewidget
     */
    public function removeWidgetAction()
    {
        WidgetRepository::deleteWidgetFromView($params);
    }

    /**
     * Remove view
     *
     * @method post
     * @route /customview/removeview
     */
    public function removeViewAction()
    {
        $givenParameters = $this->getParams('post');
        $user = $_SESSION['user'];
        CustomviewRepository::delete($givenParameters['view_id'], $user->getId());
    }

    /**
     * Bookmark view
     *
     * @method post
     * @route /customview/bookmarkview
     */
    public function bookmarkViewAction()
    {
        $givenParameters = $this->getParams('post');
        $user = $_SESSION['user'];
        CustomviewRepository::bookmark($givenParameters['view_id'], $user->getId());
    }

    /**
     * Unbookmark view
     *
     * @method post
     * @route /customview/unbookmarkview
     */
    public function unbookmarkViewAction()
    {
        $givenParameters = $this->getParams('post');
        $user = $_SESSION['user'];
        CustomviewRepository::unbookmark($givenParameters['view_id'], $user->getId());
    }

    /**
     * Set view as default
     *
     * @method post
     * @route /customview/setdefaultview
     */
    public function setDefaultViewAction()
    {
        $givenParameters = $this->getParams('post');
        $user = $_SESSION['user'];
        CustomviewRepository::setDefault($givenParameters['view_id'], $user->getId());
    }

    /**
     * Update view
     *
     * @method post
     * @route /customview/updateview
     */
    public function updateViewAction()
    {
        $givenParameters = $this->getParams('post');
        $params = array();
        $user = $_SESSION['user'];
        foreach ($givenParameters as $k => $v) {
            $params[$k] = $v;
        }
        if (!isset($params['custom_view_id'])) {
            CustomviewRepository::insert($params, $user->getId());
        } else {
            CustomviewRepository::update($params, $user->getId());
        }
        $router = \Centreon\Internal\Di::getDefault()->get('router');
        $router->response()->json(array('success' => true));
    }

    /**
     * Display view preference window
     * 
     * @method get
     * @route /customview/updateview/[i:id]?
     */
    public function displayViewPreferenceAction()
    {
        $template = \Centreon\Internal\Di::getDefault()->get('template');
        $template->assign('validateUrl', '/customview/updateview');
        $id = 0;
        $requestParam = $this->getParams('named');
        if (isset($requestParam['id']) && $requestParam['id']) {
            $id = $requestParam['id'];
        }
        $form = new Wizard('/customview/updateview', array('id' => $id));
        $title = _('Add a new view');
        if ($id) {
            $form->addHiddenComponent('custom_view_id', $id);
            $form->setDefaultValues(CustomviewRepository::getCustomViewData($id));
            $title = _('Edit view preferences');
        }
        $template->assign('modalTitle', $title);
        echo str_replace(
            array('alertMessage', 'alertClose'),
            array('alertModalMessage', 'alertModalClose'),
            $form->generate()
        );
    }

    /**
     * Get js code for view deletion
     *
     * @return string
     */
    protected function getJsDeleteView()
    {
        return '$("#view_delete").click(function() {
                    bootbox.dialog({
                        message: "Delete this view?",
                        title: "Delete view",
                        buttons: {
                            cancel: {
                                label: "Cancel",
                                className: "btn-default"
                            },
                            confirm: {
                                label: "Delete",
                                className: "btn-danger",
                                callback: function() {
                                    $.ajax({
                                        type: "POST",
                                        url: "/customview/removeview",
                                        data: { view_id: 1 }
                                    });
                                }
                            }
                        }
                    });
                });';
    }

    /**
     * Get js code for widget removal
     *
     * @return string
     */
    protected function getJsRemoveWidget()
    {
        return '$(".widget-delete").click(function() {
                    var li = $(this).parents().closest("li"); 
                    bootbox.dialog({
                        message: "Remove widget from view?",
                        title: "Remove widget",
                        buttons: {
                            cancel: {
                                label: "Cancel",
                                className: "btn-default"
                            },
                            confirm: {
                                label: "Remove",
                                className: "btn-danger",
                                callback: function() {
                                    gridster.remove_widget(li);
                                    savepos();
                                    removeWidget($(this).data("widget-id"));
                                }
                            }
                        }
                    });
                });';

    }

    /**
     * Get js code for bookmark
     *
     * @return string
     */
    protected function getJsBookmark()
    {
        return '$("#view_bookmark").click(function() {
                    bootbox.dialog({
                        message: "Bookmark this view?",
                        title: "Bookmark",
                        buttons: {
                            cancel: {
                                label: "Cancel",
                                className: "btn-default"
                            },
                            unbookmark: {
                                label: "Unbookmark",
                                className: "btn-danger",
                                callback: function() {
                                    $.ajax({
                                        type: "POST",
                                        url: "/customview/unbookmarkview",
                                        data: { view_id: 1 }
                                    });
                                }
                            },
                            confirm: {
                                label: "Bookmark",
                                className: "btn-success",
                                callback: function() {
                                    $.ajax({
                                        type: "POST",
                                        url: "/customview/bookmarkview",
                                        data: { view_id: 1 }
                                    });
                                }
                            }
                        }
                    });
                });';
    }

    /**
     * Get js code for default set
     *
     * @return string
     */
    protected function getJsDefault()
    {
        return '$("#view_default").click(function() {
                    bootbox.dialog({
                        message: "Set view as default?",
                        title: "Default view",
                        buttons: {
                            cancel: {
                                label: "Cancel",
                                className: "btn-default"
                            },
                            confirm: {
                                label: "Set as default",
                                className: "btn-success",
                                callback: function() {
                                    $.ajax({
                                        type: "POST",
                                        url: "/customview/setdefaultview",
                                        data: { view_id: 1 }
                                    });
                                }
                            }
                        }
                    });
                });';
    }

    /**
     * Get js widget list
     *
     * @return string
     */
    protected function getJsWidgetList()
    {
        return '$("#view_widget").click(function() {
                    $("#modal").removeData("bs.modal");
                    $("#modal").removeData("centreonWizard");
                    $("#modal .modal-content").text("");
                    $("#modal").one("loaded.bs.modal", function(e) {
                        $(this).centreonWizard();
                    });
                    $("#modal").modal({
                        "remote": "/customview/widgetlist/1"
                    });
                })';
    }

    /**
     * Get js code for view add
     *
     * @param string $dom
     * @param string $route
     * @return string
     */
    protected function getJsEditView($dom, $route)
    {
        return '$("'.$dom.'").click(function() {
                    $("#modal").removeData("bs.modal");
                    $("#modal").removeData("centreonWizard");
                    $("#modal .modal-content").text("");
                    $("#modal").one("loaded.bs.modal", function(e) {
                        $(this).centreonWizard();
                    });
                    $("#modal").modal({
                        "remote": "'.$route.'"
                    });
                });';
    }

    /**
     * Get js code for grid init
     *
     * @parem string $jsonPosition
     * @param string $jsonWidgets
     * @return string
     */
    protected function getJsInitGrid($jsonPosition, $jsonWidgets)
    {
        return 'var jsonPosition = '.$jsonPosition.'
                var widgets = '.$jsonWidgets.'

                var gridster = $(".gridster ul").gridster({
                    widget_margins: [10, 10],
                    widget_base_dimensions: [140, 140],
                    draggable: {
                        handle: ".portlet-header",
                        stop: function() { savepos(); }
                    },
                    resize: {
                        enabled: true,
                        stop: function() { savepos(); }
                    }
                }).data("gridster");

                gridster.remove_all_widgets();
                $.each(widgets, function(index) {
                    gridster.add_widget(
                        \'<li style="overflow:hidden;" data-index=\'+index+\' data-widget-id=\'+this.widget_id+\' > \
                        <div class="portlet-header bg-primary"> \
                        <span class="widgetTitle"> \
                        <span>\'+this.title+\'</span> \
                        <span class="portlet-ui-icon"> \
                        <i class="fa fa-refresh"></i> \
                        <i class="fa fa-gears"></i> \
                        <i class="fa fa-trash-o widget-delete"></i> \
                        </span> \
                        </span> \
                        </div> \
                        <iframe class="portlet-content" src="\'+this.url+\'" \
                                width="100%" height="100%" frameborder="0" style="overflow:hidden;"></iframe> \
                        </li>\',
                        (typeof jsonPosition[index] !== \'undefined\') ? jsonPosition[index].size_x : 5,
                        (typeof jsonPosition[index] !== \'undefined\') ? jsonPosition[index].size_y : 2,
                        (typeof jsonPosition[index] !== \'undefined\') ? jsonPosition[index].col : 1,
                        (typeof jsonPosition[index] !== \'undefined\') ? jsonPosition[index].row : 1
                    );
                });';
    }

    /**
     * Get js code for position saving
     *
     * @return string
     */
    protected function getJsFunctionSavePos() 
    {
        return 'function savepos() {
                    $.ajax({
                        type: "POST",
                        url: "/customview/saveposition",
                        data: { pos: gridster.serialize(), view_id: 1 }
                    });
                }';
    }

    /**
     * Get js code for widget removal
     *
     * @return string
     */
    protected function getJsFunctionRemoveWidget()
    {
        return 'function removeWidget(widgetId) {
                    $.ajax({
                        type: "POST",
                        url: "/customview/removewidget",
                        data: {
                            widget_id: widgetId
                        }
                    });
                }';
    }
}
