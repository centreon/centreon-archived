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

namespace Centreon\Core;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Core
 */
class Form
{
    /**
     *
     * @var \HTML_QuickForm
     */
    private $formProcessor;
    
    /**
     *
     * @var HTML_QuickForm_Renderer_ArraySmarty
     */
    private $formRenderer;
    
    /**
     *
     * @var type 
     */
    private $options;
    
    /**
     *
     * @var \Centreon\Core\Template
     */
    private $tpl;
    
    /**
     *
     * @var type
     */
    private $template;

    /**
     *
     * @var \Centreon\Core\Di 
     */
    private $di;
    
    /**
     * The style for quickform elements
     * 
     * @var array
     */
    private $style;
    
    /**
     * The separator for quickform elements
     *
     * @var string
     */
    private $basicSeparator;
    
    /**
     * Javascript rules register
     *
     * @var array
     */
    private $jsRulesRegister = array(
        'required',
        'min',
        'max',
        'range',
        'email',
        'url',
        'date',
        'dateISO',
        'number',
        'digits',
        'creditcard',
        'equalTo',
        'phoneUS'
    );
    
    /**
     * Javascript rules 
     *
     * @var array
     */
    private $jsRules;
    
    /**
     *
     * @var type 
     */
    private $javascriptCall = "";
    
    /**
     * Constructor
     *
     * @param string $name The name of form
     * @param array $options The options
     */
    public function __construct($name, $options = null)
    {
        $this->formProcessor = new \HTML_QuickForm($name, 'post');
        $this->options = $options;
        $this->init();
        $this->di = \Centreon\Core\Di::getDefault();
        $this->tpl = $this->di->get('template');
        $this->addSecurity();
    }
    
    /**
     * 
     * @return \HTML_QuickForm
     */
    public function toSmarty()
    {
        $this->formRenderer = new \HTML_QuickForm_Renderer_ArraySmarty($this->tpl, true);
        $this->formRenderer->setRequiredTemplate('{label}<font color="red" size="1">*</font>');
        $this->formRenderer->setErrorTemplate('<font color="red">{error}</font><br />{html}');
        $this->formProcessor->accept($this->formRenderer);
        return $this->formatForSmarty();
    }
    
    public function getJavascriptCall()
    {
        return $this->javascriptCall;
    }
    
    /**
     * 
     * @param array $smartyArray
     */
    private function formatForSmarty()
    {
        $smartyArray = $this->formRenderer->toArray();
        
        $finalArray = array (
            'frozen' => $smartyArray['frozen'],
            'javascript' => $smartyArray['javascript'],
            'attributes' => $smartyArray['attributes'],
            'requirednote' => $smartyArray['requirednote'],
            'errors' => $smartyArray['errors'],
            'hidden' => $smartyArray['hidden']
        );
        
        if (isset($smartyArray['elements'])) {
            foreach ($smartyArray['elements'] as $element) {
                $finalArray[$element['name']] = array();
                foreach ($element as $key => $value) {
                    $finalArray[$element['name']][$key] = $value;
                }
                $this->renderAsHtml($finalArray[$element['name']]);
            }
        }
        
        return $finalArray;
    }
    
