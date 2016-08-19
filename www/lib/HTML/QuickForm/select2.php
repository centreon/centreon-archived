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
     * @var string
     */
    var $_defaultDataset;
    
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
     * @var string 
     */
    var $_linkedObject;
    
    /**
     *
     * @var type 
     */
    var $_defaultDatasetOptions;
    
    /**
     * @var int The number of element in the pagination
     */
    var $_pagination;
    
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
        global $centreon;
        $this->_ajaxSource = false;
        $this->_defaultSelectedOptions = '';
        $this->_multipleHtml = '';
        $this->_allowClear = true; 
        $this->HTML_QuickForm_select($elementName, $elementLabel, $options, $attributes);
        $this->_elementHtmlName = $this->getName();
        $this->_defaultDataset = array();
        $this->_defaultDatasetOptions = array();
        $this->_jsCallback = '';
        $this->_allowClear = false;
        $this->parseCustomAttributes($attributes);
        
        $this->_pagination = $centreon->optGen['selectPaginationSize'];
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
        
        if (isset($attributes['allowClear']) && $attributes['allowClear'] === false) {
            $this->_allowClear = false;
        } elseif (isset($attributes['allowClear']) && $attributes['allowClear'] === true) {
            $this->_allowClear = true;
        }
        
        if (isset($attributes['defaultDataset'])) {
            $this->_defaultDataset = $attributes['defaultDataset'];
        }
        
        if (isset($attributes['defaultDatasetOptions'])) {
            $this->_defaultDatasetOptions = $attributes['defaultDatasetOptions'];
        }
        
        if (isset($attributes['linkedObject'])) {
            $this->_linkedObject = $attributes['linkedObject'];
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
        
        if(!$this->_allowClear && !$this->_flagFrozen){
            $strHtml .= '<span style="cursor:pointer;" class="clearAllSelect2" title="Clear field" ><img src="./img/icons/circle-cross.png" class="ico-14" /></span>';
        }
        $strHtml .= '<select id="' . $this->getName()
            . '" name="' . $this->getElementHtmlName()
            . '" ' . $this->_multipleHtml . ' '
            . ' style="width: 300px;" ' . $readonly . '><option></option>'
            . '%%DEFAULT_SELECTED_VALUES%%'
            . '</select>';
        
        
        $strHtml .= $this->getJsInit();
        $strHtml = str_replace('%%DEFAULT_SELECTED_VALUES%%', $this->_defaultSelectedOptions, $strHtml);
        
        return $strHtml;
    }
    
    /**
     * 
     * @return string
     */
    function addShift()
    {
        $myJs = '

            jQuery("#' . $this->getName() . '").on("select2:open", function (e) {
                e.preventDefault();
                var data = jQuery(this).data();
                data.select2.shiftFirstEl = null;
                
            });
            
            var endSelection = 0;
            jQuery("#' . $this->getName() . '").on("select2:selecting", function (event) {
                var data = jQuery(event.currentTarget).data();
                if (event.params.args.originalEvent.shiftKey) {
                    // To keep select2 opened
                    event.preventDefault(); 
                    
                    if (!data.select2.hasOwnProperty("shiftFirstEl") || data.select2.shiftFirstEl === null) {
                        data.select2.shiftFirstEl = event.params.args.data.id;
                        endSelection = 0;
                    } else {
                        endSelection = event.params.args.data.id;
                        startSelection = data.select2.shiftFirstEl;

                        var selectedValues = [];
                        startIndex = 0;
                        endIndex = 0;
                        jQuery(".select2-results li>span").each(function (index){
                            var $this = jQuery(this);
                            if ($this.data("did") == startSelection) {
                                startIndex = index;
                            }
                            if ($this.data("did") == endSelection) {
                                endIndex = index;
                            }
                        });

                        if (endIndex < startIndex) {
                            tempIndex = startIndex;
                            startIndex = endIndex;
                            endIndex = tempIndex;
                        }

                        jQuery(".select2-results li>span").each(function (index){
                            var $this = jQuery(this);
                            
                            if (index >= startIndex && index <= endIndex) {
                                selectedValues.push({id: $this.data("did").toString(), text: $this.text()});
                            }
                        });

                        for (var i = 0; i < selectedValues.length; i++) {
                            var item = selectedValues[i];

                            // Create the DOM option that is pre-selected by default
                            var option = "<option selected=\"selected\" value=\"" + item.id + "\" ";
                            if (item.hide === true) {
                                option += "hidden";
                            }
                            option += ">" + item.text + "</option>";

                            // Append it to the select
                            $currentSelect2Object'.$this->getName().'.append(option);
                        }

                        // Update the selected options that are displayed
                        $currentSelect2Object'.$this->getName().'.select2("close");
                            $currentSelect2Object'.$this->getName().'.trigger("change");
                            data.select2.shiftFirstEl = null;
                    } 
                } 
            });
        ';
        return $myJs;
    }
    
    function templatingSelect2()
    {
        $js = "
            function select2_formatResult(item) {
                if(item.id) {
                    span = jQuery('<span data-did=\"' + item.id + '\" title=\"' + item.text + '\" >' + item.text + '</span>');
                    return span;
                } else {
                    return item.text;
                }
            }";
        return $js;
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
        
        $mainJsInit = 'allowClear: true,'
            . 'templateResult: ' . $this->templatingSelect2() . ',';
        
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

            # Init nice scroll
            $scroll .= 'var initNiceScroll = function(element) {
                element.next(".select2-container").find("ul.select2-selection__rendered").niceScroll({
                    cursorcolor:"#818285",
                    cursoropacitymax: 0.6,
                    cursorwidth:3,
                    horizrailenabled: true,
                    autohidemode: true
                });
            };';

            # Init nice scroll on tabs form
            $scroll .= 'jQuery("body").on("inittab:centreon", function(event, id) {
                var tabElement = $currentSelect2Object'. $this->getName() . '.parents(".tab");
                if (jQuery(tabElement).attr("id") == id) {
                    initNiceScroll($currentSelect2Object' . $this->getName() .');
                }
            });';

            # Update nice scroll when changing tab
            $scroll .= 'jQuery("body").on("changetab:centreon", function(event, id) {
                var tabElement = $currentSelect2Object'. $this->getName() . '.parents(".tab");
                if (jQuery(tabElement).attr("id") == id) {
                    initNiceScroll($currentSelect2Object' . $this->getName() .');
                } else {
                    $currentSelect2Object'. $this->getName() . '.next(".select2-container").find("ul.select2-selection__rendered").getNiceScroll().remove();
                }
            });';

            # Update nice scroll when modify select2
            $scroll .= '$currentSelect2Object'. $this->getName() . '.on("change.select2", function (event) {
                $currentSelect2Object'. $this->getName() . '.next(".select2-container").find("ul.select2-selection__rendered").getNiceScroll().remove();
                initNiceScroll($currentSelect2Object' . $this->getName() .');
            });';

            # Nicescroll for listed elements
            $scroll .= '$currentSelect2Object'. $this->getName() . '.on("select2:open", function (event) {
                jQuery("ul.select2-results__options").off("mousewheel");
                jQuery("ul.select2-results__options").niceScroll({
                    cursorcolor:"#818285",
                    cursoropacitymax: 0.6,
                    cursorwidth:3,
                    horizrailenabled: true,
                    zindex: 5000,
                    autohidemode: false
                });
            });';
            
            /* Add catch event for block close dropdown when selectall */
            $scroll .= '$currentSelect2Object'. $this->getName() . '.on("select2:closing", function (event) {
                if (jQuery("#confirm'  . $this->getName() . '").length > 0) {
                    event.preventDefault();
                }
            });';

            $scroll .= '$currentSelect2Object'. $this->getName() . '.on("select2:open", function (event) {
                if (!jQuery(".select2-results-header").length) {
                    jQuery("span.select2-results").parents(".select2-dropdown").prepend(
                        "<div class=\'select2-results-header\'>" +
                            "<div class=\'select2-results-header__nb-elements\'>" +
                                "<span class=\'select2-results-header__nb-elements-value\'></span> ' . _(' element(s) found') . '" +
                            "</div>" +
                            "<div class=\'select2-results-header__select-all\'>" +
                                "<button class=\'btc bt_info\' onclick=\' $currentSelect2Object' . $this->getName() . '.confirmSelectAll();\'>' . _('Select all') . '</button>" +
                            "</div>" +
                        "</div>"
                    );
                }
            });
            

            $currentSelect2Object' . $this->getName() . '.confirmSelectAll = function() {
                /* Create div for popin */
                var $confirmBox = jQuery(
                    "<div id=\'confirm'  . $this->getName() . '\'>" +
                    " <p>' . _('Add ') . '" + jQuery(".select2-results-header__nb-elements-value").text() + "' . _(' elements to selection ?') . '</p>" +
                    " <div class=\'button_group_center\'>" +
                    "   <button type=\'button\' class=\'btc bt_success\'>' . _('Ok') . '</button>" +
                    "   <button type=\'button\' class=\'btc bt_default\'>' . _('Cancel') . '</button>" +
                    " </div>" +
                    "</div>"
                ).appendTo("body");

                jQuery(document).bind("keyup.centreonPopin", function (e) {
                    if (e.keyCode === 27) {
                       jQuery(document).unbind("keyup.centreonPopin");
                       $confirmBox.centreonPopin("close");
                       $confirmBox.remove();
                   }
                });
                
                $confirmBox.centreonPopin({open: true});
                
                jQuery("#confirm'  . $this->getName() . ' .btc.bt_success").on("click", function () {
                    // Get search value
                    var search = $currentSelect2Object' . $this->getName() . '.data().select2.$container.find(".select2-search__field").val();

                    // Get data filtered by search
                    jQuery.ajax({
                        url: "'. $this->_availableDatasetRoute .'",
                        data: {
                            q: search
                        },
                    }).success(function(data) {
                        // Get value already selected to avoid to select it twice
                        var selectedIds = [];
                        $currentSelect2Object'.$this->getName().'.find("option").each(function() {
                            var value = jQuery(this).val();
                            if (value.trim() !== "") {
                                selectedIds.push(jQuery(this).val());
                            }
                        });
                        for (var d = 0; d < data.items.length; d++) {
                            var item = data.items[d];

                            // Create the DOM option that is pre-selected by default
                            var option = "<option selected=\"selected\" value=\"" + item.id + "\" ";
                            if (item.hide === true) {
                                option += "hidden";
                            }
                            option += ">" + item.text + "</option>";

                            // Append it to the select
                            if (selectedIds.indexOf("" + item.id) < 0) {
                                $currentSelect2Object'.$this->getName().'.append(option);
                            }
                        }

                        // Update the selected options that are displayed
                        $currentSelect2Object'.$this->getName().'.trigger("change");

                        // Close select2
                        $currentSelect2Object'.$this->getName().'.select2("close");
                    });
                    jQuery(document).unbind("keyup.centreonPopin");
                    $confirmBox.centreonPopin("close");
                    $confirmBox.remove();
                });

                jQuery("#confirm'  . $this->getName() . ' .btc.bt_default, #confirm'  . $this->getName() . ' a.close").on("click", function () {
                    jQuery(document).unbind("keyup.centreonPopin");
                    $confirmBox.centreonPopin("close");
                    $confirmBox.remove();
                });

                jQuery("#centreonPopinOverlay").on("click", function (e) {
                    if (jQuery(e.target).parents(".centreon-popin").length === 0) {
                        jQuery(document).unbind("keyup.centreonPopin");
                        $confirmBox.centreonPopin("close");
                        $confirmBox.remove();
                    }
                });
            };';

            $mainJsInit .= 'templateSelection: function (data, container) {
                if (data.element.hidden === true) {
                    $(container).hide();
                }
                return data.text;
            },';
        } else {
            $mainJsInit .= 'false,';
        }
        
        $mainJsInit .= 'allowClear: ';
        if ($this->_allowClear) {
            $mainJsInit .= 'true,';
        } else {
            $mainJsInit .= 'false,';
        }
        
        $mainJsInit .= 'templateSelection: function (element) {
            return jQuery(\'<span class="select2-content" title="\' + element.text + \'">\' + element.text + \'</span>\');
        }';

        $strJsInitEnding = '});';
        
        if (!$this->_allowClear) {
            $strJsInitEnding .= 'jQuery("#' . $this->getName() . '").prevAll(".clearAllSelect2").on("click",function(){ '
                . '$currentValues = jQuery("#' . $this->getName() . '").val(); '
                . 'jQuery("#' . $this->getName() . '").val("");'
                . 'jQuery("#' . $this->getName() . '").empty().append(jQuery("<option>"));'
                . 'jQuery("#' . $this->getName() . '").trigger("change", $currentValues);'
                . ' }); ';
        }
        
        $additionnalJs .= $this->saveSearchJs();
        
        $additionnalJs .= $this->addShift();
        
        $finalJs = $jsPre . $strJsInitBegining . $mainJsInit . $strJsInitEnding . $scroll . $additionnalJs . $this->_jsCallback . $jsPost;
        
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

    var $_memOptions = array();
    /**
      * 
     */
    function setDefaultFixedDatas()
    {
        global $pearDB;
        
        if (!is_null($this->_linkedObject)) {
            require_once _CENTREON_PATH_ . '/www/class/' . $this->_linkedObject . '.class.php';
            $objectFinalName = ucfirst($this->_linkedObject);

            $myObject = new $objectFinalName($pearDB);
            $finalDataset = $myObject->getObjectForSelect2($this->_defaultDataset, $this->_defaultDatasetOptions);

            foreach ($finalDataset as $dataSet) {
                $currentOption = '<option selected="selected" value="'
                    . $dataSet['id'] . '" ';
                if (isset($dataSet['hide']) && $dataSet['hide'] === true) {
                    $currentOption .= "hidden";
                }
                $currentOption .= '>'
                    . $dataSet['text'] . "</option>";

                if (!in_array($dataSet['id'], $this->_memOptions)) {
                    $this->_memOptions[] = $dataSet['id'];
                    $this->_defaultSelectedOptions .= $currentOption;
                }
            }
        } else {
            foreach ($this->_defaultDataset as $elementName => $elementValue) {
                $currentOption .= '<option selected="selected" value="'
                    . $elementValue . '">'
                    . $elementName . "</option>";
                
                if (!in_array($elementValue, $this->_memOptions)) {
                    $this->_memOptions[] = $elementValue;
                    $this->_defaultSelectedOptions .= $currentOption;
                }
            }
        }
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
                    return {
                        q: params.term,
                        page_limit: ' . $this->_pagination . ',
                        page: params.page || 1
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    jQuery(".select2-results-header__nb-elements-value").text(data.total);
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * ' . $this->_pagination . ') < data.total
                        }
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
                var option = "<option selected=\"selected\" value=\"" + item.id + "\" ";
                if (item.hide === true) {
                    option += "hidden";
                }
                option += ">" + item.text + "</option>";
              
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
    
    /**
     * 
     * @param type $event
     * @param type $arg
     * @param type $caller
     * @return boolean
     */
    function onQuickFormEvent($event, $arg, &$caller)
    {
        if ('updateValue' == $event) {
            $value = $this->_findValue($caller->_constantValues);
            if (null === $value) {
                $value = $this->_findValue($caller->_submitValues);
                // Fix for bug #4465 & #5269
                // XXX: should we push this to element::onQuickFormEvent()?
                if (null === $value && (!$caller->isSubmitted() || !$this->getMultiple())) {
                    $value = $this->_findValue($caller->_defaultValues);
                }
            }
            if (null !== $value) {
                if (!is_array($value)) {
                    $value = array($value);
                }
                $this->_defaultDataset = $value;
                $this->setDefaultFixedDatas();
            }
            return true;
        } else {
            return parent::onQuickFormEvent($event, $arg, $caller);
        }
    }
    
    /**
     * Prepare JS code for save the search
     *
     * @return string The javscript code
     */
    protected function saveSearchJs()
    {
        $string = 'jQuery("#' . $this->getName() . '").on("select2:closing", function (event) {
                var $select = jQuery(event.currentTarget);
                var data = $select.data();
                var $search = data.select2.$container.find(".select2-search__field");
                data.select2.saveSearch = $search.val();
            });';
            
        $string .= 'jQuery("#' . $this->getName() . '").on("select2:open", function (event) {
                var $select = jQuery(event.currentTarget);
                var data = $select.data();
                var $search = data.select2.$container.find(".select2-search__field");
                if (data.select2.saveSearch) {
                  $search.val(data.select2.saveSearch);
                  /* Wait for select2 finish to open */
                  setTimeout(function () {
                    data.select2.trigger("query", {term: data.select2.saveSearch});
                  }, 10);
                }
            });';
            
        return $string;
    }
}

if (class_exists('HTML_QuickForm')) {
    HTML_QuickForm::registerElementType(
        'select2',
        'HTML/QuickForm/select2.php',
        'HTML_QuickForm_select2'
    );
}
