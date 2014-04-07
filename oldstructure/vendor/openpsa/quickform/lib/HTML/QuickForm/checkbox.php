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
 * HTML class for a checkbox type field
 *
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Alexey Borzov <avb@php.net>
 */
class HTML_QuickForm_checkbox extends HTML_QuickForm_input
{
    /**
     * Checkbox display text
     *
     * @var       string
     * @access    private
     */
    var $_text = '';

    /**
     * Class constructor
     *
     * @param     string    $elementName    (optional)Input field name attribute
     * @param     string    $elementLabel   (optional)Input field value
     * @param     string    $text           (optional)Checkbox display text
     * @param     mixed     $attributes     (optional)Either a typical HTML attribute string
     *                                      or an associative array
     */
    public function __construct($elementName=null, $elementLabel=null, $text='', $attributes=null)
    {
        parent::__construct($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_text = $text;
        $this->setType('checkbox');
        $this->updateAttributes(array('value'=>1));
        $this->_generateId();
    }

    /**
     * Sets whether a checkbox is checked
     *
     * @param     bool    $checked  Whether the field is checked or not
     */
    public function setChecked($checked)
    {
        if (!$checked) {
            $this->removeAttribute('checked');
        } else {
            $this->updateAttributes(array('checked'=>'checked'));
        }
    }

    /**
     * Returns whether a checkbox is checked
     *
     * @return    bool
     */
    public function getChecked()
    {
        return (bool)$this->getAttribute('checked');
    }

    /**
     * Returns the checkbox element in HTML
     *
     * @return    string
     */
    public function toHtml()
    {
        if (0 == strlen($this->_text)) {
            $label = '';
        } elseif ($this->_flagFrozen) {
            $label = $this->_text;
        } else {
            $label = '<label for="' . $this->getAttribute('id') . '">' . $this->_text . '</label>';
        }
        return HTML_QuickForm_input::toHtml() . $label;
    }

    /**
     * Returns the value of field without HTML tags
     *
     * @return    string
     */
    public function getFrozenHtml()
    {
        if ($this->getChecked()) {
            return '<tt>[x]</tt>' .
                   $this->_getPersistantData();
        } else {
            return '<tt>[ ]</tt>';
        }
    }

    /**
     * Sets the checkbox text
     *
     * @param     string    $text
     */
    public function setText($text)
    {
        $this->_text = $text;
    }

    /**
     * Returns the checkbox text
     *
     * @return    string
     */
    public function getText()
    {
        return $this->_text;
    }

    /**
     * Sets the value of the form element
     *
     * @param     string    $value      Default value of the form element
     */
    public function setValue($value)
    {
        return $this->setChecked($value);
    }

    /**
     * Returns the value of the form element
     *
     * @return    bool
     */
    public function getValue()
    {
        return $this->getChecked();
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
            case 'updateValue':
                // constant values override both default and submitted ones
                // default values are overriden by submitted
                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    // if no boxes were checked, then there is no value in the array
                    // yet we don't want to display default value in this case
                    if ($caller->isSubmitted()) {
                        $value = $this->_findValue($caller->_submitValues);
                    } else {
                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                if (null !== $value || $caller->isSubmitted()) {
                    $this->setChecked($value);
                }
                break;
            case 'setGroupValue':
                $this->setChecked($arg);
                break;
            default:
                parent::onQuickFormEvent($event, $arg, $caller);
        }
        return true;
    }

   /**
    * Return true if the checkbox is checked, null if it is not checked (getValue() returns false)
    */
    function exportValue(&$submitValues, $assoc = false)
    {
        $value = $this->_findValue($submitValues);
        if (null === $value) {
            $value = $this->getChecked()? true: null;
        }
        return $this->_prepareValue($value, $assoc);
    }
}
?>
