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
require_once 'HTML/QuickForm/select.php';

/**
 * Description of select2
 *
 * @author Lionel Assepo <lassepo@centreon.com>
 */
class HTML_QuickForm_select2 extends HTML_QuickForm_select
{
    /**
     *
     * @var string 
     */
    var $_elementHtmlName;
    
    /**
     *
     * @var string 
     */
    var $_elementTemplate;
    
    /**
     *
     * @var string 
     */
    var $_elementCSS;
    
    /**
     *
     * @var string 
     */
    var $_availableDatasetRoute;
    
    /**
     *
     * @var string 
     */
    var $_defaultDatasetRoute;
    
    /**
     *
     * @var boolean 
     */
    var $_ajaxSource;
    
    /**
     *
     * @var boolean 
     */
    var $_multiple;
    
    /**
     *
     * @var string 
     */
    var $_multipleHtml;
    
    /**
     *
     * @var string 
     */
    var $_defaultSelectedOptions;
    
    /**
     *
     * @var string 
     */
    var $_jsCallback;
    
    
    /**
     *
     * @var boolean 
     */
    var $_allowClear;
    
    
    
    /**
     * 
     * @param string $elementName
     * @param string $elementLabel
     * @param array $options
     * @param array $attributes
     * @param string $sort
     */
    function HTML_QuickForm_select2(
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
        $this->HTML_QuickForm_select($elementName, $elementLabel, $options, $attributes);
        $this->_elementHtmlName = $this->getName();
        $this->parseCustomAttributes($attributes);
        $this->_jsCallback = '';
        $this->_allowClear = false;
    }
    
    /**
     * 
     * @param array $attributes
     */
    function parseCustomAttributes(&$attributes)
    {
        // Check for 
        if (isset($attributes['datasourceOrigin']) && ($attributes['datasourceOrigin'] == 'ajax')) {
            $this->_ajaxSource = true;
            // Check for 
            if (isset($attributes['availableDatasetRoute'])) {
                $this->_availableDatasetRoute = $attributes['availableDatasetRoute'];
            }
            
            // Check for 
            if (isset($attributes['defaultDatasetRoute'])) {
                $this->_defaultDatasetRoute = $attributes['defaultDatasetRoute'];
            }
        }
        
        if (isset($attributes['multiple']) && $attributes['multiple'] === true) {
            $this->_elementHtmlName .= '[]';
            $this->_multiple = true;
            $this->_multipleHtml = 'multiple="multiple"';
        } else {
            $this->_multiple = false;
        }
        
        if(isset($attributes['allowClear']) && $attributes['allowClear'] === false){
            $this->_allowClear = false;
        }else if(isset($attributes['allowClear']) && $attributes['allowClear'] === true){
            $this->_allowClear = true;
        }
        
    }
    
    /**
     * 
     * @param boolean $raw
     * @param boolean $min
     * @return string
     */
    function getElementJs($raw = true, $min = false)
    {
        $jsFile = './include/common/javascript/jquery/plugins/select2/js/';
        
        if ($min) {
            $jsFile .= 'select2.min.js';
        } else {
            $jsFile .= 'select2.js';
        }
        
        $js = '<script type="text/javascript" '
            . 'src="' . $jsFile . '">'
            . '</script>';
        
        return $js;
    }
    
    /**
     * 
     * @return type
     */
    function getElementHtmlName()
    {
        return $this->_elementHtmlName;
    }
    
    /**
     * 
     * @param boolean $raw
     * @param boolean $min
     * @return string
     */
    function getElementCss($raw = true, $min = false)
    {
        $cssFile = './include/common/javascript/jquery/plugins/select2/css/';
        
        if ($min) {
            $cssFile .= 'select2.min.js';
        } else {
            $cssFile .= 'select2.js';
        }
        
        $css = '<link href="' . $cssFile . '" rel="stylesheet" type="text/css"/>';
        
        return $css;
    }
    
