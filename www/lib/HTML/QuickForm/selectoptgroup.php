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
 * Description of select2
 *
 * @author Lionel Assepo <lassepo@centreon.com>
 */
class HTML_QuickForm_selectoptgroup extends HTML_QuickForm_select
{
    /**
     *
     * @var string
     */
    public $_elementHtmlName;

    /**
     *
     * @var string
     */
    public $_elementTemplate;

    /**
     *
     * @var string
     */
    public $_elementCSS;

    /**
     *
     * @var string
     */
    public $_availableDatasetRoute;

    /**
     *
     * @var string
     */
    public $_defaultDatasetRoute;

    /**
     *
     * @var string
     */
    public $_defaultDataset;

    /**
     *
     * @var boolean
     */
    public $_ajaxSource;

    /**
     *
     * @var boolean
     */
    public $_multiple;

    /**
     *
     * @var string
     */
    public $_multipleHtml;

    /**
     *
     * @var string
     */
    public $_defaultSelectedOptions;

    /**
     *
     * @var string
     */
    public $_jsCallback;

    /**
     *
     * @var boolean
     */
    public $_allowClear;

    /**
     *
     * @var string
     */
    public $_linkedObject;

    /**
     *
     * @var type
     */
    public $_defaultDatasetOptions;

    /**
     * @var int The number of element in the pagination
     */
    public $_pagination;

    /**
     * @var array
     */
    public $realOptionsArray;

    /**
     *
     * @param string $elementName
     * @param string $elementLabel
     * @param array $options
     * @param array $attributes
     * @param string $sort
     */
    public function __construct(
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
        $this->realOptionsArray = $options;
        parent::__construct($elementName, $elementLabel, $options, $attributes);
        $this->_elementHtmlName = $this->getName();
        $this->_defaultDataset = null;
        $this->_defaultDatasetOptions = array();
        $this->_jsCallback = '';
        $this->parseCustomAttributes($attributes);

        $this->_pagination = $centreon->optGen['selectPaginationSize'];
    }

    /**
     *
     * @param array $attributes
     */
    public function parseCustomAttributes(&$attributes)
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

        if (isset($attributes['defaultDataset']) && !is_null($attributes['defaultDataset'])) {
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
    public function getElementJs($raw = true, $min = false)
    {
        $jsFile = './include/common/javascript/jquery/plugins/select2/js/';
        $jsFile2 = './include/common/javascript/centreon/centreon-select2-optgroup.js';

        if ($min) {
            $jsFile .= 'select2.min.js';
        } else {
            $jsFile .= 'select2.js';
        }

        $js = '<script type="text/javascript" '
            . 'src="' . $jsFile . '">'
            . '</script>'
            . '<script type="text/javascript" '
            . 'src="' . $jsFile2 . '">'
            . '</script>';

        return $js;
    }

    /**
     *
     * @return type
     */
    public function getElementHtmlName()
    {
        return $this->_elementHtmlName;
    }

    /**
     *
     * @param boolean $raw
     * @param boolean $min
     * @return string
     */
    public function getElementCss($raw = true, $min = false)
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
    public function toHtml()
    {
        $strHtml = '';
        $readonly = '';
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
    public function getJsInit()
    {
        $allowClear = 'true';
        $additionnalJs = '';

        if (false === $this->_allowClear || $this->_flagFrozen) {
            $allowClear = 'false';
        }

        $disabled = 'false';
        if ($this->_flagFrozen) {
            $disabled = 'true';
        }

        $ajaxOption = '';
        $defaultData = '';
        if ($this->_ajaxSource) {
            $ajaxOption = 'ajax: {
                url: "' . $this->_availableDatasetRoute . '"
            },';

            if ($this->_defaultDatasetRoute && is_null($this->_defaultDataset)) {
                $additionnalJs = $this->setDefaultAjaxDatas();
            } else {
                $this->setDefaultFixedDatas();
            }
        } else {
            $defaultData = $this->setFixedDatas() . ',';
            $this->setDefaultFixedDatas();
        }

        $additionnalJs .= ' ' . $this->_jsCallback;

        $javascriptString = '<script>
            jQuery(function () {
                var $currentSelect2Object' . $this->getName() .
            ' = jQuery("#' . $this->getName() . '").centreonSelect2({
                    allowClear: ' . $allowClear . ',
                    pageLimit: ' . $this->_pagination . ',
                    optGroup: true,
                    select2: {
                        ' . $ajaxOption . '
                        ' . $defaultData . '
                        placeholder: "' . $this->getLabel() . '",
                        disabled: ' . $disabled . '
                    }
                });
                
                ' . $additionnalJs . '
            });
         </script>';

        return $javascriptString;
    }

    /**
     *
     * @return string
     */
    public function setFixedDatas()
    {
        $datas = 'data: ';
        $datas .= json_encode($this->realOptionsArray, 1);
        return $datas;
    }

    public $_memOptions = array();

    /**
     * obsolete
     */
    public function setDefaultFixedDatas()
    {
        return true;
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
            url: "' . $this->_defaultDatasetRoute . '",
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
                $currentSelect2Object' . $this->getName() . '.append(option);
            }
 
            // Update the selected options that are displayed
            $currentSelect2Object' . $this->getName() . '.trigger("change",[{origin:\'select2defaultinit\'}]);
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
    public function getFrozenHtml()
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
    public function onQuickFormEvent($event, $arg, &$caller)
    {
        if ('updateValue' == $event) {
            $value = $this->_findValue($caller->_constantValues);

            if (null === $value) {
                if (is_null($this->_defaultDataset)) {
                    $value = $this->_findValue($caller->_submitValues);
                } else {
                    $value = $this->_defaultDataset;
                }

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
}

if (class_exists('HTML_QuickForm')) {
    (new HTML_QuickForm)->registerElementType(
        'selectoptgroup',
        'HTML/QuickForm/selectoptgroup.php',
        'HTML_QuickForm_selectoptgroup'
    );
}
