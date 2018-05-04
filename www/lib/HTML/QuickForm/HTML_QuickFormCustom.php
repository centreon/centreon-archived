<?php

require_once realpath(dirname(__FILE__) . '/../../../../vendor/autoload.php');
require_once 'PEAR.php';

class HTML_QuickFormCustom extends HTML_QuickForm
{

    /**
     * Array containing the form fields
     * @since     1.0
     * @var  array
     * @access   private
     */
    public $_elements = array();

    /**
     * Array containing element name to index map
     * @since     1.1
     * @var  array
     * @access   private
     */
    public $_elementIndex = array();

    /**
     * Array containing indexes of duplicate elements
     * @since     2.10
     * @var  array
     * @access   private
     */
    public $_duplicateIndex = array();

    /**
     * Array containing required field IDs
     * @since     1.0
     * @var  array
     * @access   private
     */
    public $_required = array();

    /**
     * Prefix message in javascript alert if error
     * @since     1.0
     * @var  string
     * @access   public
     */
    public $_jsPrefix = 'Invalid information entered.';

    /**
     * Postfix message in javascript alert if error
     * @since     1.0
     * @var  string
     * @access   public
     */
    public $_jsPostfix = 'Please correct these fields.';

    /**
     * Datasource object implementing the informal
     * datasource protocol
     * @since     3.3
     * @var  object
     * @access   private
     */
    public $_datasource;

    /**
     * Array of default form values
     * @since     2.0
     * @var  array
     * @access   private
     */
    public $_defaultValues = array();

    /**
     * Array of constant form values
     * @since     2.0
     * @var  array
     * @access   private
     */
    public $_constantValues = array();

    /**
     * Array of submitted form values
     * @since     1.0
     * @var  array
     * @access   private
     */
    public $_submitValues = array();

    /**
     * Array of submitted form files
     * @since     1.0
     * @var  integer
     * @access   public
     */
    public $_submitFiles = array();

    /**
     * Value for maxfilesize hidden element if form contains file input
     * @since     1.0
     * @var  integer
     * @access   public
     */
    public $_maxFileSize = 1048576; // 1 Mb = 1048576

    /**
     * Flag to know if all fields are frozen
     * @since     1.0
     * @var  boolean
     * @access   private
     */
    public $_freezeAll = false;

    /**
     * Array containing the form rules
     * @since     1.0
     * @var  array
     * @access   private
     */
    public $_rules = array();

    /**
     * Form rules, global variety
     * @var     array
     * @access  private
     */
    public $_formRules = array();

    /**
     * Array containing the validation errors
     * @since     1.0
     * @var  array
     * @access   private
     */
    public $_errors = array();

    /**
     * Note for required fields in the form
     * @var       string
     * @since     1.0
     * @access    private
     */
    public $_requiredNote = '<span style="font-size:80%; color:#ff0000;">*</span><span style="font-size:80%;"> denotes required field</span>';

    /**
     * Whether the form was submitted
     * @var       boolean
     * @access    private
     */
    public $_flagSubmitted = false;

    /**
     *
     * @var type
     */
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
    public function __construct(
        $formName = '',
        $method = 'post',
        $action = '',
        $target = '',
        $attributes = null,
        $trackSubmit = false
    ) {
        parent::__construct($formName, $method, $action, $target, $attributes, $trackSubmit);

        $this->addFormRule([$this, 'checkSecurityToken']);
    }


    /**
     * Sets a datasource object for this form object
     *
     * Datasource default and constant values will feed the QuickForm object if
     * the datasource implements defaultValues() and constantValues() methods.
     *
     * @param     object $datasource datasource object implementing the informal datasource protocol
     * @param     mixed $defaultsFilter string or array of filter(s) to apply to default values
     * @param     mixed $constantsFilter string or array of filter(s) to apply to constants values
     * @since     3.3
     * @access    public
     * @return    void
     * @throws    HTML_QuickForm_Error
     */
    public function setDatasource(&$datasource, $defaultsFilter = null, $constantsFilter = null)
    {
        if (is_object($datasource)) {
            $this->_datasource =& $datasource;
            if (is_callable(array($datasource, 'defaultValues'))) {
                $this->setDefaults($datasource->defaultValues($this), $defaultsFilter);
            }
            if (is_callable(array($datasource, 'constantValues'))) {
                $this->setConstants($datasource->constantValues($this), $constantsFilter);
            }
        } else {
            return PEAR::raiseError(
                null,
                QUICKFORM_INVALID_DATASOURCE,
                null,
                E_USER_WARNING,
                "Datasource is not an object in QuickForm::setDatasource()",
                'HTML_QuickForm_Error',
                true
            );
        }
    }

