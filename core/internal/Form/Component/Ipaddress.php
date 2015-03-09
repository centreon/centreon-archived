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
 */

namespace Centreon\Internal\Form\Component;

use Centreon\Internal\Di;

class Ipaddress extends Component
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        (isset($element['html']) ? $value = 'value="'.$element['html'].'" ' :  $value = '');
        
        if (!isset($element['placeholder']) || (isset($element['placeholder']) && empty($element['placeholder']))) {
            $placeholder = 'placeholder="'.$element['name'].'" ';
        }
        
        if (!isset($element['id']) || (isset($element['id']) && empty($element['id']))) {
            $element['id'] = $element['name'];
        }
        
        $addClass = '';
        $required = '';
        if (isset($element['label_mandatory']) && $element['label_mandatory'] == "1") {
            $addClass .= 'mandatory-field ';
            $required .= ' required';
        }
        
        $myJs = "";
        
        $myHtml = '<div id="'.$element['name'].'_ipaddress" class="input-group input-group-sm">';
        
        $myHtml .= '<input '.
                'id="'.$element['id'].'" '.
                'type="text" '.
                'name="'.$element['name'].'" '.
                $value.
                'class="form-control '.$addClass.'" '.
                $placeholder.
                '/>';
                    
        $myHtml .= '<span id="'.$element['name'].'_ipaddress_span" class=""></span>';
        
        $myHtml .= '<span id="'.$element['name'].'_resolve_dns_span" class="input-group-btn">'
            . '<button id="'.$element['name'].'_resolve_dns" class="btn btn-default" type="button">Resolve</button>'
            . '</span>';
        
        $myHtml .= '</div>';
        
        return array(
            'html' => $myHtml,
            'js' => $myJs
        );
    }
    
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function addValidation($element)
    {
        $validations = parent::addValidation($element);
        
        $resolveUrl = Di::getDefault()
                        ->get('router')
                        ->getPathFor('/validator/resolvedns');
        
        $ipAddressValidationUrl = Di::getDefault()
                            ->get('router')
                            ->getPathFor('/validator/ipaddress');
        
        $validations['eventValidation'] .= '$("#'.$element['name'].'_resolve_dns").on("click", function(){
                $.ajax({
                    url: "'.$resolveUrl.'",
                    type: "POST",
                    data: {"dnsname":$("#'.$element['name'].'").val()},
                    dataType: "json",
                    context: document.body
                })
                .success(function(data, status, jqxhr) {
                    alertClose();
                    if (data["success"]) {
                        $("#'.$element['name'].'").val(data["value"]);
                        $("#'.$element['name'].'").trigger("blur");
                    } else {
                        alertMessage(data["error"], "alert-danger");
                    }
                });
            });';
        
        $validations['eventValidation'] .= '$("#'.$element['name'].'").on("blur", function() {
                    $.ajax({
                        url: "'.$ipAddressValidationUrl.'",
                        type: "POST",
                        data: {"ipaddress":$("#'.$element['name'].'").val()},
                        dataType: "json",
                        context: document.body
                    })
                    .success(function(data, status, jqxhr) {
                        alertClose();
                        if (data["success"]) {
                            $("#'
                            .$element['name']
                            .'_ipaddress").removeClass("has-error has-feedback");
                            $("#'
                            .$element['name']
                            .'_ipaddress_span").removeClass("glyphicon glyphicon-remove form-control-feedback");
                            $("#'
                            .$element['name']
                            .'_ipaddress").addClass("has-success has-feedback");
                            $("#'
                            .$element['name']
                            .'_ipaddress_span").addClass("glyphicon glyphicon-ok form-control-feedback");    
                        } else {
                            alertMessage(data["error"], "alert-danger");
                            $("#'
                            .$element['name']
                            .'_ipaddress").removeClass("has-error has-feedback");
                            $("#'
                            .$element['name']
                            .'_ipaddress_span").removeClass("glyphicon glyphicon-ok form-control-feedback");
                            $("#'
                            .$element['name']
                            .'_ipaddress").addClass("has-error has-feedback");
                            $("#'
                            .$element['name']
                            .'_ipaddress_span").addClass("glyphicon glyphicon-remove form-control-feedback"); 
                        }
                    });
                });';
        
        return $validations;
    }
}