    /**
     * 
     * @param array $element
     */
    private function renderAsHtml(&$element)
    {
        switch ($element['type']) {
            case 'text':
            case 'password':
            default:
                $element['input'] = $this->renderHtmlInput($element);
                $element['label'] = $this->renderHtmlLabel($element);
                $element['html'] = $this->renderFinalHtml($element);
                break;
            
            case 'textarea':
                $element['input'] = $this->renderHtmlTextarea($element);
                $element['label'] = $this->renderHtmlLabel($element);
                $element['html'] = $this->renderFinalHtml($element);
                break;
            
            case 'button':
            case 'submit':
            case 'reset':
                $element['input'] = $this->renderHtmlButton($element);
                $element['label'] = "";
                $element['html'] = $this->renderFinalHtml($element);
                break;
            
            case 'static':
                $className = "\\Centreon\\Core\\Form\\Custom\\".ucfirst($element['label_type']);
                if (class_exists($className) && method_exists($className, 'renderHtmlInput')) {
                    $element['label'] = $element['label_label'];
                    $in = $className::renderHtmlInput($element);
                    if (isset($in['html'])) {
                        $element['input'] = $in['html'];
                    }
                    $element['label'] = $this->renderHtmlLabel($element);
                    $element['html'] = $this->renderFinalHtml($element);
                    if (isset($in['js'])) {
                        $this->tpl->addCustomJs($in['js']);
                    }
                }
                break;
            
            case 'checkbox':
                $element['input'] = $this->renderHtmlCheckbox($element);
                $element['label'] = $this->renderHtmlLabel($element);
                $element['html'] = $this->renderFinalHtml($element);
                break;
            
            case 'radio':
                $element['input'] = $this->renderHtmlRadio($element, $element['name']);
                $element['label'] = $this->renderHtmlLabel($element);
                $element['html'] = $this->renderFinalHtml($element);
                break;
            
            case 'group':
                $selectedValues = array();
                if (is_array($element['value'])) {
                    $selectedValues = array_keys($element['value']);
                }
                $element['input'] = '<div class="input-group">';
                foreach($element['elements'] as $groupElement) {
                    if ($groupElement['type'] == 'checkbox') {
                        $element['input'] .= $this->renderHtmlCheckbox($groupElement);
                    } else {
                        $currentElementName = substr($groupElement['name'], strlen($element['name'])+1, -1);
                        $selected = in_array($currentElementName, $selectedValues);
                        $element['input'] .= $this->renderHtmlRadio($groupElement, $element['name'], $selected).'&nbsp;&nbsp;';
                    }
                }
                $element['input'] .= '</div>';
                $element['label'] = $this->renderHtmlLabel($element);
                $element['html'] = $this->renderFinalHtml($element);
                break;
        }
    }
    
    public function renderFinalHtml($inputElement)
    {
        return '<div class="form-group">'.
                '<div class="col-sm-3" style="text-align:right">'.$inputElement['label'].'</div>'.
                '<div class="col-sm-6">'.$inputElement['input'].'</div>'.
                '<div class="col-sm-3"><input type="text" disabled="disabled" value="inherited" /></div>'.
                '</div>';
    }
    
    
    /**
     * 
     * @param array $inputElement
     * @return string
     */
    public function renderHtmlLabel($inputElement)
    {
        if (!isset($inputElement['label']) || (isset($inputElement['label']) && empty($inputElement['label']))) {
            $inputElement['label'] = $inputElement['name'];
        }
        
        if (!isset($inputElement['id']) || (isset($inputElement['id']) && empty($inputElement['id']))) {
            $inputElement['id'] = $inputElement['name'];
        }
        
        $inputHtml = '<label class="label-controller" for="'.$inputElement['id'].'">'.$inputElement['label'].'</label>';
        
        return $inputHtml;
    }
    
    /**
     * 
     * @param array $inputElement
     * @param string $parentName
     * @param boolean $selected
     * @return string
     */
    public function renderHtmlRadio($inputElement, $parentName, $selected = false)
    {
        (isset($inputElement['value']) ? $value = 'value="'.$inputElement['value'].'" ' :  $value = '');
        
        $htmlSelected = '';
        if ($selected) {
            $htmlSelected = 'checked=checked';
        }
        
        if (!isset($inputElement['label']) || (isset($inputElement['label']) && empty($inputElement['label']))) {
            $inputElement['label'] = $inputElement['name'];
        }
        
        if (!isset($inputElement['id']) || (isset($inputElement['id']) && empty($inputElement['id']))) {
            $inputElement['id'] = $inputElement['name'];
        }
        
        $inputHtml = '<label class="label-controller" for="'.$inputElement['id'].'">&nbsp;'.
                        '<input '.'id="'.$inputElement['id'].'" '.
                        'type="'.$inputElement['type'].'" '.'name="'.$parentName.'" '.
                        $value.' '.$htmlSelected.' '.
                        '/>'.' '.$inputElement['label'].
                        '</label>';
        return $inputHtml;
    }
    
