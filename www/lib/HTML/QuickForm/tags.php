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
 *
 */

/**
 * Base class for form elements
 */ 
require_once 'HTML/QuickForm/select2.php';

/**
 * Description of tags
 *
 * @author Toufik MECHOUET
 */
class HTML_QuickForm_tags extends HTML_QuickForm_select2
{
 
    /**
     * 
     * @param string $elementName
     * @param string $elementLabel
     * @param array $options
     * @param array $attributes
     * @param string $sort
     */
    function HTML_QuickForm_tags(
        $elementName = null,
        $elementLabel = null,
        $options = null,
        $attributes = null,
        $sort = null
    ) {
        $this->_ajaxSource = false;
        $this->_defaultSelectedOptions = '';
        $this->_multipleHtml = '';
        $this->_allowClear = true; 
        $this->HTML_QuickForm_select2($elementName, $elementLabel, $options, $attributes);
        $this->_elementHtmlName = $this->getName();
        $this->_defaultDataset = array();
        $this->_defaultDatasetOptions = array();
        $this->_jsCallback = '';
        $this->_allowClear = false;
        $this->parseCustomAttributes($attributes);
    }
    
   
    /**
     * 
     * @return string
     */
    function getJsInit()
    {
        $jsPre = '<script type="text/javascript">';
        $additionnalJs = '';
        $jsPost = '</script>';
        $strJsInitBegining = '$currentSelect2Object'. $this->getName() . ' = jQuery("#' . $this->getName() . '").select2({';
        
        $mainJsInit .= 'tags: true,';
        $mainJsInit .= 'allowClear: true,';
        
        $label = $this->getLabel();
        if (!empty($label)) {
            $mainJsInit .= 'placeholder: "' . $this->getLabel() . '",';
        }
        
        if ($this->_flagFrozen) {
             $mainJsInit .= 'disabled: true,';
        }
        
        if ($this->_ajaxSource) {
            $mainJsInit .= $this->setAjaxSource() . ',';
            if ($this->_defaultDatasetRoute && (count($this->_defaultDataset) == 0)) {
                $additionnalJs .= $this->setDefaultAjaxDatas();
            } else {
                $this->setDefaultFixedDatas();
            }
        } else {
            $mainJsInit .= $this->setFixedDatas() . ',';
        }
        
        $mainJsInit .= 'multiple: ';
        $scroll = "";
        if ($this->_multiple) {
            $mainJsInit .= 'true,';
            $scroll = '$currentSelect2Object'. $this->getName() . '.next(".select2-container").find("ul.select2-selection__rendered").niceScroll({
            	cursorcolor:"#818285",
            	cursoropacitymax: 0.6,
            	cursorwidth:3,
            	horizrailenabled:false
            	});';

                $mainJsInit .= 'templateSelection: function (data, container) {
                    if (data.element.hidden === true) {
                        $(container).hide();
                    }
                    return data.text;
                },';
        } else {
            $mainJsInit .= 'false,';
        }
        //$mainJsInit .= 'minimumInputLength: 1,';
        
        $mainJsInit .= 'allowClear: ';
        if ($this->_allowClear) {
            $mainJsInit .= 'true,';
        } else {
            $mainJsInit .= 'false,';
        }

        $strJsInitEnding = '});';
        
        if (!$this->_allowClear) {
            $strJsInitEnding .= 'jQuery("#' . $this->getName() . '").prevAll(".clearAllSelect2").on("click",function(){ '
                . '$currentValues = jQuery("#' . $this->getName() . '").val(); '
                . 'jQuery("#' . $this->getName() . '").val("");'
                . 'jQuery("#' . $this->getName() . '").empty().append(jQuery("<option>"));'
                . 'jQuery("#' . $this->getName() . '").trigger("change", $currentValues);'
                . ' }); ';
        }
        
        
        $additionnalJs .= ' jQuery(".select2-selection").each(function(){'
            . ' if(typeof this.isResiable == "undefined" || this.isResiable){'
            . ' jQuery(this).resizable({ maxWidth: 500, '
            . ' minWidth : jQuery(this).width() != 0 ? jQuery(this).width() : 200, '
            . ' minHeight : jQuery(this).height() != 0 ? jQuery(this).height() : 45 });'
            . ' this.isResiable = true; '
            . ' }'
            . ' }); ';
        
        $finalJs = $jsPre . $strJsInitBegining . $mainJsInit . $strJsInitEnding . $scroll . $additionnalJs . $this->_jsCallback . $jsPost;
        
        return $finalJs;
    }
    
}

if (class_exists('HTML_QuickForm')) {
    HTML_QuickForm::registerElementType(
        'tags',
        'HTML/QuickForm/tags.php',
        'HTML_QuickForm_tags'
    );
}
