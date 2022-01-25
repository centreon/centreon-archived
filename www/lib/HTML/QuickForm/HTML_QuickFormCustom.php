<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

class HTML_QuickFormCustom extends HTML_QuickForm
{

    /** @var bool */
    public $_tokenValidated = false;


    /**
     * Class constructor
     * @param    string $formName Form's name.
     * @param    string $method (optional)Form's method defaults to 'POST'
     * @param    string $action (optional)Form's action
     * @param    string $target (optional)Form's target defaults to '_self'
     * @param    mixed $attributes (optional)Extra attributes for <form> tag
     * @param    bool $trackSubmit (optional)Whether to track if the form was submitted by adding a special hidden field
     * @access   public
     */
    public function __construct($formName = '', $method = 'post', $action = '', $target = '', $attributes = null, $trackSubmit = false)
    {
        parent::__construct($formName, $method, $action, $target, $attributes, $trackSubmit);

        $this->addFormRule([$this, 'checkSecurityToken']);

        $this->loadCustomElementsInGlobal();
    }

    /**
     * Accepts a renderer
     *
     * @param object An HTML_QuickForm_Renderer object
     */
    public function accept(&$renderer)
    {
        $this->createSecurityToken();
        parent::accept($renderer);
    }

    /**
     * Creates a new form element of the given type.
     *
     * This method accepts variable number of parameters, their
     * meaning and count depending on $elementType
     *
     * @param     string     $elementType    type of element to add (text, textarea, file...)
     * @return    HTML_QuickForm_Element
     * @throws    HTML_QuickForm_Error
     */
    public function &createElement($elementType)
    {
        if ($elementType == 'radio') { // If element is radio we'll load our custom class type
            $elementType = 'radio_custom';
        } elseif ($elementType === 'checkbox') {
            $elementType = 'checkbox_custom';
        }

        $parentMethod = [get_parent_class($this), __FUNCTION__];
        $arguments = array_slice(func_get_args(), 1); // Get all arguments except the first one
        array_unshift($arguments, $elementType); // Add the modified element type name

        return call_user_func_array($parentMethod, $arguments);
    }

    /**
     * Create the CSRF Token to be set in every form using QuickForm
     */
    public function createSecurityToken()
    {
        if (!$this->elementExists('centreon_token')) {
            $token = bin2hex(openssl_random_pseudo_bytes(16));

            if (!isset($_SESSION['x-centreon-token']) || !is_array($_SESSION['x-centreon-token'])) {
                $_SESSION['x-centreon-token'] = array();
                $_SESSION['x-centreon-token-generated-at'] = array();
            }

            $_SESSION['x-centreon-token'][] = $token;
            $_SESSION['x-centreon-token-generated-at'][(string)$token] = time();

            $myTokenElement = $this->addElement('hidden', 'centreon_token');
            $myTokenElement->setValue($token);
        }
    }

    /**
     * Check if the CSRF Token is still valid
     *
     * @param array $submittedValues
     * @return boolean
     */
    public function checkSecurityToken($submittedValues)
    {
        $success = false;

        if ($this->_tokenValidated) {
            $success = true;
        } else {
            if (isset($submittedValues['centreon_token']) &&
                in_array($submittedValues['centreon_token'], $_SESSION['x-centreon-token'])
            ) {
                $elapsedTime =
                    time() - $_SESSION['x-centreon-token-generated-at'][(string)$submittedValues['centreon_token']];
                if ($elapsedTime < (15 * 60)) {
                    $key = array_search((string)$submittedValues['centreon_token'], $_SESSION['x-centreon-token']);
                    unset($_SESSION['x-centreon-token'][$key]);
                    unset($_SESSION['x-centreon-token-generated-at'][(string)$submittedValues['centreon_token']]);
                    $success = true;
                    $this->_tokenValidated = true;
                }
            }
        }

        if ($success) {
            $error = true;
        } else {
            $error = array('centreon_token' => 'The Token is invalid');
            echo "<div class='msg' align='center'>" .
                _("The form has not been submitted since 15 minutes. Please retry to resubmit") .
                "<a href='' OnLoad = windows.location(); alt='reload'> " . _("here") . "</a></div>";
        }

        $this->purgeToken();

        return $error;
    }