    /**
     * 
     * @param array $inputElement
     * @return string
     */
    public function renderHtmlButton($inputElement)
    {
        (isset($inputElement['value']) ? $value = 'value="'.$inputElement['value'].'" ' :  $value = '');
        
        if (!isset($inputElement['id']) || (isset($inputElement['id']) && empty($inputElement['id']))) {
            $inputElement['id'] = $inputElement['name'];
        }
        
        $inputHtml = '<input '.
                            'id="'.$inputElement['id'].'" '.
                            'type="'.$inputElement['type'].'" '.
                            'name="'.$inputElement['name'].'" '.
                            $value.
                            'class="btn btn-default" '.
                            '/>';
        return $inputHtml;
    }
    
    /**
     * 
     * @param array $inputElement
     * @return string
     */
    public function renderHtmlTextarea($inputElement)
    {
        (isset($inputElement['value']) ? $value = $inputElement['value']:  $value = '');
        
        if (!isset($inputElement['label']) || (isset($inputElement['label']) && empty($inputElement['label']))) {
            $inputElement['label'] = $inputElement['name'];
        }
        
        if (!isset($inputElement['placeholder']) || (isset($inputElement['placeholder']) && empty($inputElement['placeholder']))) {
            $placeholder = 'placeholder="'.$inputElement['name'].'" ';
        }
        
        if (!isset($inputElement['id']) || (isset($inputElement['id']) && empty($inputElement['id']))) {
            $inputElement['id'] = $inputElement['name'];
        }
        
        $inputHtml = '<textarea '.
                            'id="'.$inputElement['id'].'" '.
                            'name="'.$inputElement['name'].'" '.
                            'class="form-control" '.
                            'rows="3" '.
                            $placeholder.
                            '>'.$value.'</textarea>';
        return $inputHtml;
    }
    
    /**
     * 
     * @param array $inputElement
     * @param boolean $useValue
     * @param boolean $usePlaceholder
     * @return string
     */
    public function renderHtmlCheckbox($inputElement)
    {
        (isset($inputElement['value']) && $inputElement['value']) ? $value = 'checked=checked' :  $value = '';
        
        if (!isset($inputElement['label']) || (isset($inputElement['label']) && empty($inputElement['label']))) {
            $inputElement['label'] = $inputElement['name'];
        }
        
        if (!isset($inputElement['id']) || (isset($inputElement['id']) && empty($inputElement['id']))) {
            $inputElement['id'] = $inputElement['name'];
        }
        
        (isset($inputElement['value']) ? $value = 'value="'.$inputElement['value'].'" ' :  $value = '');
        
        $inputHtml = '<label class="label-controller" for="'.$inputElement['id'].'">&nbsp;'.
                        '<input '.
                        'id="'.$inputElement['id'].'" '.
                        'type="'.$inputElement['type'].'" '.
                        'name="'.$inputElement['name'].'" '.
                        $value.' '.
                        '/>'.' '.$inputElement['label'].
                        '</label>&nbsp;';
        return $inputHtml;
    }


