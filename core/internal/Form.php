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

namespace Centreon\Internal;

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
    private $eventValidation = '';
    
    /**
     *
     * @var string 
     */
    private $submitValidation = '';
    
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
        $this->tpl->addCustomJs($this->eventValidation);
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
            case 'static':
                $className = "\\Centreon\\Internal\\Form\\Component\\".ucfirst($element['label_type']);
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
                        $this->eventValidation .= $inVal['eventValidation'];
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
     * @param type $inputElement
     * @return type
     */
    public function renderFinalHtml($inputElement)
    {
        $helpButton = '';
        $classInput = 'col-sm-9';
        $classAdvanced = '';
        $extraHtml = '';
        if ($inputElement['type'] !== 'submit') {
            $helpButton = $this->renderHelp($inputElement);
            $classInput = 'col-sm-9';
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
                '<div class="col-sm-2" style="text-align:right">'.$inputElement['label'].'</div>'.
                '<div class="'.$classInput.'">'.$inputElement['input'].'</div>'. $extraHtml .
                $helpButton.
                '</div>';
    }
    
    /**
     * 
     * @param type $inputElement
     * @return string
     */
    private function renderHelp($inputElement)
    {
        $helpButton = '';
        
        if (isset($inputElement['label_help'])) {
            $helpButton = '<div class="col-sm-1"><button id="'
                . $inputElement['name'] . '_help" type="button" class="btn btn-sm btn-info">?</button>'
                . '</div>';
            $helpBubble = '$("#' . $inputElement['name'] . '_help").qtip({
                                content: {
                                    text: "'.str_replace('"', '\"', $inputElement['label_help']).'",
                                    title: "'.$inputElement['label_label'].' Help",
                                    button: true
                                },
                                position: {
                                    my: "top right",
                                    at: "bottom left",
                                    target: $("#' . $inputElement['name'] . '_help") // my target
                                },
                                show: {
                                    event: "click",
                                    solo: "true"
                                },
                                style: {
                                    classes: "qtip-bootstrap"
                                },
                                hide: {
                                    event: "unfocus"
                                }
                            });';
            $this->tpl->addCustomJs($helpBubble);
        }
        
        return $helpButton;
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
        
        $mandatorySign = "";
        if (isset($inputElement['label_mandatory']) && $inputElement['label_mandatory'] == "1") {
            $mandatorySign .= ' <span style="color:red">*</span>';
        }
        
        $inputHtml = '<label class="label-controller" for="'.$inputElement['id'].'">'.$inputElement['label'].'</label>'.
            $mandatorySign;
        
        return $inputHtml;
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
                    $radioValues['list'][] = array(
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
                        $checkboxValues['list'][] = array(
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
        $params['mandatory'] = $field['mandatory'];
        
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
}