    /**
     * Initializes default form values
     *
     * @param     array $defaultValues values used to fill the form
     * @param     mixed $filter (optional) filter(s) to apply to all default values
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    HTML_QuickForm_Error
     */
    public function setDefaults($defaultValues = null, $filter = null)
    {
        if (is_array($defaultValues)) {
            if (isset($filter)) {
                if (is_array($filter) && (2 != count($filter) || !is_callable($filter))) {
                    foreach ($filter as $val) {
                        if (!is_callable($val)) {
                            return PEAR::raiseError(
                                null,
                                QUICKFORM_INVALID_FILTER,
                                null,
                                E_USER_WARNING,
                                "Callback function does not exist in QuickForm::setDefaults()",
                                'HTML_QuickForm_Error',
                                true
                            );
                        } else {
                            $defaultValues = $this->_recursiveFilter($val, $defaultValues);
                        }
                    }
                } elseif (!is_callable($filter)) {
                    return PEAR::raiseError(
                        null,
                        QUICKFORM_INVALID_FILTER,
                        null,
                        E_USER_WARNING,
                        "Callback function does not exist in QuickForm::setDefaults()",
                        'HTML_QuickForm_Error',
                        true
                    );
                } else {
                    $defaultValues = $this->_recursiveFilter($filter, $defaultValues);
                }
            }
            $this->_defaultValues = HTML_QuickForm::arrayMerge($this->_defaultValues, $defaultValues);
            foreach (array_keys($this->_elements) as $key) {
                $this->_elements[$key]->onQuickFormEvent('updateValue', null, $this);
            }
        }
    }

    /**
     * Initializes constant form values.
     * These values won't get overridden by POST or GET vars
     *
     * @param     array $constantValues values used to fill the form
     * @param     mixed $filter (optional) filter(s) to apply to all default values
     *
     * @since     2.0
     * @access    public
     * @return    void
     * @throws    HTML_QuickForm_Error
     */
    public function setConstants($constantValues = null, $filter = null)
    {
        if (is_array($constantValues)) {
            if (isset($filter)) {
                if (is_array($filter) && (2 != count($filter) || !is_callable($filter))) {
                    foreach ($filter as $val) {
                        if (!is_callable($val)) {
                            return PEAR::raiseError(
                                null,
                                QUICKFORM_INVALID_FILTER,
                                null,
                                E_USER_WARNING,
                                "Callback function does not exist in QuickForm::setConstants()",
                                'HTML_QuickForm_Error',
                                true
                            );
                        } else {
                            $constantValues = $this->_recursiveFilter($val, $constantValues);
                        }
                    }
                } elseif (!is_callable($filter)) {
                    return PEAR::raiseError(
                        null,
                        QUICKFORM_INVALID_FILTER,
                        null,
                        E_USER_WARNING,
                        "Callback function does not exist in QuickForm::setConstants()",
                        'HTML_QuickForm_Error',
                        true
                    );
                } else {
                    $constantValues = $this->_recursiveFilter($filter, $constantValues);
                }
            }
            $this->_constantValues = HTML_QuickForm::arrayMerge($this->_constantValues, $constantValues);
            foreach (array_keys($this->_elements) as $key) {
                $this->_elements[$key]->onQuickFormEvent('updateValue', null, $this);
            }
        }
    }