    /**
     * 
     * @param array $inputElement
     * @param boolean $useValue
     * @param boolean $usePlaceholder
     * @return string
     */
    public function renderHtmlInput($inputElement, $useValue = true, $usePlaceholder = true, $selected = false)
    {
        ((isset($inputElement['value']) && $useValue) ? $value = 'value="'.$inputElement['value'].'" ' :  $value = '');
        
        if (!isset($inputElement['label']) || (isset($inputElement['label']) && empty($inputElement['label']))) {
            $inputElement['label'] = $inputElement['name'];
        }
        
        if ($usePlaceholder) {
            if (!isset($inputElement['placeholder']) || (isset($inputElement['placeholder']) && empty($inputElement['placeholder']))) {
                $placeholder = 'placeholder="'.$inputElement['name'].'" ';
            }
        } else {
            $placeholder = '';
        }
        
        if (!isset($inputElement['id']) || (isset($inputElement['id']) && empty($inputElement['id']))) {
            $inputElement['id'] = $inputElement['name'];
        }
        
        $inputHtml = '<input '.
                        'id="'.$inputElement['id'].'" '.
                        'type="'.$inputElement['type'].'" '.
                        'name="'.$inputElement['name'].'" '.
                        $value.
                        'class="form-control" '.
                        $placeholder.
                        '/>';
        return $inputHtml;
    }

    /**
     * 
     */
    private function addSecurity()
    {
        $token = self::getSecurityToken();
        $this->addHidden('token', $token);
    }
    
    /**
     * 
     * @param type $token
     * @return boolean
     * @throws Exception
     */
    public static function validateSecurity($token)
    {
        if (isset($_SESSION['form_token']) && isset($_SESSION['form_token_time'])) {
            if ($token == $_SESSION['form_token']) {
                $oldTimestamp = time() - (15*60);
                if ($_SESSION['form_token_time'] < $oldTimestamp) {
                    throw new Exception;
                }
            } else {
                throw new Exception;
            }
        } else {
            throw new Exception;
        }
        
        return true;
    }

    /**
     * 
     * @return boolean
     * @throws Exception
     */
    private function checkSecurity()
    {
        $submittedToken = $this->formProcessor->getSubmitValue('token');
        return self::validateSecurity($submittedToken);
    }
    
    /**
     * 
     * @return string
     */
    public static function getSecurityToken()
    {
        $token = md5(uniqid(Di::getDefault()->get('config')->get('global', 'secret'), true));
        $_SESSION['form_token'] = $token;
        $_SESSION['form_token_time'] = time();
        return $token;
    }

    /**
     * 
     * @param string $name
     * @param string $fieldType
     * @param array $additionalParameters
     */
    public function add($name, $fieldType = 'text', $label = "", $additionalParameters = array())
    {
        if (empty($label)) {
            $label = $name;
        }
        
        switch (strtolower($fieldType)) {
            case 'button':
                $this->checkParameters($additionalParameters, array('params' => array()));
                $this->addButton($name, $label, $additionalParameters['params']);
                break;
            case 'checkbox':
                $this->checkParameters(
                    $additionalParameters,
                    array(
                        'params' => array('value' => $name),
                        'separators' => "&nbsp;",
                    )
                );
                $this->addCheckBox($name, $label, $additionalParameters['separators'], $additionalParameters['params']);
                break;
            case 'hidden':
                $this->checkParameters($additionalParameters, array('value' => ''));
                $this->formProcessor->addElement('hidden', $name, $additionalParameters['value']);
                break;
            case 'radio':
                $this->checkParameters(
                    $additionalParameters,
                    array(
                        'elements' => array(),
                        'separators' => "&nbsp;",
                        'defaultValue' => null
                    )
                );
                $this->addRadio(
                    $name,
                    $label,
                    $additionalParameters['separators'],
                    $additionalParameters['elements'],
                    $additionalParameters['defaultValue']
                );
                break;
            case 'reset':
                $this->checkParameters($additionalParameters, array('params' => array()));
                $this->addReset($name, $label, $additionalParameters['params']);
                break;
            case 'select':
                $this->checkParameters(
                    $additionalParameters,
                    array(
                        'multiple' => false,
                        'data' => array(),
                        'style' => null
                    )
                );
                if ($additionalParameters['multiple']) {
                    $this->addMultiSelect(
                        $name,
                        $label,
                        $additionalParameters['data']
                    );
                } else {
                    $this->addSelect(
                        $name,
                        $label,
                        $additionalParameters['data'],
                        $additionalParameters['style']
                    );
                }
                break;
            case 'submit':
                $this->checkParameters($additionalParameters, array('params' => array()));
                $this->addSubmit($name, $label, $additionalParameters['params']);
                break;
            case 'submitbar':
                $this->checkParameters($additionalParameters, array('cancel' => true));
                $this->addSubmitBar($name = 'submitbar', $additionalParameters['cancel']);
                break;
            case 'textarea':
                $this->addTextarea($name, $label);
                break;
            default:
            case 'text':
                $this->checkParameters(
                    $additionalParameters,
                    array(
                        'style' => null,
                        'placeholder' => null,
                        'help' => null
                    )
                );
                $this->addText(
                    $name,
                    $label,
                    $additionalParameters['style'],
                    $additionalParameters['placeholder'],
                    $additionalParameters['help']
                );
                break;
        }
        
        return $this;
    }
    
