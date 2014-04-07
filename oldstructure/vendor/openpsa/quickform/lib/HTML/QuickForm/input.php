<?php
/**
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * Base class for <input /> form elements
 *
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @abstract
 */
class HTML_QuickForm_input extends HTML_QuickForm_element
{
    /**
     * Sets the element type
     *
     * @param     string    $type   Element type
     */
    public function setType($type)
    {
        $this->_type = $type;
        $this->updateAttributes(array('type'=>$type));
    }

    /**
     * Sets the input field name
     *
     * @param     string    $name   Input field name attribute
     */
    public function setName($name)
    {
        $this->updateAttributes(array('name'=>$name));
    }

    /**
     * Returns the element name
     *
     * @return    string
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * Sets the value of the form element
     *
     * @param     string    $value      Default value of the form element
     */
    public function setValue($value)
    {
        $this->updateAttributes(array('value'=>$value));
    }

    /**
     * Returns the value of the form element
     *
     * @return    string
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }

    /**
     * Returns the input field in HTML
     *
     * @return    string
     */
    public function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            return $this->_getTabs() . '<input' . $this->_getAttrString($this->_attributes) . ' />';
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
        // do not use submit values for button-type elements
        $type = $this->getType();
        if (('updateValue' != $event) ||
            ('submit' != $type && 'reset' != $type && 'image' != $type && 'button' != $type)) {
            parent::onQuickFormEvent($event, $arg, $caller);
        } else {
            $value = $this->_findValue($caller->_constantValues);
            if (null === $value) {
                $value = $this->_findValue($caller->_defaultValues);
            }
            if (null !== $value) {
                $this->setValue($value);
            }
        }
        return true;
    }

   /**
    * We don't need values from button-type elements (except submit) and files
    */
    function exportValue(&$submitValues, $assoc = false)
    {
        $type = $this->getType();
        if ('reset' == $type || 'image' == $type || 'button' == $type || 'file' == $type) {
            return null;
        } else {
            return parent::exportValue($submitValues, $assoc);
        }
    }
}
?>
