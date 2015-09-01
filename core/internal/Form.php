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

namespace Centreon\Internal;

use Centreon\Internal\Di;
use Centreon\Internal\Form\Component\Component;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
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
     * @var \Centreon\Template
     */
    private $tpl;
    
    /**
     *
     * @var type
     */
    private $template;

    /**
     *
     * @var \Centreon\Internal\Di 
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
     * Javascript rules 
     *
     * @var array
     */
    private $jsRules;
    
    /**
     *
     * @var array 
     */
    private $customValidator = array();
    
    /**
     *
     * @var string 
     */
    private $eventValidation = array(
        'validators' => array(),
        'formId' => '',
        'extraJs' => ''
    );
    
    /**
     *
     * @var string 
     */
    private $submitValidation = '';

    /** 
     *
     * @var string The form name
     */
    private $formName;
    
    /**
     * Constructor
     *
     * @param string $name The name of form
     * @param array $options The options
     */
    public function __construct($name, $options = null)
    {
        $this->formProcessor = new \HTML_QuickForm($name, 'post');
        $this->formName = $name;
        $this->options = $options;
        $this->init();
        $this->di = Di::getDefault();
        $this->tpl = $this->di->get('template');
    }
    
    /**
     * 
     * @return array
     */
    public function toSmarty()
    {
        $this->formRenderer = new \HTML_QuickForm_Renderer_ArraySmarty($this->tpl, true);
        $this->formRenderer->setRequiredTemplate('{label}<font color="red" size="1">*</font>');
        $this->formRenderer->setErrorTemplate('<font color="red">{error}</font><br />{html}');
        $this->formProcessor->accept($this->formRenderer);
        $smartyArrayFormat = $this->formatForSmarty();
        $this->tpl->assign('eventValidation', $this->eventValidation);
        $this->tpl->assign('submitValidation', $this->submitValidation);
        return $smartyArrayFormat;
    }
    
    /**
     * 
     * @return array
     */
    public function getCustomValidator()
    {
        return $this->customValidator;
    }
    
    /**
     * 
     * @return string
     */
    public function getAjaxValidator()
    {
        return $this->ajaxValidator;
    }

    /**
     * Set the form ID
     *
     * @param string $formId The new form ID
     */
    public function setFormId($formId)
    {
        if ($this->formName != $formId) {
            $this->eventValidation['formId'] = $formId;
        }
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

        if ($this->eventValidation['formId'] === '') {
            $this->eventValidation['formId'] = $this->formName;
        }
        
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
            default:
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
            case 'radio':
                $element['input'] = $this->renderHtmlRadio($element);
                $element['label'] = "";
                $element['html'] = $this->renderFinalHtml($element);
                break;
            case 'checkbox':
                $element['input'] = $this->renderHtmlCheckbox($element);
                $element['label'] = "";
                $element['html'] = $this->renderFinalHtml($element);
                break;
            case 'static':
                $className = Component::parseComponentName($element['label_type']);
                if (class_exists($className) && method_exists($className, 'renderHtmlInput')) {
                    $element['label'] = $element['label_label'];
                    $in = $className::renderHtmlInput($element);
                    $inVal = $className::addValidation($element);
                    
                    if (isset($in['html'])) {
                        $element['input'] = $in['html'];
                    }
                    if (isset($in['css'])) {
                        $element['css'] = $in['css'];
                    }
                    if (isset($in['extrahtml'])) {
                        $element['extrahtml'] = $in['extrahtml'];
                    }
                    $element['label'] = $this->renderHtmlLabel($element);
                    $element['html'] = $this->renderFinalHtml($element);
                    if (isset($in['js'])) {
                        $this->tpl->addCustomJs($in['js']);
                    }
                    
                    if (isset($inVal['eventValidation'])) {
                        if (isset($inVal['eventValidation']['extraJs'])) {
                            $this->eventValidation['extraJs'] .= $inVal['eventValidation']['extraJs'];
                            unset($inVal['eventValidation']['extraJs']);
                        }
                        $this->eventValidation['validators'] = array_merge(
                            $this->eventValidation['validators'],
                            $inVal['eventValidation']
                        );
                    }
                    
                    if (isset($inVal['submitValidation'])) {
                        $this->submitValidation .= $inVal['submitValidation'];
                    }
                }
                break;
        }
    }
    
    /**
     * 
     * @param array $inputElement
     * @return string
     */
    public function renderHtmlButton($inputElement)
    {
        if (!isset($inputElement['value'])) {
            $inputElement['value'] = '';
        }

        if (!isset($inputElement['id']) || (isset($inputElement['id']) && empty($inputElement['id']))) {
            $inputElement['id'] = $inputElement['name'];
        }

        $tpl = Di::getDefault()->get('template');

        $tpl->assign('inputElement', $inputElement);

        return $tpl->fetch('file:[Core]/form/component/button.tpl');
    }
    
    /**
     * 
     * @param array $inputElement
     * @return string
     */
    public function renderHtmlRadio($inputElement)
    {
        if (!isset($inputElement['value'])) {
            $inputElement['value'] = '';
        }

        if (!isset($inputElement['id']) || (isset($inputElement['id']) && empty($inputElement['id']))) {
            $inputElement['id'] = $inputElement['name'];
        }
        
        $tpl = Di::getDefault()->get('template');

        $tpl->assign('inputElement', $inputElement);

        return $tpl->fetch('file:[Core]/form/component/radio.tpl');
    }
    /**
     * 
     * @param array $inputElement
     * @return string
     */
    public function renderHtmlCheckbox($inputElement)
    {
        if (!isset($inputElement['value'])) {
            $inputElement['value'] = '';
        }

        if (!isset($inputElement['id']) || (isset($inputElement['id']) && empty($inputElement['id']))) {
            $inputElement['id'] = $inputElement['name'];
        }
        
        $tpl = Di::getDefault()->get('template');

        $tpl->assign('inputElement', $inputElement);

        return $tpl->fetch('file:[Core]/form/component/radio.tpl');
    }
    
    /**
     * 
     * @param type $inputElement
     * @return type
     */
    public function renderFinalHtml($inputElement)
    {
        $helpButton = '';
        $classInput = '';
        $classAdvanced = '';
        $extraHtml = '';
        if ($inputElement['type'] !== 'submit') {
            $helpButton = $this->renderHelp($inputElement);
            $classInput = 'col-md-12';
        }
        if (isset($inputElement['css'])) {
            $classInput = $inputElement['css'];
        }
        if (isset($inputElement['extrahtml'])) {
            $extraHtml = $inputElement['extrahtml'];
        }
        
        if (isset($inputElement['label_advanced']) && $inputElement['label_advanced'] == '1') {
            $classAdvanced = 'advanced';
        }
        
        return '<div class="form-group ' . $classAdvanced . '">'.
                $inputElement['label'].
                $inputElement['input']. $extraHtml .
                '<div class="inheritance" id="' . $inputElement['name'] . '_inheritance"></div>'.
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
        
        $tpl = Di::getDefault()->get('template');

        $tpl->assign('inputElement', $inputElement);

        return $tpl->fetch('file:[Core]/form/component/label.tpl');
    }

     /**
         *
         * @param type $inputElement
         * @return string
         */
        private function renderHelp($inputElement)
        {
            $helpButton = '';

            return $helpButton;
        }
    
    /**
     * Add a submit to the form
     *
     * @param string $name The name of submit
     * @param string $label The value of submit
     * @param array $params Additionnal param
     * @return \HTML_QuickForm_Element_InputSubmit
     */
    public function addSubmit($name, $label, $params = array())
    {
        $this->formProcessor->addElement('submit', $name, $label, $params)
                ->updateAttributes(array('id'=>$name, 'class'=>'btn-primary'));
    }
    
    /**
     * 
     * @param type $origin
     * @param type $uri
     * @return type
     */
    public static function getValidatorsQuery($origin, $uri)
    {
        $di = Di::getDefault();
        $baseUrl = $di->get('config')->get('global', 'base_url');
        $uri = substr($uri, strlen($baseUrl));
        switch ($origin) {
            default:
            case 'form':
                $validatorsQuery = "SELECT
                        fv.`name` as validator_name, `action` as `validator`,
                        ff.`name` as `field_name`, ff.`label` as `field_label`
                    FROM
                        cfg_forms_validators fv, cfg_forms_fields_validators_relations ffv, cfg_forms_fields ff
                    WHERE
                        ffv.validator_id = fv.validator_id
                    AND
                        ff.field_id = ffv.field_id
                    AND
                        ffv.field_id IN (
                            SELECT
                                fi.field_id
                            FROM
                                cfg_forms_fields fi, cfg_forms_blocks fb, cfg_forms_blocks_fields_relations fbf, cfg_forms_sections fs, cfg_forms f
                            WHERE
                                fi.field_id = fbf.field_id
                            AND
                                fbf.block_id = fb.block_id
                            AND
                                fb.section_id = fs.section_id
                            AND
                                fs.form_id = f.form_id
                            AND
                                f.route = '$uri'
                    );";
                break;
            case 'wizard':
                $validatorsQuery = "SELECT
                        fv.`name` as validator_name, `action` as `validator`, ff.`name` as `field_name`,
                        ff.`label` as `field_label`
                    FROM
                        cfg_forms_validators fv, cfg_forms_fields_validators_relations ffv, cfg_forms_fields ff
                    WHERE
                        ffv.validator_id = fv.validator_id
                    AND
                        ff.field_id = ffv.field_id
                    AND
                        ffv.field_id IN (
                            SELECT
                                fi.field_id
                            FROM
                                cfg_forms_fields fi, cfg_forms_steps fs, cfg_forms_steps_fields_relations fsf, cfg_forms_wizards fw
                            WHERE
                                fi.field_id = fsf.field_id
                            AND
                                fsf.step_id = fs.step_id
                            AND
                                fs.wizard_id = fw.wizard_id
                            AND
                                fw.route = '$uri'
                    );";
                break;
        }
        return $validatorsQuery;
    }
    
    /**
     * 
     * @param type $origin
     * @param type $uri
     * @return type
     */
    public static function getValidators($origin, $uri)
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        // Check if we are in form or wizard
        $validatorsQuery = self::getValidatorsQuery($origin, $uri);
        
        $stmt = $dbconn->query($validatorsQuery);
        $validatorsRawList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $validatorsFinalList = array();
        foreach ($validatorsRawList as $validator) {
            $validatorsFinalList[$validator['field_name']][] = array(
                'call' => $validator['validator_name'],
                'label' => $validator['field_label']
            );
        }
        return $validatorsFinalList;
    }
    
    /**
     * 
     * @param type $field
     * @param type $extraParams
     * @return \Centreon\Internal\Form
     */
    public function add($field, $extraParams = array())
    {
        switch ($field['type']) {
            default:
                $this->addStatic($field, $extraParams);
                break;
            case 'radio':
                $values = json_decode($field['attributes']);
                $radioValues = array();
                foreach ($values as $label => $value) {
                    $radioValues['values'] = array(
                        'name' => $label,
                        'label' => $label,
                        'value' => $value
                    );
                }
                $this->addRadio(
                    $field['name'],
                    $field['label'],
                    $field['name'],
                    '&nbsp;',
                    $radioValues
                );
                break;
            case 'checkbox':
                $values = json_decode($field['attributes']);
                if (is_array($values) || is_object($values)) {
                    $checkboxValues = array();
                    foreach ($values as $label => $value) {
                        $checkboxValues['values'] = array(
                            'name' => $label,
                            'label' => $label,
                            'value' => $value
                        );
                    }
                    $this->addCheckBox(
                        $field['name'],
                        $field['label'],
                        '&nbsp;',
                        $checkboxValues
                    );
                } else {
                    $this->addCheckbox($field['name'], $field['label']);
                }
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
        $params['mandatory'] =  isset($field['mandatory']) ? $field['mandatory'] : '';
        $params['parent_field'] = isset($field['parent_field']) ? $field['parent_field'] : '';
        $params['parent_value'] = isset($field['parent_value']) ? $field['parent_value'] : '';
        $params['child_actions'] = isset($field['child_actions']) ? $field['child_actions'] : '';

        if(isset($field['show_label'])){
            $params['show_label'] = $field['show_label'];
        }
        if (isset($field['advanced']) && $field['advanced'] != null) {
            $params['advanced'] = $field['advanced'];
        }
        
        if (isset($field['help']) && $field['help'] != null) {
            $params['help'] = $field['help'];
        }
        
        if (isset($field['help_url']) && $field['help_url'] != null) {
            $params['help_url'] = $field['help_url'];
        }
        
        if (isset($field['validators']) && $field['validators'] != null) {
            $params['validators'] = $field['validators'];
        }
        $params['extra'] = $extraParams;

        $elem = $this->formProcessor->addElement('static', $field['name'], $params);
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
     * 
     * @param type $defaultValues
     * @param type $filter
     */
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
     * @param type $origin
     * @param type $uri
     * @param type $moduleName
     * @param type $submittedValues
     * @return type
     */
    public static function validate($origin, $uri, $moduleName, &$submittedValues)
    {
        $isValidate = true;
        $errorMessage = '';
        try {
            unset($submittedValues['token']);
            if (!isset($submittedValues['object_id'])) {
                $submittedValues['object_id'] = null;
            }
            
            $validatorsList = self::getValidators($origin, $uri);
            foreach ($validatorsList as $validatorKey => $validatorsForField) {
                $nbOfValidators = count($validatorsForField);
                for ($i=0; $i<$nbOfValidators; $i++) {
                    $validatorCall = '\Centreon\Internal\Form\Validator\\'.ucfirst($validatorsForField[$i]['call']);
                    $resultValidate = $validatorCall::validate(
                        $submittedValues[$validatorKey],
                        $moduleName,
                        $submittedValues['object'],
                        $submittedValues['object_id'],
                        $validatorKey
                    );
                    if (!$resultValidate['success']) {
                        $isValidate = false;
                        $errorMessage .= '<b>'
                            .$validatorsForField[$i]['label']
                            . '</b> : '
                            . $resultValidate['error']
                            . '<br />';
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            $isValidate = false;
            $errorMessage = $e->getMessage();
        }
        
        return array('success' => $isValidate, 'error' => $errorMessage);
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
    
    /**
     * Add custom radio
     *
     * @param array $field
     * @param array $extraParams
     */
    public function addRadio($name, $label, $id, $separateur, $radioValues)
    {        
        $params = array();
        
        if (!isset($id) || (isset($id) && empty($id))) {
            $id = $name;
        }
        
        $params['label'] = $label;
        $params['type'] = 'radio';

        if(isset($name)){
            $general_label = ucwords(str_replace('_', ' ', $name));
            $params['general_label'] = $general_label;
            $params['name'] = $name;
        }
               
        if (isset($id)) {
            $params['id'] = $id;
        }
        
        
        if (isset($radioValues['values'])) {
            $params['values'] = (array)$radioValues['values']['value'];
        }        
        $elem = $this->formProcessor->addElement('radio', $name, $params);
    }
    
    /**
     * 
     * @param type $name
     * @param type $label
     * @param type $separteur
     * @param array $checkboxValues
     */
    public function addCheckbox($name, $label, $separteur = '&nbsp;', $checkboxValues = array())
    {       
        $params['label'] = $label;
        $params['type'] = 'checkbox';

        if(isset($name)){
            $general_label = ucwords(str_replace('_', ' ', $name));
            $params['general_label'] = $general_label;
            $params['name'] = $name;
        }
                
        if (isset($checkboxValues['values'])) {
            $params['values'] = (array)$checkboxValues['values']['value'];
        }
                        
        $elem = $this->formProcessor->addElement('checkbox', $name, $params);
    }
    
    
    /**
     * 
     * @param integer $id
     */
    public function getValidatorsByField($id)
    {
        $di = Di::getDefault();
        $this->dbconn = $di->get('db_centreon');
        $validators = array();
        
        $validatorQuery = "SELECT v.route as validator_action, vr.params as params, vr.client_side_event as rules "
                    . "FROM cfg_forms_validators v, cfg_forms_fields_validators_relations vr "
                    . "WHERE vr.field_id = :fieldId "
                    . "AND vr.validator_id = v.validator_id";
        $validatorStmt = $this->dbconn->prepare($validatorQuery);

        $validatorStmt->bindParam(':fieldId', $id, \PDO::PARAM_INT);
        $validatorStmt->execute();
        while ($validator = $validatorStmt->fetch()) {
            $myvalidator['rules']  = $validator['rules'];
            $myvalidator['params'] = json_decode($validator['params'], true);
            $myvalidator['validator_action']  = $validator['validator_action'];
            $validators[] = $myvalidator;
        }

        return $validators;
    }
}