    /**
     * 
     * @param type $given
     * @param type $mandatory
     */
    private function checkParameters(&$given, $mandatory)
    {
        foreach ($mandatory as $field => $value) {
            if (!isset($given[$field])) {
                $given[$field] = $value;
            }
        }
    }
    
    /**
     * 
     * @param string $name
     * @param string $value
     */
    public function addHidden($name, $value)
    {
        $this->formProcessor->addElement('hidden', $name, $value);
    }
    
    /**
     * Add a input text element
     *
     * @param string $name The name and the id of element
     * @param string $label The label of element
     * @param string|null $style The input style (prefix by input-)
     *                           if null the style is medium
     * @return \HTML_QuickForm_Element_InputText
     */
    public function addText($name, $label, $style = null, $placeholder = null, $help = null)
    {
        if (is_null($style)) {
            $style = "medium";
        }
        $param = array();
        if (!is_null($placeholder)) {
            $param['placeholder'] = $placeholder;
        }
        if (!is_null($help)) {
            $param['_help'] = $help;
        }
        $elem = $this->formProcessor
            ->addElement('text', $name, $label ,$param)
            ->updateAttributes(
                array(
                    'id'=>$name,
                    'class' => "input-".$style,
                    'label' => $label
                )
            );
        return $elem;
    }

    /**
     * Add a select
     *
     * @param string $name The name and the id of element
     * @param string $label The label of element
     * @param array $data The list for options
     * @param string|null $style The input style (prefix by input-)
     *                           if null the style is medium
     * @return \HTML_QuickForm_Element_Select
     */
    public function addSelect($name, $label, $datas, $urlCastParemeter)
    {
        $selectParameters = json_decode($datas, true);
        
        if (isset($selectParameters['type']) && $selectParameters['type'] == 'object') {
            if (isset($selectParameters['defaultValuesRouteParams'])) {
                
            }
            $selectParameters['defaultValuesRoute'] = \Centreon\Core\Di::getDefault()
                            ->get('router')
                            ->getPathFor($selectParameters['defaultValuesRoute'], $urlCastParemeter);
            
            if (isset($selectParameters['listValuesRouteParams'])) {
                
            }
            $selectParameters['listValuesRoute'] = \Centreon\Core\Di::getDefault()
                            ->get('router')
                            ->getPathFor($selectParameters['listValuesRoute'], $urlCastParemeter);
        }
        
        $selectParameters['label'] = $label;
        
        $elem = $this->formProcessor->addElement('static', $name, $selectParameters);
        $elem->setValue($selectParameters);
        
        return $elem;
    }
    
    /**
     * Add custom inputs
     *
     * @param array $field
     * @param array $extraParams
     */
    public function addStatic($field, $extraParams = array())
    {
        $params = array();
        if (isset($field['attributes']) && $field['attributes']) {
            $params = json_decode($field['attributes'], true);
        }
        $params['label'] = $field['label'];
        $params['type'] = $field['type'];
        $params['extra'] = $extraParams;
        $elem = $this->formProcessor->addElement('static', $field['name'], $params);
    }