    /**
     * Returns a form element of the given type
     *
     * @param     string $event event to send to newly created element ('createElement' or 'addElement')
     * @param     string $type element type
     * @param     array $args arguments for event
     * @since     2.0
     * @access    private
     * @return    HTML_QuickForm_Element
     * @throws    HTML_QuickForm_Error
     */
    public function &_loadElement($event, $type, $args)
    {
        $type = strtolower($type);
        if (!HTML_QuickForm::isTypeRegistered($type)) {
            $error = PEAR::raiseError(
                null,
                QUICKFORM_UNREGISTERED_ELEMENT,
                null,
                E_USER_WARNING,
                "Element '$type' does not exist in HTML_QuickForm::_loadElement()",
                'HTML_QuickForm_Error',
                true
            );
            return $error;
        }
        $className = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type];
        $elementObject = new $className();
        for ($i = 0; $i < 5; $i++) {
            if (!isset($args[$i])) {
                $args[$i] = null;
            }
        }
        $err = $elementObject->onQuickFormEvent($event, $args, $this);
        if ($err !== true) {
            return $err;
        }
        return $elementObject;
    }

    /**
     * Adds an element into the form
     *
     * If $element is a string representing element type, then this
     * method accepts variable number of parameters, their meaning
     * and count depending on $element
     *
     * @param    mixed $element element object or type of element to add (text, textarea, file...)
     * @since    1.0
     * @return   HTML_QuickForm_Element     a reference to newly added element
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    public function &addElement($element)
    {
        if (is_object($element) && is_subclass_of($element, 'html_quickform_element')) {
            $elementObject = &$element;
            $elementObject->onQuickFormEvent('updateValue', null, $this);
        } else {
            $args = func_get_args();
            $elementObject =& $this->_loadElement('addElement', $element, array_slice($args, 1));
            if (PEAR::isError($elementObject)) {
                return $elementObject;
            }
        }
        $elementName = $elementObject->getName();

        // Add the element if it is not an incompatible duplicate
        if (!empty($elementName) && isset($this->_elementIndex[$elementName])) {
            if ($this->_elements[$this->_elementIndex[$elementName]]->getType() ==
                $elementObject->getType()) {
                $this->_elements[] =& $elementObject;
                $elKeys = array_keys($this->_elements);
                $this->_duplicateIndex[$elementName][] = end($elKeys);
            } else {
                $error = PEAR::raiseError(
                    null,
                    QUICKFORM_INVALID_ELEMENT_NAME,
                    null,
                    E_USER_WARNING,
                    "Element '$elementName' already exists in HTML_QuickForm::addElement()",
                    'HTML_QuickForm_Error',
                    true
                );
                return $error;
            }
        } else {
            $this->_elements[] =& $elementObject;
            $elKeys = array_keys($this->_elements);
            $this->_elementIndex[$elementName] = end($elKeys);
        }
        if ($this->_freezeAll) {
            $elementObject->freeze();
        }

        return $elementObject;
    }

    /**
     * Inserts a new element right before the other element
     *
     * Warning: it is not possible to check whether the $element is already
     * added to the form, therefore if you want to move the existing form
     * element to a new position, you'll have to use removeElement():
     * $form->insertElementBefore($form->removeElement('foo', false), 'bar');
     *
     * @access   public
     * @since    3.2.4
     * @param    HTML_QuickForm_element  Element to insert
     * @param    string                  Name of the element before which the new
     *                                   one is inserted
     * @return   HTML_QuickForm_element  reference to inserted element
     * @throws   HTML_QuickForm_Error
     */
    public function &insertElementBefore(&$element, $nameAfter)
    {
        if (!empty($this->_duplicateIndex[$nameAfter])) {
            $error = PEAR::raiseError(
                null,
                QUICKFORM_INVALID_ELEMENT_NAME,
                null,
                E_USER_WARNING,
                'Several elements named "' . $nameAfter . '" exist in HTML_QuickForm::insertElementBefore().',
                'HTML_QuickForm_Error',
                true
            );
            return $error;
        } elseif (!$this->elementExists($nameAfter)) {
            $error = PEAR::raiseError(
                null,
                QUICKFORM_NONEXIST_ELEMENT,
                null,
                E_USER_WARNING,
                "Element '$nameAfter' does not exist in HTML_QuickForm::insertElementBefore()",
                'HTML_QuickForm_Error',
                true
            );
            return $error;
        }
        $elementName = $element->getName();
        $targetIdx = $this->_elementIndex[$nameAfter];
        $duplicate = false;
        // Like in addElement(), check that it's not an incompatible duplicate
        if (!empty($elementName) && isset($this->_elementIndex[$elementName])) {
            if ($this->_elements[$this->_elementIndex[$elementName]]->getType() != $element->getType()) {
                $error = PEAR::raiseError(
                    null,
                    QUICKFORM_INVALID_ELEMENT_NAME,
                    null,
                    E_USER_WARNING,
                    "Element '$elementName' already exists in HTML_QuickForm::insertElementBefore()",
                    'HTML_QuickForm_Error',
                    true
                );
                return $error;
            }
            $duplicate = true;
        }
        // Move all the elements after added back one place, reindex _elementIndex and/or _duplicateIndex
        $elKeys = array_keys($this->_elements);
        for ($i = end($elKeys); $i >= $targetIdx; $i--) {
            if (isset($this->_elements[$i])) {
                $currentName = $this->_elements[$i]->getName();
                $this->_elements[$i + 1] =& $this->_elements[$i];
                if ($this->_elementIndex[$currentName] == $i) {
                    $this->_elementIndex[$currentName] = $i + 1;
                } else {
                    $dupIdx = array_search($i, $this->_duplicateIndex[$currentName]);
                    $this->_duplicateIndex[$currentName][$dupIdx] = $i + 1;
                }
                unset($this->_elements[$i]);
            }
        }
        // Put the element in place finally
        $this->_elements[$targetIdx] =& $element;
        if (!$duplicate) {
            $this->_elementIndex[$elementName] = $targetIdx;
        } else {
            $this->_duplicateIndex[$elementName][] = $targetIdx;
        }
        $element->onQuickFormEvent('updateValue', null, $this);
        if ($this->_freezeAll) {
            $element->freeze();
        }
        // If not done, the elements will appear in reverse order
        ksort($this->_elements);
        return $element;
    }

    /**
     * Returns a reference to the element
     *
     * @param     string $element Element name
     * @since     2.0
     * @access    public
     * @return    HTML_QuickForm_element    reference to element
     * @throws    HTML_QuickForm_Error
     */
    public function &getElement($element)
    {
        if (isset($this->_elementIndex[$element])) {
            return $this->_elements[$this->_elementIndex[$element]];
        } else {
            $error = PEAR::raiseError(
                null,
                QUICKFORM_NONEXIST_ELEMENT,
                null,
                E_USER_WARNING,
                "Element '$element' does not exist in HTML_QuickForm::getElement()",
                'HTML_QuickForm_Error',
                true
            );
            return $error;
        }
    }

    /**
     * Returns the element's raw value
     *
     * This returns the value as submitted by the form (not filtered)
     * or set via setDefaults() or setConstants()
     *
     * @param     string $element Element name
     * @since     2.0
     * @access    public
     * @return    mixed     element value
     * @throws    HTML_QuickForm_Error
     */
    public function &getElementValue($element)
    {
        if (!isset($this->_elementIndex[$element])) {
            $error = PEAR::raiseError(
                null,
                QUICKFORM_NONEXIST_ELEMENT,
                null,
                E_USER_WARNING,
                "Element '$element' does not exist in HTML_QuickForm::getElementValue()",
                'HTML_QuickForm_Error',
                true
            );
            return $error;
        }
        $value = $this->_elements[$this->_elementIndex[$element]]->getValue();
        if (isset($this->_duplicateIndex[$element])) {
            foreach ($this->_duplicateIndex[$element] as $index) {
                if (null !== ($v = $this->_elements[$index]->getValue())) {
                    if (is_array($value)) {
                        $value[] = $v;
                    } else {
                        $value = (null === $value) ? $v : array($value, $v);
                    }
                }
            }
        }
        return $value;
    }

    /**
     * Removes an element
     *
     * The method "unlinks" an element from the form, returning the reference
     * to the element object. If several elements named $elementName exist,
     * it removes the first one, leaving the others intact.
     *
     * @param string $elementName The element name
     * @param boolean $removeRules True if rules for this element are to be removed too
     * @access public
     * @since 2.0
     * @return HTML_QuickForm_element    a reference to the removed element
     * @throws HTML_QuickForm_Error
     */
    public function &removeElement($elementName, $removeRules = true)
    {
        if (!isset($this->_elementIndex[$elementName])) {
            $error = PEAR::raiseError(
                null,
                QUICKFORM_NONEXIST_ELEMENT,
                null,
                E_USER_WARNING,
                "Element '$elementName' does not exist in HTML_QuickForm::removeElement()",
                'HTML_QuickForm_Error',
                true
            );
            return $error;
        }
        $el =& $this->_elements[$this->_elementIndex[$elementName]];
        unset($this->_elements[$this->_elementIndex[$elementName]]);
        if (empty($this->_duplicateIndex[$elementName])) {
            unset($this->_elementIndex[$elementName]);
        } else {
            $this->_elementIndex[$elementName] = array_shift($this->_duplicateIndex[$elementName]);
        }
        if ($removeRules) {
            $this->_required = array_diff($this->_required, array($elementName));
            unset($this->_rules[$elementName], $this->_errors[$elementName]);
            if ('group' == $el->getType()) {
                foreach (array_keys($el->getElements()) as $key) {
                    unset($this->_rules[$el->getElementName($key)]);
                }
            }
        }
        return $el;
    }

    /**
     * Adds a validation rule for the given field
     *
     * If the element is in fact a group, it will be considered as a whole.
     * To validate grouped elements as separated entities,
     * use addGroupRule instead of addRule.
     *
     * @param    string $element Form element name
     * @param    string $message Message to display for invalid data
     * @param    string $type Rule type, use getRegisteredRules() to get types
     * @param    string $format (optional)Required for extra rule data
     * @param    string $validation (optional)Where to perform validation: "server", "client"
     * @param    boolean $reset Client-side validation:
     * reset the form element to its original value if there is an error?
     * @param    boolean $force Force the rule to be applied, even if the target form element does not exist
     * @since    1.0
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    public function addRule(
        $element,
        $message,
        $type,
        $format = null,
        $validation = 'server',
        $reset = false,
        $force = false
    ) {
        if (!$force) {
            if (!is_array($element) && !$this->elementExists($element)) {
                return PEAR::raiseError(
                    null,
                    QUICKFORM_NONEXIST_ELEMENT,
                    null,
                    E_USER_WARNING,
                    "Element '$element' does not exist in HTML_QuickForm::addRule()",
                    'HTML_QuickForm_Error',
                    true
                );
            } elseif (is_array($element)) {
                foreach ($element as $el) {
                    if (!$this->elementExists($el)) {
                        return PEAR::raiseError(
                            null,
                            QUICKFORM_NONEXIST_ELEMENT,
                            null,
                            E_USER_WARNING,
                            "Element '$el' does not exist in HTML_QuickForm::addRule()",
                            'HTML_QuickForm_Error',
                            true
                        );
                    }
                }
            }
        }
        if (false === ($newName = $this->isRuleRegistered($type, true))) {
            return PEAR::raiseError(
                null,
                QUICKFORM_INVALID_RULE,
                null,
                E_USER_WARNING,
                "Rule '$type' is not registered in HTML_QuickForm::addRule()",
                'HTML_QuickForm_Error',
                true
            );
        } elseif (is_string($newName)) {
            $type = $newName;
        }
        if (is_array($element)) {
            $dependent = $element;
            $element = array_shift($dependent);
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
            $this->updateAttributes(
                array(
                    'onsubmit' => 'try { var myValidator = validate_' . $this->_attributes['id'] .
                        '; } catch(e) { return true; } return myValidator(this);'
                )
            );
        }
        $this->_rules[$element][] = array(
            'type' => $type,
            'format' => $format,
            'message' => $message,
            'validation' => $validation,
            'reset' => $reset,
            'dependent' => $dependent
        );
    }

    /**
     * Adds a validation rule for the given group of elements
     *
     * Only groups with a name can be assigned a validation rule
     * Use addGroupRule when you need to validate elements inside the group.
     * Use addRule if you need to validate the group as a whole. In this case,
     * the same rule will be applied to all elements in the group.
     * Use addRule if you need to validate the group against a function.
     *
     * @param    string $group Form group name
     * @param    mixed $arg1 Array for multiple elements or error message string for one element
     * @param    string $type (optional)Rule type use getRegisteredRules() to get types
     * @param    string $format (optional)Required for extra rule data
     * @param    int $howmany (optional)How many valid elements should be in the group
     * @param    string $validation (optional)Where to perform validation: "server", "client"
     * @param    bool $reset Client-side: whether to reset the element's value to its original state if failed.
     * @since    2.5
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    public function addGroupRule(
        $group,
        $arg1,
        $type = '',
        $format = null,
        $howmany = 0,
        $validation = 'server',
        $reset = false
    ) {
        if (!$this->elementExists($group)) {
            return PEAR::raiseError(
                null,
                QUICKFORM_NONEXIST_ELEMENT,
                null,
                E_USER_WARNING,
                "Group '$group' does not exist in HTML_QuickForm::addGroupRule()",
                'HTML_QuickForm_Error',
                true
            );
        }

        $groupObj =& $this->getElement($group);
        if (is_array($arg1)) {
            $required = 0;
            foreach ($arg1 as $elementIndex => $rules) {
                $elementName = $groupObj->getElementName($elementIndex);
                foreach ($rules as $rule) {
                    $format = (isset($rule[2])) ? $rule[2] : null;
                    $validation = (isset($rule[3]) && 'client' == $rule[3]) ? 'client' : 'server';
                    $reset = isset($rule[4]) && $rule[4];
                    $type = $rule[1];
                    if (false === ($newName = $this->isRuleRegistered($type, true))) {
                        return PEAR::raiseError(
                            null,
                            QUICKFORM_INVALID_RULE,
                            null,
                            E_USER_WARNING,
                            "Rule '$type' is not registered in HTML_QuickForm::addGroupRule()",
                            'HTML_QuickForm_Error',
                            true
                        );
                    } elseif (is_string($newName)) {
                        $type = $newName;
                    }

                    $this->_rules[$elementName][] = array(
                        'type' => $type,
                        'format' => $format,
                        'message' => $rule[0],
                        'validation' => $validation,
                        'reset' => $reset,
                        'group' => $group
                    );

                    if ('required' == $type || 'uploadedfile' == $type) {
                        $groupObj->_required[] = $elementName;
                        $this->_required[] = $elementName;
                        $required++;
                    }
                    if ('client' == $validation) {
                        $this->updateAttributes(
                            array(
                                'onsubmit' => 'try { var myValidator = validate_' . $this->_attributes['id'] .
                                    '; } catch(e) { return true; } return myValidator(this);'
                            )
                        );
                    }
                }
            }
            if ($required > 0 && count($groupObj->getElements()) == $required) {
                $this->_required[] = $group;
            }
        } elseif (is_string($arg1)) {
            if (false === ($newName = $this->isRuleRegistered($type, true))) {
                return PEAR::raiseError(
                    null,
                    QUICKFORM_INVALID_RULE,
                    null,
                    E_USER_WARNING,
                    "Rule '$type' is not registered in HTML_QuickForm::addGroupRule()",
                    'HTML_QuickForm_Error',
                    true
                );
            } elseif (is_string($newName)) {
                $type = $newName;
            }

            // addGroupRule() should also handle <select multiple>
            if (is_a($groupObj, 'html_quickform_group')) {
                // Radios need to be handled differently when required
                if ($type == 'required' && $groupObj->getGroupType() == 'radio') {
                    $howmany = ($howmany == 0) ? 1 : $howmany;
                } else {
                    $howmany = ($howmany == 0) ? count($groupObj->getElements()) : $howmany;
                }
            }

            $this->_rules[$group][] = array(
                'type' => $type,
                'format' => $format,
                'message' => $arg1,
                'validation' => $validation,
                'howmany' => $howmany,
                'reset' => $reset
            );
            if ($type == 'required') {
                $this->_required[] = $group;
            }
            if ($validation == 'client') {
                $this->updateAttributes(
                    array(
                        'onsubmit' => 'try { var myValidator = validate_' .
                            $this->_attributes['id'] . '; } catch(e) { return true; } return myValidator(this);'
                    )
                );
            }
        }
    } // end func addGroupRule

    // }}}
    // {{{ addFormRule()

    /**
     * Adds a global validation rule
     *
     * This should be used when for a rule involving several fields or if
     * you want to use some completely custom validation for your form.
     * The rule function/method should return true in case of successful
     * validation and array('element name' => 'error') when there were errors.
     *
     * @access   public
     * @param    mixed   Callback, either function name or array(&$object, 'method')
     * @throws   HTML_QuickForm_Error
     */
    public function addFormRule($rule)
    {
        if (!is_callable($rule)) {
            return PEAR::raiseError(
                null,
                QUICKFORM_INVALID_RULE,
                null,
                E_USER_WARNING,
                'Callback function does not exist in HTML_QuickForm::addFormRule()',
                'HTML_QuickForm_Error',
                true
            );
        }
        $this->_formRules[] = $rule;
    }

    // }}}
    // {{{ applyFilter()

    /**
     * Applies a data filter for the given field(s)
     *
     * @param    mixed $element Form element name or array of such names
     * @param    mixed $filter Callback, either function name or array(&$object, 'method')
     * @since    2.0
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    public function applyFilter($element, $filter)
    {
        if (!is_callable($filter)) {
            return PEAR::raiseError(
                null,
                QUICKFORM_INVALID_FILTER,
                null,
                E_USER_WARNING,
                "Callback function does not exist in QuickForm::applyFilter()",
                'HTML_QuickForm_Error',
                true
            );
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
                        $idx = "['" .
                            str_replace(
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
     * Performs the server side validation
     * @access    public
     * @since     1.0
     * @return    boolean   true if no error found
     * @throws    HTML_QuickForm_Error
     */
    public function validate()
    {
        if (count($this->_rules) == 0 && count($this->_formRules) == 0 &&
            $this->isSubmitted()) {
            return (0 == count($this->_errors));
        } elseif (!$this->isSubmitted()) {
            return false;
        }

        $registry =& HTML_QuickForm_RuleRegistry::singleton();

        foreach ($this->_rules as $target => $rules) {
            $submitValue = $this->getSubmitValue($target);

            foreach ($rules as $rule) {
                if ((isset($rule['group']) && isset($this->_errors[$rule['group']])) ||
                    isset($this->_errors[$target])
                ) {
                    continue 2;
                }
                // If element is not required and is empty, we shouldn't validate it
                if (!$this->isElementRequired($target)) {
                    if (!isset($submitValue) || '' == $submitValue) {
                        continue 2;
                        // Fix for bug #3501: we shouldn't validate not uploaded files, either.
                        // Unfortunately, we can't just use $element->isUploadedFile() since
                        // the element in question can be buried in group. Thus this hack.
                        // See also bug #12014, we should only consider a file that has
                        // status UPLOAD_ERR_NO_FILE as not uploaded, in all other cases
                        // validation should be performed, so that e.g. 'maxfilesize' rule
                        // will display an error if status is UPLOAD_ERR_INI_SIZE
                        // or UPLOAD_ERR_FORM_SIZE
                    } elseif (is_array($submitValue)) {
                        if (false === ($pos = strpos($target, '['))) {
                            $isUpload = !empty($this->_submitFiles[$target]);
                        } else {
                            $base = str_replace(
                                array('\\', '\''),
                                array('\\\\', '\\\''),
                                substr($target, 0, $pos)
                            );
                            $idx = "['" .
                                str_replace(
                                    array('\\', '\'', ']', '['),
                                    array('\\\\', '\\\'', '', "']['"),
                                    substr($target, $pos + 1, -1)
                                ) . "']";
                            eval("\$isUpload = isset(\$this->_submitFiles['{$base}']['name']{$idx});");
                        }
                        if ($isUpload &&
                            (!isset($submitValue['error']) || UPLOAD_ERR_NO_FILE == $submitValue['error'])
                        ) {
                            continue 2;
                        }
                    }
                }
                if (isset($rule['dependent']) && is_array($rule['dependent'])) {
                    $values = array($submitValue);
                    foreach ($rule['dependent'] as $elName) {
                        $values[] = $this->getSubmitValue($elName);
                    }
                    $result = $registry->validate($rule['type'], $values, $rule['format'], true);
                } elseif (is_array($submitValue) && !isset($rule['howmany'])) {
                    $result = $registry->validate($rule['type'], $submitValue, $rule['format'], true);
                } else {
                    $result = $registry->validate($rule['type'], $submitValue, $rule['format'], false);
                }

                if (!$result || (!empty($rule['howmany']) && $rule['howmany'] > (int)$result)) {
                    if (isset($rule['group'])) {
                        $this->_errors[$rule['group']] = $rule['message'];
                    } else {
                        $this->_errors[$target] = $rule['message'];
                    }
                }
            }
        }

        // process the global rules now
        foreach ($this->_formRules as $rule) {
            if (true !== ($res = call_user_func($rule, $this->_submitValues, $this->_submitFiles))) {
                if (is_array($res)) {
                    $this->_errors += $res;
                } else {
                    return PEAR::raiseError(
                        null,
                        QUICKFORM_ERROR,
                        null,
                        E_USER_WARNING,
                        'Form rule callback returned invalid value in HTML_QuickForm::validate()',
                        'HTML_QuickForm_Error',
                        true
                    );
                }
            }
        }

        return (0 == count($this->_errors));
    } // end func validate

    // }}}
    // {{{ freeze()

    /**
     * Displays elements without HTML input tags
     *
     * @param    mixed $elementList array or string of element(s) to be frozen
     * @since     1.0
     * @access   public
     * @throws   HTML_QuickForm_Error
     */
    public function freeze($elementList = null)
    {
        if (!isset($elementList)) {
            $this->_freezeAll = true;
            $elementList = array();
        } else {
            if (!is_array($elementList)) {
                $elementList = preg_split('/[ ]*,[ ]*/', $elementList);
            }
            $elementList = array_flip($elementList);
        }

        foreach (array_keys($this->_elements) as $key) {
            $name = $this->_elements[$key]->getName();
            if ($this->_freezeAll || isset($elementList[$name])) {
                $this->_elements[$key]->freeze();
                unset($elementList[$name]);
            }
        }

        if (!empty($elementList)) {
            return PEAR::raiseError(
                null,
                QUICKFORM_NONEXIST_ELEMENT,
                null,
                E_USER_WARNING,
                "Nonexistant element(s): '" . implode("', '", array_keys(
                    $elementList
                )) . "' in HTML_QuickForm::freeze()",
                'HTML_QuickForm_Error',
                true
            );
        }
        return true;
    }

    /**
     * Performs the form data processing
     *
     * @param    mixed $callback Callback, either function name or array(&$object, 'method')
     * @param    bool $mergeFiles Whether uploaded files should be processed too
     * @since    1.0
     * @access   public
     * @throws   HTML_QuickForm_Error
     * @return   mixed     Whatever value the $callback function returns
     */
    public function process($callback, $mergeFiles = true)
    {
        if (!is_callable($callback)) {
            return PEAR::raiseError(
                null,
                QUICKFORM_INVALID_PROCESS,
                null,
                E_USER_WARNING,
                "Callback function does not exist in QuickForm::process()",
                'HTML_QuickForm_Error',
                true
            );
        }
        $values = ($mergeFiles === true)
            ? HTML_QuickForm::arrayMerge($this->_submitValues, $this->_submitFiles)
            : $this->_submitValues;
        return call_user_func($callback, $values);
    } // end func process

    // }}}
    // {{{ accept()

    /**
     * Accepts a renderer
     *
     * @param object     An HTML_QuickForm_Renderer object
     * @since 3.0
     * @access public
     * @return void
     */
    public function accept(&$renderer)
    {
        $this->createSecurityToken();
        $renderer->startForm($this);
        foreach (array_keys($this->_elements) as $key) {
            $element =& $this->_elements[$key];
            $elementName = $element->getName();
            $required = ($this->isElementRequired($elementName) && !$element->isFrozen());
            $error = $this->getElementError($elementName);
            $element->accept($renderer, $required, $error);
        }
        $renderer->finishForm($this);
    }

    /**
     * Returns the client side validation script
     *
     * @since     2.0
     * @access    public
     * @return    string    Javascript to perform validation, empty string if no 'client' rules were added
     */
    public function getValidationScript()
    {
        if (empty($this->_rules) || empty($this->_attributes['onsubmit'])) {
            return '';
        }

        $registry =& HTML_QuickForm_RuleRegistry::singleton();
        $test = array();
        $js_escape = array(
            "\r" => '\r',
            "\n" => '\n',
            "\t" => '\t',
            "'" => "\\'",
            '"' => '\"',
            '\\' => '\\\\'
        );

        foreach ($this->_rules as $elementName => $rules) {
            foreach ($rules as $rule) {
                if ('client' == $rule['validation']) {
                    unset($element);

                    $dependent = isset($rule['dependent']) && is_array($rule['dependent']);
                    $rule['message'] = strtr($rule['message'], $js_escape);

                    if (isset($rule['group'])) {
                        $group =& $this->getElement($rule['group']);
                        // No JavaScript validation for frozen elements
                        if ($group->isFrozen()) {
                            continue 2;
                        }
                        $elements =& $group->getElements();
                        foreach (array_keys($elements) as $key) {
                            if ($elementName == $group->getElementName($key)) {
                                $element =& $elements[$key];
                                break;
                            }
                        }
                    } elseif ($dependent) {
                        $element = array();
                        $element[] =& $this->getElement($elementName);
                        foreach ($rule['dependent'] as $elName) {
                            $element[] =& $this->getElement($elName);
                        }
                    } else {
                        $element =& $this->getElement($elementName);
                    }
                    // No JavaScript validation for frozen elements
                    if (is_object($element) && $element->isFrozen()) {
                        continue 2;
                    } elseif (is_array($element)) {
                        foreach (array_keys($element) as $key) {
                            if ($element[$key]->isFrozen()) {
                                continue 3;
                            }
                        }
                    }

                    $test[] = $registry->getValidationScript($element, $elementName, $rule);
                }
            }
        }
        if (count($test) > 0) {
            return
                "\n<script type=\"text/javascript\">\n" .
                "//<![CDATA[\n" .
                "function validate_" . $this->_attributes['id'] . "(frm) {\n" .
                "  var value = '';\n" .
                "  var errFlag = new Array();\n" .
                "  var _qfGroups = {};\n" .
                "  _qfMsg = '';\n\n" .
                join("\n", $test) .
                "\n  if (_qfMsg != '') {\n" .
                "    _qfMsg = '" . strtr($this->_jsPrefix, $js_escape) . "' + _qfMsg;\n" .
                "    _qfMsg = _qfMsg + '\\n" . strtr($this->_jsPostfix, $js_escape) . "';\n" .
                "    alert(_qfMsg);\n" .
                "    return false;\n" .
                "  }\n" .
                "  return true;\n" .
                "}\n" .
                "//]]>\n" .
                "</script>";
        }
        return '';
    }

    /**
     * Returns a 'safe' element's value
     *
     * This method first tries to find a cleaned-up submitted value,
     * it will return a value set by setValue()/setDefaults()/setConstants()
     * if submitted value does not exist for the given element.
     *
     * @param  string   Name of an element
     * @access public
     * @return mixed
     * @throws HTML_QuickForm_Error
     */
    public function exportValue($element)
    {
        if (!isset($this->_elementIndex[$element])) {
            return PEAR::raiseError(
                null,
                QUICKFORM_NONEXIST_ELEMENT,
                null,
                E_USER_WARNING,
                "Element '$element' does not exist in HTML_QuickForm::getElementValue()",
                'HTML_QuickForm_Error',
                true
            );
        }
        $value = $this->_elements[$this->_elementIndex[$element]]->exportValue($this->_submitValues, false);
        if (isset($this->_duplicateIndex[$element])) {
            foreach ($this->_duplicateIndex[$element] as $index) {
                if (null !== ($v = $this->_elements[$index]->exportValue($this->_submitValues, false))) {
                    if (is_array($value)) {
                        $value[] = $v;
                    } else {
                        $value = (null === $value) ? $v : array($value, $v);
                    }
                }
            }
        }
        return $value;
    }

    // }}}
    // {{{ exportValues()

    /**
     * Returns 'safe' elements' values
     *
     * Unlike getSubmitValues(), this will return only the values
     * corresponding to the elements present in the form.
     *
     * @param   mixed   Array/string of element names, whose values we want. If not set then return all elements.
     * @access  public
     * @return  array   An assoc array of elements' values
     * @throws  HTML_QuickForm_Error
     */
    public function exportValues($elementList = null)
    {
        $values = array();
        if (null === $elementList) {
            // iterate over all elements, calling their exportValue() methods
            foreach (array_keys($this->_elements) as $key) {
                $value = $this->_elements[$key]->exportValue($this->_submitValues, true);
                if (is_array($value)) {
                    // This shit throws a bogus warning in PHP 4.3.x
                    $values = HTML_QuickForm::arrayMerge($values, $value);
                }
            }
        } else {
            if (!is_array($elementList)) {
                $elementList = array_map('trim', explode(',', $elementList));
            }
            foreach ($elementList as $elementName) {
                $value = $this->exportValue($elementName);
                if (PEAR::isError($value)) {
                    return $value;
                }
                $values[$elementName] = $value;
            }
        }
        return $values;
    }

    /**
     * Return a textual error message for an QuickForm error code
     *
     * @access  public
     * @param   int     error code
     * @return  string  error message
     * @static
     */
    public static function errorMessage($value)
    {
        // make the variable static so that it only has to do the defining on the first call
        static $errorMessages;

        // define the varies error messages
        if (!isset($errorMessages)) {
            $errorMessages = array(
                QUICKFORM_OK => 'no error',
                QUICKFORM_ERROR => 'unknown error',
                QUICKFORM_INVALID_RULE => 'the rule does not exist as a registered rule',
                QUICKFORM_NONEXIST_ELEMENT => 'nonexistent html element',
                QUICKFORM_INVALID_FILTER => 'invalid filter',
                QUICKFORM_UNREGISTERED_ELEMENT => 'unregistered element',
                QUICKFORM_INVALID_ELEMENT_NAME => 'element already exists',
                QUICKFORM_INVALID_PROCESS => 'process callback does not exist',
                QUICKFORM_DEPRECATED => 'method is deprecated',
                QUICKFORM_INVALID_DATASOURCE => 'datasource is not an object'
            );
        }

        // If this is an error object, then grab the corresponding error code
        if (HTML_QuickForm::isError($value)) {
            $value = $value->getCode();
        }

        // return the textual error message corresponding to the code
        return isset($errorMessages[$value]) ? $errorMessages[$value] : $errorMessages[QUICKFORM_ERROR];
    } // end func errorMessage

    /**
     * Create the CSRF Token to be set in every form using QuickForm
     */
    public function createSecurityToken()
    {

        $token = md5(uniqid());
        if (false === isset($_SESSION['x-centreon-token']) &&
            (isset($_SESSION['x-centreon-token']) &&
                false === is_array($_SESSION['x-centreon-token']))
        ) {
            $_SESSION['x-centreon-token'] = array();
            $_SESSION['x-centreon-token-generated-at'] = array();
        }
        $_SESSION['x-centreon-token'][] = $token;
        $_SESSION['x-centreon-token-generated-at'][(string)$token] = time();

        $myTokenElement = $this->addElement('hidden', 'centreon_token');
        $myTokenElement->setValue($token);
    }

    /**
     * Check if the CSRF Token is still valid
     *
     * @param type $submittedValues
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
     * Empty all elapsed Toekn stored
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
}
