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
namespace Centreon\Core\Form\Custom;

class Selectimage implements Custominterface
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
        
        $addImageUrl = \Centreon\Core\Di::getDefault()
                        ->get('router')
                        ->getPathFor($element['label_wizardRoute']);
        
        $selectForImage = Select::renderHtmlInput($selectImageParameters);
        $fileUploadForImage = File::renderHtmlInput($element);
        
        $finalHtml = '<div class="row">'
            . '<div class="col-sm-10">'.$selectForImage['html'].'</div>'
            . '<div class="col-sm-2"><button class="btn btn-default" id="modalAdd_'.$element['name'].'" type="button">Add Files...</button></div>'
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