    /**
     * Add a multiselect
     *
     * @param string $name The name and the id of element
     * @param string $label The label of element
     * @param array $data The list for options
     * @param string|null $style The input style (prefix by input-)
     *                           if null the style is medium
     * @return \HTML_QuickForm_Element_Select
     */
    public function addMultiSelect($name, $label, $data)
    {
        $this->tpl->addCss('jquery-chosen.css');
        $this->tpl->addJs('jquery/chosen/chosen.jquery.min.js');
        $this->tpl->addJs('centreon/formMultiSelect.js');
        $elem = $this->formProcessor
                    ->addElement('select', $name, array('multiple' => 'multiple'))
                    ->updateAttributes(
                        array(
                            'id'=>$name,
                            'class'=>'chzn-select',
                            'label'=>$label
                        )
                    );
        return $elem;
    }
    
    /**
     * Add a checkbox to the form
     *
     * @param string $name The name and the id of element
     * @param string $label The label of element
     * @param array $params The list of options in option group
     * @return \HTML_QuickForm_Container_Group
     */
    public function addCheckBox($name, $label, $separators = '&nbsp;', $params = array())
    {
        
        if (isset($params['list']) && count($params['list'])) {
            $cbList = array();
            foreach ($params['list'] as $cb) {
                $cbList[] = $this->formProcessor->createElement(
                    "checkbox",
                    $cb['name'],
                    $cb['label'],
                    $cb['label']
                );
            }
            $cbg = $this->formProcessor->addGroup($cbList, $name, $label, $separators);
        } else {
            $cbg = $this->formProcessor->addElement(
                'checkbox',
                $name,
                $label,
                $label
            );
        }
        return $cbg;
    }
    
    /**
     * 
     * @param string $name
     * @param string $label
     * @param string $separators
     * @param array $params
     * @return type
     */
    public function addRadio($name, $label, $value, $separators = '&nbsp;', $params = array())
    {
        if (isset($params['list']) && count($params['list'])) {
            $cbList = array();
            foreach ($params['list'] as $cb) {

                $cbList[] = $this->formProcessor->createElement(
                    'radio',
                    $cb['name'],
                    $cb['label'],
                    $separators,
                    $cb['value']
                );
            }
            $cbg = $this->formProcessor->addGroup($cbList, $name, $label, $separators);
        } else {
            $cbg = $this->formProcessor->addElement(
                'radio',
                $name,
                $label,
                null,
                $value
            );
        }
        return $cbg;
    }

    /**
     * Add a textarea to the form
     *
     * @param string $name The name and the id of element
     * @param string $label The label of element
     * @return \HTML_QuickForm_Element_Textarea
     */
    public function addTextarea($name, $label)
    {
        $elem = $this->formProcessor
                    ->addElement('textarea', $name, $label, $this->template['textarea'])
                    ->updateAttributes(array('id'=>$name, 'label'=>$label));
        return $elem;
    }
    
    /**
     * Add a button to the form
     *
     * @param string $name The name of button
     * @param string $value The value of button
     * @param array $param Additionnal param
     * @return \HTML_QuickForm_Element_Button
     */
    public function addButton($name, $label, $params = array())
    {
        $params['id'] = $name;
        $this->formProcessor->addElement('button', $name, $label, $params);
    }

    /**
     * Add a submit to the form
     *
     * @param string $name The name of submit
     * @param string $value The value of submit
     * @param array $param Additionnal param
     * @return \HTML_QuickForm_Element_InputSubmit
     */
    public function addSubmit($name, $label, $params = array())
    {
        $this->formProcessor->addElement('submit', $name, $label, $params)
                ->updateAttributes(array('id'=>$name, 'class'=>'btn-primary'));
    }

    /**
     * Add a reset to the form
     *
     * @param string $name The name of reset
     * @param string $value The value of reset
     * @param array $param Additionnal param
     * @return \HTML_QuickForm_Element_InputReset
     */
    public function addReset($name, $label, $params = array())
    {
        $elem = $this->formProcessor
                        ->addElement('reset', $name, $label, $params)
                        ->updateAttributes(array('id'=>$name));
        return $elem;
    }
    
