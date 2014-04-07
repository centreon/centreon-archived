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
namespace Controllers;

use \Centreon\Core\Form\Wizard,
    \Centreon\Repository\CustomviewRepository,
    \Centreon\Repository\WidgetRepository;

/**
 * Custom view controller
 * @authors Sylvestre Ho
 * @package Centreon
 * @subpackage Controllers
 */
class CustomviewController extends \Centreon\Core\Controller
{
    /**
     * Action for custom view
     *
     * @method get
     * @route /customview
     */
    public function customviewAction()
    {
        $template = \Centreon\Core\Di::getDefault()->get('template');
        $template->addCss('jquery.gridster.min.css')
            ->addCss('centreon-widget.css')
            ->addCss('centreon-wizard.css');
        $template->addJs('jquery.gridster.min.js')
            ->addJs('jquery.gridster.with-extras.min.js')
            ->addJs('centreon-wizard.js')
            ->addJs('bootbox.min.js');
        $currentView = 1;
        $customViews = CustomviewRepository::getCustomViews(1);
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
                '.$this->getJsAddView().'
                '.$this->getJsViewSettings().'
                '.$this->getJsDeleteView().'
                '.$this->getJsDefault().'
                '.$this->getJsBookmark().'
                '.$this->getJsRemoveWidget().'
            });';
        $template->addCustomJs($gridJs);
        $template->display('home/customview.tpl');
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
     * Add a new view
     *
     * @method post
     * @route /customview/addview
     */
    public function addViewAction()
    {

    }

    /**
     * Remove view
     *
     * @method post
     * @route /customview/removeview
     */
    public function removeViewAction()
    {

    }

    /**
     * Bookmark view
     *
     * @method post
     * @route /customview/bookmarkview
     */
    public function bookmarkViewAction()
    {

    }

    /**
     * Update view
     *
     * @method post
     * @route /customview/updateview/
     */
    public function updateViewAction()
    {

    }

    /**
     * Display view preference window
     * 
     * @method get
     * @route /customview/updateview/[i:id]?
     */
    public function displayViewPreferenceAction()
    {
        $template = \Centreon\Core\Di::getDefault()->get('template');
        $form = new Wizard('/customview/updateview', array('id' => 0));
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
                                className: "btn-default",
                                callback: function() {
                                    console.log("cancelled");
                                }
                            },
                            confirm: {
                                label: "Delete",
                                className: "btn-danger",
                                callback: function() {
                                    console.log("confirmed")
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
                            confirm: {
                                label: "Bookmark",
                                className: "btn-success",
                                callback: function() {
                                    console.log("confirmed")
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
                                className: "btn-default",
                                callback: function() {
                                    console.log("cancelled");
                                }
                            },
                            confirm: {
                                label: "Set as default",
                                className: "btn-success",
                                callback: function() {
                                    console.log("confirmed")
                                }
                            }
                        }
                    });
                });';
    }

    /**
     * Get js code for view add
     *
     * @return string
     */
    protected function getJsAddView()
    {
        return '$("#view_add").click(function() {
                    $("#modal").modal({
                        "remote": "/customview/updateview"
                    });
                });';
    }

    /**
     * Get js code for view settings
     *
     * @return string
     */
    protected function getJsViewSettings()
    {
        return '$("#view_settings").click(function() {
                    $("#modal").modal({
                        "remote": "/customview/updateview/1"
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
     *
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
     *
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
