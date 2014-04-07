<?php
/**
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * Base class for form elements
 *
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Alexey Borzov <avb@php.net>
 */
abstract class HTML_QuickForm_element extends HTML_Common
{
    /**
     * Label of the field
     *
     * @var       string
     * @access    private
     */
    var $_label = '';

    /**
     * Form element type
     *
     * @var       string
     * @access    private
     */
    var $_type = '';

    /**
     * Flag to tell if element is frozen
     *
     * @var       boolean
     * @access    private
     */
    var $_flagFrozen = false;

    /**
     * Does the element support persistant data when frozen
     *
     * @var       boolean
     * @access    private
     */
    var $_persistantFreeze = false;

    /**
     * Class constructor
     *
     * @param    string     Name of the element
     * @param    mixed      Label(s) for the element
     * @param    mixed      Associative array of tag attributes or HTML attributes name="value" pairs
     */
    public function __construct($elementName=null, $elementLabel=null, $attributes=null)
    {
        parent::__construct($attributes);
        if (isset($elementName)) {
            $this->setName($elementName);
        }
        if (isset($elementLabel)) {
            $this->setLabel($elementLabel);
        }
    }

    /**
     * Returns the current API version
     *
     * @return    float
     */
    public function apiVersion()
    {
        return 3.2;
    }

    /**
     * Returns element type
     *
     * @return    string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Sets the input field name
     *
     * @param     string    $name   Input field name attribute
     */
    public function setName($name)
    {
        // interface method
    }

    /**
     * Returns the element name
     *
     * @return    string
     */
    public function getName()
    {
        // interface method
    }

    /**
     * Sets the value of the form element
     *
     * @param     string    $value      Default value of the form element
     */
    public function setValue($value)
    {
        // interface
    }

    /**
     * Returns the value of the form element
     *
     * @return    mixed
     */
    public function getValue()
    {
        // interface
        return null;
    }

    /**
     * Freeze the element so that only its value is returned
     */
    public function freeze()
    {
        $this->_flagFrozen = true;
    }

   /**
    * Unfreezes the element so that it becomes editable
    */
    public function unfreeze()
    {
        $this->_flagFrozen = false;
    }

    /**
     * Returns the value of field without HTML tags
     *
     * @return    string
     */
    public function getFrozenHtml()
    {
        $value = $this->getValue();
        return (strlen($value)? htmlspecialchars($value): '&nbsp;') .
               $this->_getPersistantData();
    }

   /**
    * Used by getFrozenHtml() to pass the element's value if _persistantFreeze is on
    *
    * @access private
    * @return string
    */
    function _getPersistantData()
    {
        if (!$this->_persistantFreeze) {
            return '';
        } else {
            $id = $this->getAttribute('id');
            return '<input' . $this->_getAttrString(array(
                       'type'  => 'hidden',
                       'name'  => $this->getName(),
                       'value' => $this->getValue()
                   ) + (isset($id)? array('id' => $id): array())) . ' />';
        }
    }

    /**
     * Returns whether or not the element is frozen
     *
     * @return    bool
     */
    public function isFrozen()
    {
        return $this->_flagFrozen;
    }

    /**
     * Sets wether an element value should be kept in an hidden field
     * when the element is frozen or not
     *
     * @param     bool    $persistant   True if persistant value
     */
    public function setPersistantFreeze($persistant=false)
    {
        $this->_persistantFreeze = $persistant;
    }

    /**
     * Sets display text for the element
     *
     * @param     string    $label  Display text for the element
     */
    public function setLabel($label)
    {
        $this->_label = $label;
    }

    /**
     * Returns display text for the element
     *
     * @return    string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Tries to find the element value from the values array
     *
     * @return    mixed
     */
    protected function _findValue(&$values)
    {
        if (empty($values)) {
            return null;
        }
        $elementName = $this->getName();
        if (isset($values[$elementName])) {
            return $values[$elementName];
        } elseif (strpos($elementName, '[')) {
            $myVar = "['" . str_replace(
                         array('\\', '\'', ']', '['), array('\\\\', '\\\'', '', "']['"),
                         $elementName
                     ) . "']";
            return eval("return (isset(\$values$myVar)) ? \$values$myVar : null;");
        } else {
            return null;
        }
    }

    /**
     * Called by HTML_QuickForm whenever form event is made on this element
     *
     * @param     string    $event  Name of event
     * @param     mixed     $arg    event arguments
     * @param     object    &$caller calling object
     */
    public function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'createElement':
                break;
            case 'addElement':
                $this->onQuickFormEvent('updateValue', null, $caller);
                break;
            case 'updateValue':
                // constant values override both default and submitted ones
                // default values are overriden by submitted
                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    $value = $this->_findValue($caller->_submitValues);
                    if (null === $value) {
                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                if (null !== $value) {
                    $this->setValue($value);
                }
                break;
            case 'setGroupValue':
                $this->setValue($arg);
        }
        return true;
    }

   /**
    * Accepts a renderer
    *
    * @param HTML_QuickForm_Renderer    renderer object
    * @param boolean                       Whether an element is required
    * @param string                     An error message associated with an element
    */
    public function accept(HTML_QuickForm_Renderer &$renderer, $required = false, $error = null)
    {
        $renderer->renderElement($this, $required, $error);
    }

   /**
    * Automatically generates and assigns an 'id' attribute for the element.
    *
    * Currently used to ensure that labels work on radio buttons and
    * checkboxes. Per idea of Alexander Radivanovich.
    *
    * @access private
    */
    function _generateId()
    {
        static $idx = 1;

        if (!$this->getAttribute('id')) {
            $this->updateAttributes(array('id' => 'qf_' . substr(md5(microtime() . $idx++), 0, 6)));
        }
    }

   /**
    * Returns a 'safe' element's value
    *
    * @param  array   array of submitted values to search
    * @param  bool    whether to return the value as associative array
    * @return mixed
    */
    public function exportValue(&$submitValues, $assoc = false)
    {
        $value = $this->_findValue($submitValues);
        if (null === $value) {
            $value = $this->getValue();
        }
        return $this->_prepareValue($value, $assoc);
    }

   /**
    * Used by exportValue() to prepare the value for returning
    *
    * @param  mixed   the value found in exportValue()
    * @param  bool    whether to return the value as associative array
    * @access private
    * @return mixed
    */
    function _prepareValue($value, $assoc)
    {
        if (null === $value) {
            return null;
        } elseif (!$assoc) {
            return $value;
        } else {
            $name = $this->getName();
            if (!strpos($name, '[')) {
                return array($name => $value);
            } else {
                $valueAry = array();
                $myIndex  = "['" . str_replace(
                                array('\\', '\'', ']', '['), array('\\\\', '\\\'', '', "']['"),
                                $name
                            ) . "']";
                eval("\$valueAry$myIndex = \$value;");
                return $valueAry;
            }
        }
    }
}
?>