    /**
     * Add the submit bar to a form
     * 
     * @param string $name The name of bar
     * @param boolean $cancel If include the cancel button
     * @return Centreon_SubmitBar
     */
    public function addSubmitBar($name = 'submitbar', $cancel = true)
    {
        $submitbar = $this->formProcessor
                            ->addElement('submitbar', $name)
                            ->updateAttributes(array('id'=>$name));
        
        $submitbar
            ->addElement('submit', 'submit')
            ->updateAttributes(array('id'=>'submit', 'label'=>_('Save changes'), 'class'=>'btn-primary'));
        
        if ($cancel) {
            $submitbar
                ->addElement('reset', 'reset')
                ->updateAttributes(array('id'=>'reset', 'label'=>_('Cancel')));
        }
        
        return $submitbar;
    }
    
    /**
     * Add clonable element
     * 
     * @param string $type
     * @param string $name
     * @param string $label
     * @param array $options
     * @param string $style
     * @return \HTML_QuickForm_Element
     */
    public function addClonableElement($type, $name, $label, $options = array(), $style = null)
    {
        switch (strtolower($type)) {
            case 'text':
                $elem = $this->addText($name, $label, $style);
                break;
            case 'select':
                $elem = $this->addSelect($name, $label, $options, $style);
                break;
            case 'checkbox':
                $elem = $this->addCheckBox($name, $label, $options);
                break;
            default:
                throw new Centreon_Exception_Core('Element type cannot be cloned');
        }
        
        $elem
            ->updateAttributes(array('id'=>$name."_#index#"))
            ->setName($name."[#index#]");
        
        return $elem;
    }
    
    /**
     * Add a tab into the form
     *
     * @param string $id The tab id
     * @param string $label The tab label
     * @return QuickForm_Container_Tab
     */
    public function addTab($id, $label)
    {
        return $this->formProcessor
                    ->addElement('tabs', $label)
                    ->updateAttributes(array('id'=>$id, 'label'=>$label));
    }
    
    /**
     * 
     * @param type $title
     * @param type $label
     */
    public function addHeader($title, $label)
    {
        $this->formProcessor->addElement('header', $title, $label);
    }

    /**
     * Add a fieldset into the form
     *
     * @param string $label The legend
     * @return \HTML_QuickForm_Container_Fieldset
     */
    public function addFieldSet($label)
    {
        return $this->formProcessor
                    ->addElement('fieldset', $label)
                    ->setLabel($label);
    }

    /**
     * Return the array for smarty
     * 
     * @return type
     */
    public function display()
    {
        $this->setDefaults();
        $renderer = \HTML_QuickForm_Renderer::factory('centreon');
        $this->formProcessor->render($renderer);

        $this->formProcessor->addRecursiveFilter("trim");
        
        return $this->rulesToArray($renderer->toArray());
    }

    /**
     * Add help by block
     *
     * The array help format :
     *
     *     array(
     *       'elementname' => 'help string'
     *     )
     *
     * @param array $helps The helps
     */
    public function addHelps($helps)
    {
        foreach ($helps as $element => $help) {
            $formEl = $this->formProcessor->getElementsByName($element);
            if (count($formEl) > 0) {
                $formEl[0]->setAttribute('_help', $help);
            }
        }
    }

    /**
     * Register a rule
     * 
     * @param string $name The rule name
     * @param string|array $function The callback
     */
    public function registerRule($name, $function)
    {
        \HTML_QuickForm_Factory::registerRule(
            $name,
            '\HTML_QuickForm_Rule_Callback',
            'HTML/QuickForm/Rule/Callback.php',
            $function
        );
    }

    /**
     * Register a javascript rule
     *
     * @param string $name The rule name
     * @param string $file The javascript file who add the rule
     */
    public function registerJsRule($name, $file)
    {
        if (!in_array($name, $this->jsRulesRegister)) {
            $this->tpl->addJs($file);
            $this->jsRulesRegister[] = $name;
        }
    }