    /**
     * Empty all elapsed Token stored
     */
    public function purgeToken()
    {
        foreach ($_SESSION['x-centreon-token-generated-at'] as $key => $value) {
            $elapsedTime = time() - $value;

            if ($elapsedTime > (15 * 60)) {
                $tokenKey = array_search((string)$key, $_SESSION['x-centreon-token']);
                unset($_SESSION['x-centreon-token'][$tokenKey]);
                unset($_SESSION['x-centreon-token-generated-at'][(string)$key]);
            }
        }
    }

    /**
     * Adds a validation rule for the given field
     *
     * If the element is in fact a group, it will be considered as a whole.
     * To validate grouped elements as separated entities,
     * use addGroupRule instead of addRule.
     *
     * @param    string     $element       Form element name
     * @param    string     $message       Message to display for invalid data
     * @param    string     $type          Rule type, use getRegisteredRules() to get types
     * @param    string     $format        (optional)Required for extra rule data
     * @param    string     $validation    (optional)Where to perform validation: "server", "client"
     * @param    boolean    $reset         Client-side validation: reset the form element to its original value if there is an error?
     * @param    boolean    $force         Force the rule to be applied, even if the target form element does not exist
     * @throws   HTML_QuickForm_Error
     */
    public function addRule($element, $message, $type, $format = null, $validation = 'server', $reset = false, $force = false)
    {
        if (!$force) {
            if (!is_array($element) && !$this->elementExists($element)) {
                trigger_error("Element '$element' does not exist");
                return;
            } elseif (is_array($element)) {
                foreach ($element as $el) {
                    if (!$this->elementExists($el)) {
                        trigger_error("Element '$el' does not exist");
                        return;
                    }
                }
            }
        }
        if (false === ($newName = $this->isRuleRegistered($type, true))) {
            throw new HTML_QuickForm_Error("Rule '$type' is not registered", QUICKFORM_INVALID_RULE);
        } elseif (is_string($newName)) {
            $type = $newName;
        }
        if (is_array($element)) {
            $dependent = $element;
            $element   = array_shift($dependent);
        } else {
            $dependent = null;
        }
        if ($type == 'required' || $type == 'uploadedfile') {
            $this->_required[] = $element;
        }
        if (!isset($this->_rules[$element])) {
            $this->_rules[$element] = array();
        }
        if ($validation == 'client') {
            $this->updateAttributes(array('onsubmit' => 'try { var myValidator = validate_' . $this->_attributes['id'] . '; } catch(e) { return true; } return myValidator(this);'));
        }
        $this->_rules[$element][] = array(
            'type'        => $type,
            'format'      => $format,
            'message'     => $message,
            'validation'  => $validation,
            'reset'       => $reset,
            'dependent'   => $dependent
        );
    }


    /**
     * Applies a data filter for the given field(s)
     *
     * @param    mixed     $element       Form element name or array of such names
     * @param    mixed     $filter        Callback, either function name or array(&$object, 'method')
     * @throws   HTML_QuickForm_Error
     */
    public function applyFilter($element, $filter)
    {
        if (!is_callable($filter)) {
            trigger_error("Callback function '$filter' does not exist");
        }
        if ($element == '__ALL__') {
            $this->_submitValues = $this->_recursiveFilter($filter, $this->_submitValues);
        } else {
            if (!is_array($element)) {
                $element = array($element);
            }
            foreach ($element as $elName) {
                $value = $this->getSubmitValue($elName);
                if (null !== $value) {
                    if (false === strpos($elName, '[')) {
                        $this->_submitValues[$elName] = $this->_recursiveFilter($filter, $value);
                    } else {
                        $idx  = "['" . str_replace(
                            array('\\', '\'', ']', '['),
                            array('\\\\', '\\\'', '', "']['"),
                            $elName
                        ) . "']";
                        eval("\$this->_submitValues{$idx} = \$this->_recursiveFilter(\$filter, \$value);");
                    }
                }
            }
        }
    }

    /**
     * Add additional custom element types to $GLOBALS
     */
    private function loadCustomElementsInGlobal()
    {
        // Add custom radio element type which will load our own radio HTML class
        if (!isset($GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']['radio_custom'])) {
            $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']['radio_custom'] = 'HTML_QuickForm_radio_Custom';
        }

        // Add custom checkbox element type which will load our own checkbox HTML class
        if (!isset($GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']['checkbox_custom'])) {
            $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']['checkbox'] = 'HTML_QuickForm_checkbox_Custom';
            $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']['checkbox_custom'] = 'HTML_QuickForm_checkbox_Custom';
        }
    }
}