    /**
     * 
     * @return string
     */
    function toHtml()
    {
        $strHtml = '';
        $readonly = '';
        
        
        
        $strHtml = '<select id="' . $this->getName()
            . '" name="' . $this->getElementHtmlName()
            . '" ' . $this->_multipleHtml . ' '
            . ' style="width: 300px;" ' . $readonly . '><option></option>'
            . '%%DEFAULT_SELECTED_VALUES%%'
            . '</select>';
        if(!$this->_allowClear){
            $strHtml .= '<span style="cursor:pointer;" class="clearAllSelect2">x</span>';
        }
        
        $strHtml .= $this->getJsInit();
        $strHtml = str_replace('%%DEFAULT_SELECTED_VALUES%%', $this->_defaultSelectedOptions, $strHtml);
        
        return $strHtml;
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
        
        $mainJsInit = 'allowClear: true,';
        
        $label = $this->getLabel();
        if (!empty($label)) {
            $mainJsInit .= 'placeholder: "' . $this->getLabel() . '",';
        }
        
        if ($this->_flagFrozen) {
             $mainJsInit .= 'disabled: true,';
        }
        
        
        if ($this->_ajaxSource) {
            $mainJsInit .= $this->setAjaxSource() . ',';
            if ($this->_defaultDatasetRoute) {
                $additionnalJs .= $this->setDefaultAjaxDatas();
            }
        } else {
            $mainJsInit .= $this->setFixedDatas() . ',';
        }
        
        $mainJsInit .= 'multiple: ';
        if ($this->_multiple) {
            $mainJsInit .= 'true,';
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
            $strJsInitEnding .= 'jQuery("#' . $this->getName() . '").nextAll(".clearAllSelect2").on("click",function(){'
                . 'jQuery("#' . $this->getName() . '").val("");'
                . 'jQuery("#' . $this->getName() . '").empty().append(jQuery("<option>"));'
                . 'jQuery("#' . $this->getName() . '").trigger("change");'
                . '});';
        }
        
        
        
        $finalJs = $jsPre . $strJsInitBegining . $mainJsInit . $strJsInitEnding . $additionnalJs . $this->_jsCallback . $jsPost;
        
        return $finalJs;
    }
    
    /**
     * 
     * @return string
     */
    public function setFixedDatas()
    {
        $datas = 'data: [';
        
        // Set default values
        $strValues = is_array($this->_values)? array_map('strval', $this->_values): array();
        
        foreach ($this->_options as $option) {
            if (empty($option["attr"]["value"])) {
                $option["attr"]["value"] = -1;
            }
            $datas .= '{id: ' . $option["attr"]["value"] . ', text: "' . $option['text'] . '"},';
            
            if (!empty($strValues) && in_array($option['attr']['value'], $strValues, true)) {
                $option['attr']['selected'] = 'selected';
                $this->_defaultSelectedOptions .= "<option" . $this->_getAttrString($option['attr']) . '>' .
                        $option['text'] . "</option>";
            }
        }
        $datas .= ']';
        
        return $datas;
    }
    
    /**
     * 
     * @return string
     */
    public function setAjaxSource()
    {
        $ajaxInit = 'ajax: { ';
        $ajaxInit .= 'url: "' . $this->_availableDatasetRoute . '",'
            . 'data: function (params) {
                    var queryParameters = {
                        q: params.term
                    };
                    
                    return queryParameters;
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                }';
        $ajaxInit .= '} ';
        return $ajaxInit;
    }
    
    /**
     * 
     * @param string $event
     * @param string $callback
     */
    public function addJsCallback($event, $callback)
    {
        $this->_jsCallback .= ' jQuery("#' . $this->getName() . '").on("' . $event . '", function(){ '
            . $callback
            . ' }); ';
    }
    
    /**
     * 
     * @return string
     */
    public function setDefaultAjaxDatas()
    {
        $ajaxDefaultDatas = '$request' . $this->getName() . ' = jQuery.ajax({
            url: "'. $this->_defaultDatasetRoute .'",
        });

        $request' . $this->getName() . '.success(function (data) {
            for (var d = 0; d < data.length; d++) {
                var item = data[d];
                
                // Create the DOM option that is pre-selected by default
                var option = new Option(item.text, item.id, true, true);
              
                // Append it to the select
                $currentSelect2Object'.$this->getName().'.append(option);
            }
 
            // Update the selected options that are displayed
            $currentSelect2Object'.$this->getName().'.trigger("change",[{origin:\'select2defaultinit\'}]);
        });
        
        $request' . $this->getName() . '.error(function(data) {
            
        });
        ';
        
        return $ajaxDefaultDatas;
    }
    
    /**
     * 
     * @return string
     */
    function getFrozenHtml()
    {
        $strFrozenHtml = '';
        return $strFrozenHtml;
    }
}

if (class_exists('HTML_QuickForm')) {
    HTML_QuickForm::registerElementType(
        'select2',
        'HTML/QuickForm/select2.php',
        'HTML_QuickForm_select2'
    );
}
