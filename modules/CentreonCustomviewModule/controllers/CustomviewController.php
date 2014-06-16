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
     * @var int $currentView
     */
    protected $currentView;

    /**
     * @var string $baseUrl
     */
    protected $baseUrl;

    /**
     * Init
     */
    protected function init()
    {
        parent::init();
        $this->user = $_SESSION['user'];
        $this->baseUrl = rtrim(\Centreon\Internal\Di::getDefault()->get('config')->get('global', 'base_url'), '/');
    }

    /**
     * Action for custom view
     *
     * @method get
     * @route /customview/[i:id]?
     */
    public function customviewAction()
    {
        if (isset($_SESSION['customview_filters'])) {
            unset($_SESSION['customview_filters']);
        }
        $this->currentView = CustomviewRepository::getCurrentView($this->user->getId(), $this->getParams());
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
        $customViews = CustomviewRepository::getCustomViewsOfUser($this->user->getId());
        $jsonPosition = "[]";
        if (isset($customViews[$this->currentView]) && $customViews[$this->currentView]['position']) {
            $jsonPosition = $customViews[$this->currentView]['position'];
        }
        $widgets = WidgetRepository::getWidgetsFromViewId($this->currentView);
        $jsonWidgets = "[]";
        if (is_array($widgets)) {
            $jsonWidgets = json_encode($widgets);
        }
        $gridJs = '
            $(function() {
                '.$this->getJsFunctionSavePos().'
                '.$this->getJsFunctionRemoveWidget().'
                '.$this->getJsInitGrid($jsonPosition, $jsonWidgets).'
                '.$this->getJsEditView("#view_add", "$this->baseUrl/customview/updateview").'
                '.$this->getJsEditView("#view_settings", "$this->baseUrl/customview/updateview/{$this->currentView}").'
                '.$this->getJsDeleteView().'
                '.$this->getJsDefault().'
                '.$this->getJsBookmark().'
                '.$this->getJsWidgetList().'
                '.$this->getJsRemoveWidget().'
                '.$this->getJsWidgetSettings().'
            });';
        $template->addCustomJs($gridJs);
        $filters = CustomviewRepository::getViewFilters($this->currentView);
        $options = '<option value=""></option>';
        foreach ($filters as $k => $v) {
            $options .= sprintf('<option value="%s">%s</option>', $k, $v);
        }
        $filterHtml = '
<div class="filter-div col-md-2"> 
    <div class="remove-filter fa fa-times-circle"></div> 
    <div> 
        <select class="filter-name form-control input-sm"> 
            '.$options.'
        </select> 
    </div> 
    <div>
        <select class="filter-cmp form-control input-sm">
            <option value="'.CustomviewRepository::EQUAL.'">equal</option>
            <option value="'.CustomviewRepository::NOT_EQUAL.'">not equal</option>
            <option value="'.CustomviewRepository::CONTAINS.'">contains</option>
            <option value="'.CustomviewRepository::NOT_CONTAINS.'">not contains</option>
            <option value="'.CustomviewRepository::GREATER.'">greater than</option>
            <option value="'.CustomviewRepository::GREATER_EQUAL.'">greater or equal</option>
            <option value="'.CustomviewRepository::LESSER.'">lesser than</option>
            <option value="'.CustomviewRepository::LESSER_EQUAL.'">lesser or equal</option>
        </select>
    </div>
    <div> 
        <input type="text" class="filter-value form-control input-sm"></input> 
    </div> 
</div>';
        $template->assign('filterHtml', $filterHtml);
        $template->assign('filterHtmlForJs', str_replace(array("\r", "\n"), "", $filterHtml));
        $template->display('file:[CentreonCustomviewModule]customview.tpl');
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
     * Update widget settings
     *
     * @method post
     * @route /customview/updatewidgetsettings
     */
    public function updatePreferencesAction()
    {
        $params = $this->getParams('post');
        WidgetRepository::updateWidgetPreferences($params, $this->user->getId()); 
        \Centreon\Internal\Di::getDefault()
            ->get('router')
            ->response()
            ->json(array('success' => true));
    }

    /**
     * Display widget preference widget settings
     *
     * @method get
     * @route /customview/widgetsettings/[i:id]
     */
    public function displayWidgetPreferenceAction()
    {
        $params = $this->getParams('named');
        $widgetId = $params['id'];
        $template = \Centreon\Internal\Di::getDefault()->get('template');
        $template->assign('validateUrl', '/customview/updatewidgetsettings');
        $form = new \Centreon\Internal\Form\Widget($widgetId, array('id' => $widgetId));
        $title = _('Settings for widget');
        $form->addHiddenComponent('widget_id', $widgetId);
        $template->assign('modalTitle', $title);
        echo str_replace(
            array('alertMessage', 'alertClose'),
            array('alertModalMessage', 'alertModalClose'),
            $form->generate()
        );
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
                'text' => $info['name']
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
        $template->assign('formRedirect', '/customview/'.$params['view_id']);
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
        $givenParameters = $this->getParams('post');
        WidgetRepository::deleteWidgetFromView($givenParameters, $this->user->getId());
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
        CustomviewRepository::delete($givenParameters['view_id'], $this->user->getId());
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
        CustomviewRepository::bookmark($givenParameters['view_id'], $this->user->getId());
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
        CustomviewRepository::unbookmark($givenParameters['view_id'], $this->user->getId());
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
        CustomviewRepository::setDefault($givenParameters['view_id'], $this->user->getId());
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
        foreach ($givenParameters as $k => $v) {
            $params[$k] = $v;
        }
        if (!isset($params['custom_view_id'])) {
            CustomviewRepository::insert($params, $this->user->getId());
        } else {
            CustomviewRepository::update($params, $this->user->getId());
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
     * Apply global filters
     *
     * @method POST
     * @route /customview/applyfilters
     */
    public function applyFiltersAction()
    {
        $_SESSION['customview_filters'] = array();
        $params = $this->getParams('post');
        if (isset($params['filterNames']) && isset($params['filterValues'])) {
            $filterNames = json_decode($params['filterNames']);
            $filterValues = json_decode($params['filterValues']);
            $filterCmp = json_decode($params['filterCmp']);
            foreach($filterNames as $index => $name) {
                if ($name != "" && isset($filterValues[$index])) {
                    $_SESSION['customview_filters'][$name] = CustomviewRepository::getCmpString(
                        $filterCmp[$index], 
                        $filterValues[$index]
                    );
                }
            }
        }
        \Centreon\Internal\Di::getDefault()
            ->get('router')
            ->response()
            ->json(array('success' => true));
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
                                        url: "'.$this->baseUrl.'/customview/removeview",
                                        data: { view_id: '.$this->currentView.' }
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
                                    removeWidget($(li).data("widget-id"));
                                }
                            }
                        }
                    });
                });';

    }

    /**
     * Get js code for widget settings
     *
     * @return string
     */
    protected function getJsWidgetSettings()
    {
        return '$(".widget-settings").click(function() {
                    var li = $(this).parents().closest("li"); 

                    $("#modal").removeData("bs.modal");
                    $("#modal").removeData("centreonWizard");
                    $("#modal .modal-content").text("");
                    $("#modal").one("loaded.bs.modal", function(e) {
                        $(this).centreonWizard();
                    });
                    $("#modal").modal({
                        "remote": "'.$this->baseUrl.'/customview/widgetsettings/" + $(li).data("widget-id")
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
                                        url: "'.$this->baseUrl.'/customview/unbookmarkview",
                                        data: { view_id: '.$this->currentView.' }
                                    });
                                }
                            },
                            confirm: {
                                label: "Bookmark",
                                className: "btn-success",
                                callback: function() {
                                    $.ajax({
                                        type: "POST",
                                        url: "'.$this->baseUrl.'/customview/bookmarkview",
                                        data: { view_id: '.$this->currentView.' }
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
                                        url: "'.$this->baseUrl.'/customview/setdefaultview",
                                        data: { view_id: '.$this->currentView.' }
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
                        "remote": "'.$this->baseUrl.'/customview/widgetlist/'.$this->currentView.'"
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
     * @param string $jsonPosition
     * @param string $jsonWidgets
     * @return string
     */
    protected function getJsInitGrid($jsonPosition, $jsonWidgets)
    {
        return 'var jsonPosition = '.$jsonPosition.'
                var widgets = '.$jsonWidgets.'
                var w = Math.round($(window).width() / 8);
                var h = 140;
        
                var gridster = $(".gridster ul").gridster({
                    max_cols: 6,
                    widget_margins: [3, 3],
                    widget_base_dimensions: [w, h],
                    draggable: {
                        handle: ".portlet-header",
                        stop: function() { 
                            $(".overlay_fix").hide();
                            savepos(); 
                        },
                        start: function() {
                            $(".overlay_fix").show();
                        }
                    },
                    resize: {
                        enabled: true,
                        stop: function() { 
                            $(".overlay_fix").hide();
                            savepos(); 
                        },
                        start: function() {
                            $(".overlay_fix").show();
                        }
                    }
                }).data("gridster");

                gridster.remove_all_widgets();
                $.each(widgets, function(index) {
                    gridster.add_widget(
                        \'<li class="widget-li" data-index=\'+index+\' data-widget-id=\'+this.widget_id+\' > \
                        <div class="overlay_fix"></div> \
                        <div class="portlet-header bg-primary"> \
                        <span class="widgetTitle"> \
                        <span>\'+this.title+\'</span> \
                        <span class="portlet-ui-icon"> \
                        <i class="fa fa-chain"></i> \
                        <i class="fa fa-refresh"></i> \
                        <i class="fa fa-gears widget-settings"></i> \
                        <i class="fa fa-trash-o widget-delete"></i> \
                        </span> \
                        </span> \
                        </div> \
                        <iframe class="portlet-content" src="'.$this->baseUrl.'/widget/\' + this.widget_id + \'" \
                                width="100%" height="100%" frameborder="0" style="overflow:hidden;"></iframe> \
                        </li>\',
                        (typeof jsonPosition[index] !== \'undefined\') ? jsonPosition[index].size_x : 5,
                        (typeof jsonPosition[index] !== \'undefined\') ? jsonPosition[index].size_y : 2,
                        (typeof jsonPosition[index] !== \'undefined\') ? jsonPosition[index].col : 1,
                        (typeof jsonPosition[index] !== \'undefined\') ? jsonPosition[index].row : 1
                    );
                });

                $(".widget-li").mouseenter(function() {
                    $(this).children(".portlet-header").show();
                }).mouseleave(function() {
                    $(this).children(".portlet-header").hide();
                })

                ';
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
                        url: "'.$this->baseUrl.'/customview/saveposition",
                        data: { pos: gridster.serialize(), view_id: '.$this->currentView.' }
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
                        url: "'.$this->baseUrl.'/customview/removewidget",
                        data: {
                            widget_id: widgetId
                        }
                    });
                }';
    }
}