    /**
     * Add rule for form
     *
     * @param string $ruleName The rule name
     * @param string $field The field name
     * @param string $msg The message
     * @param string|null $jsExt Extended information for javascript
     */
    public function addRule($ruleName, $field, $msg, $jsExt = null)
    {
        /* If Quickform rule exists */
        if (\HTML_QuickForm_Factory::isRuleRegistered($ruleName)) {
            $elements = $this->formProcessor->getElementsByName($field);
            foreach ($elements as $element) {
                $this->formProcessor->addRule($ruleName, $msg);
            }
        }
        /* If javascript rule exists */
        if (in_array($ruleName, $this->jsRulesRegister)) {
            if (is_null($jsExt)) {
                $jsExt = 'true';
            }
            $this->jsRules[$field][] = array(
                'rule' => $ruleName,
                'message' => $msg,
                'info' => $jsExt
            );
            /* Add javascript for initialize the form rules */
            $this->tpl->addJs('jquery/validate/jquery.validate.min.js')
                      ->addJs('centreon/formRules.js');
        }
    }

    /**************************************/

    public function addMassiveChangeUpdateOption($name, $defaultValue, $o)
    {
        if ($o == "mc") {
            $this->formProcessor->addElementRadio(
                $name,
                _("Update mode"),
                array(
                    0 => _("Incremental"),
                    1 => _("Replacement")
                ),
                $defaultValue
            );
        }
    }

    public function applyFilter($field, $function)
    {
        //$this->formProcessor->applyFilter($field, array($this, $function));
        //$this->formProcessor->addFilter($field, array($this, $function));
    }
     
    public function setDefaults($defaultValues = null, $filter = null)
    {
       $this->formProcessor->setDefaults($defaultValues, $filter);
    }

    /**
     * Enable or Disable freeze status
     * @params boolean
     *
     */
    public function freeze($bool = 1)
    {
        $this->formProcessor->toggleFrozen($bool);
    }
    
    /**
     * Returns the element's value, possibly with filters applied
     *
     */
    public function getValue()
    {
        return $this->formProcessor->getValue();
    }
    
    /**
     * 
     * @param type $elem
     * @return string
     */
    public function getSubmitValue($elem = null)
    {
        if (!isset($elem)) {
            return $this->formProcessor->getSubmitValue();
        } else {
            return $this->formProcessor->getSubmitValue($elem);
        }
    }

    /**
     * 
     * @param type $elem
     * @return type
     */
    public function getSubmitValues($elem = null)
    {
        if (!isset($elem)) {
            return $this->formProcessor->getSubmitValues();
        } else {
            return $this->formProcessor->getSubmitValues($elem);
        }
    }
    
    /**
     * 
     */
    public function isSubmitted()
    {
        $this->formProcessor->isSubmitted();
    }

    /**
     * 
     * @param type $elem
     * @return type
     */
    public function getElement($elem)
    {
        return $this->formProcessor->getElement($elem);
    }
    
    /**
     * 
     * @return type
     */
    public function validate()
    {
        $this->checkSecurity();
        return $this->formProcessor->validate();
    }

    /**
     * 
     * @param type $field Specific rules
     * @return type
     */
    private function removeSpaces($field)
    {
        $ret = $this->formProcessor->getSubmitValues();
        return (str_replace(" ", "_", $ret[$field]));
    }

    /****************************************************/

    /**
     * Add javascript rules in end of form array
     *
     * @param array $array The quickform to array
     * @return array
     */
    private function rulesToArray($array)
    {
        $array['rules'] = $this->jsRules;
        return $array;
    }

    /**
     * Initialiaze the form templating style
     */
    private function init()
    {
        $this->template = array();
        $this->template['textarea'] = array('rows' => '6', 'cols' => '120');
        
        $this->style = array();
    
        $this->basicSeparator = '&nbsp;';
    }
}
