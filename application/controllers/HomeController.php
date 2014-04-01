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

use \Centreon\Core\Form\Wizard;

/**
 * Home controller
 * @authors Sylvestre Ho
 * @package Centreon
 * @subpackage Controllers
 */
class HomeController extends \Centreon\Core\Controller
{
    /**
     * Action for home page
     *
     * @method get
     * @route /home
     */
    public function homeAction()
    {
        $template = \Centreon\Core\Di::getDefault()->get('template');
        $template->addCss('jquery.gridster.min.css');
        $template->addCss('centreon-widget.css');
        $template->addJs('jquery.gridster.min.js');
        $template->addJs('jquery.gridster.with-extras.min.js');
        $gridJs = '
            $(function() {
                function savepos() {
                    $.ajax({
                        type: "POST",
                        url: "/home/saveposition",
                        data: { pos: gridster.serialize() }
                    });
                }

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

                $("#view_add").click(function() {
                    $("#modal").modal({
                        "remote": "/home/displayviewpreference"
                    });
                });
            });';
        $template->addCustomJs($gridJs);
        $widgets = array();
        $widgets[] = true;
        $widgets[] = true;
        $widgets[] = true;
        $template->assign('widgets', $widgets);
        $template->display('home/home.tpl');
    }

    /**
     * Save position
     *
     * @method post
     * @route /home/saveposition
     */
    public function savePositionAction()
    {
        $params = $this->getParams('post');
        print_r($params);
    }

    /**
     * Update preference
     *
     * @method post
     * @route /home/updatewidgetpreference
     */
    public function updatePreferencesAction()
    {

    }

    /**
     * Display widget preference window
     *
     * @method get
     * @route /home/displaywidgetpreference
     */
    public function displayWidgetPreferenceAction()
    {

    }

    /**
     * Add a new widget
     *
     * @method post
     * @route /home/addwidget
     */
    public function addWidgetAction()
    {

    }

    /**
     * Remove widget
     *
     * @method post
     * @route /home/removewidget
     */
    public function removeWidgetAction()
    {

    }

    /**
     * Add a new view
     *
     * @method post
     * @route /home/addview
     */
    public function addViewAction()
    {

    }

    /**
     * Remove view
     *
     * @method post
     * @route /home/removeview
     */
    public function removeViewAction()
    {

    }

    /**
     * Update view
     *
     * @method post
     * @route /home/updateview
     */
    public function updateViewAction()
    {

    }

    /**
     * Display view preference window
     * 
     * @method get
     * @route /home/displayviewpreference
     */
    public function displayViewPreferenceAction()
    {
        $template = \Centreon\Core\Di::getDefault()->get('template');
        $form = new Wizard('/home/updateview', 0, array('id' => 0));
        echo $form->generate();
//        $template->display('home/viewpreferences.tpl');
    }
}
