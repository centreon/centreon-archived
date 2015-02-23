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
namespace Centreon\Internal\Form\Custom;

use CentreonPerformance\Repository\GraphTemplate;

/**
 * Astract for custom form for graph
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @package Centreon
 * @subpackage Core
 */
abstract class Customcurvegraph extends Customobject
{
    /**
     * @var string The template name for render input
     */
    protected static $templateName = null;

    /**
     * Render the html input
     *
     * @param array $element The element to render
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        file_put_contents('/tmp/debug', var_export($element, true));
        $di = \Centreon\Internal\Di::getDefault();
        /* Add javascript file for clone */
        $tpl = $di->get('template');
        $tpl->addJs('centreon-clone.js')
            ->addJs('spectrum.js');
        $tpl->addCss('spectrum.css');
        $router = $di->get('router');

        $metricsUrl = $router->getPathFor('/centreon-performance/configuration/graphtemplate/listMetrics');

        $javascript = '$(function() {
  $(".clonable").centreonClone({
    events: {
      add: [
        function(element) {
          element.find(".color-picker").spectrum({
            showInput: true,
            allowEmpty: true,
            preferredFormat: "hex"
          });
        }
      ]
    }
  });

  $(".cloned_element .color-picker").spectrum({
    showInput: true,
    allowEmpty: true,
    preferredFormat: "hex"
  });

  $("body").on("click", ".btn-toggle", function() {
    $(this).find(".btn").toggleClass("active");
    if ($(this).find(".btn-primary").size()>0) {
      $(this).find(".btn").toggleClass("btn-primary");
    }
    $(this).find(".btn").toggleClass("btn-default");
  });
    
  $("#load_metrics").on("click", function() {
    var tmplId = $("#service_template_id").val();
    if (tmplId == "") {
      return;
    }
    $.ajax({
      url: "' . $metricsUrl . '",
      data: {svc_tmpl_id: tmplId},
      dataType: "json",
      type: "post",
      success: function(data, statusText, jqXHR) {
        var listMetrics = [];
        if (data.success) {
          $("input[name=\'metric_id[]\']").each(function(idx, el) {
            listMetrics.push($(el).val());
          });
          $.each(data.data, function(idx, metric) {
            if (-1 == $.inArray(metric, listMetrics)) {
              $(".clonable").centreonClone("addElement", {
                "input[name=\'metric_id[]\']": metric
              });
            }
          });
        }
      }
    });
  });
});';
        /* Load default values */
        if (isset($element['label_extra']) && isset($element['label_extra']['id'])) {
            $graphTmplId = $element['label_extra']['id'];
            $listMetrics = GraphTemplate::getMetrics($graphTmplId);
            $tpl->assign('metrics', $listMetrics);
        }
        $tpl->assign('element', $element);
        return array(
            'html' => $tpl->fetch(static::$templateName),
            'js' => $javascript
        );
    }
}
