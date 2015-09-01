<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace Centreon\Internal\Form\Component;

use Centreon\Internal\Di;

class Selectimage extends Component
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        $selectImageParameters = array(
            'label_label' => $element['label_label'],
            'label_multiple' => false,
            'name' => $element['name'],
            'label_object_type' => $element['label_object_type'],
            'label_defaultValuesRoute' => $element['label_defaultValuesRoute'],
            'label_listValuesRoute' => $element['label_listValuesRoute'],
            'label_extra' => $element['label_extra'],
            'label_object_type' => $element['label_object_type']
        );
        
        $addImageUrl = Di::getDefault()
                        ->get('router')
                        ->getPathFor($element['label_wizardRoute']);
        
        $selectForImage = Select::renderHtmlInput($selectImageParameters);
        $fileUploadForImage = File::renderHtmlInput($element);
        
        $finalHtml = '<div class="inlineGroup">'
                . '<div class="Elem1">'.$selectForImage['html'].'</div>'
                . '<div class="Elem2">'
                    . '<button '
                        . 'class="btnC btnDefault " '
                        . 'id="modalAdd_'.$element['name'].'" '
                        . 'type="button">'
                        . '<i class="icon-upload"></i>'
                    . '</button>'
                . '</div>'
            . '</div>';
        
        $finalJs = $selectForImage['js'].' '.$fileUploadForImage['js'].' ';
        $finalJs .= '$("#modalAdd_'.$element['name'].'").on("click", function(e) {
            $("#modal").removeData("bs.modal");
            $("#modal").removeData("centreonWizard");
            $("#modal .modal-content").text("");
            $("#modal").one("loaded.bs.modal", function(e) {
                $(this).centreonWizard();
            });
            $("#modal").modal({
                "remote": "'.$addImageUrl.'"
            });
        });';
        
        return array(
            'html' => $finalHtml,
            'js' => $finalJs
        );
    }
}
