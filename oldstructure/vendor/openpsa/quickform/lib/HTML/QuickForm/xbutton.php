<?php
/**
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * Class for HTML 4.0 <button> element
 *
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 */
class HTML_QuickForm_xbutton extends HTML_QuickForm_element
{
   /**
    * Contents of the <button> tag
    * @var      string
    * @access   private
    */
    var $_content;

   /**
    * Class constructor
    *
    * @param    string  Button name
    * @param    string  Button content (HTML to add between <button></button> tags)
    * @param    mixed   Either a typical HTML attribute string or an associative array
    */
    public function __construct($elementName = null, $elementContent = null, $attributes = null)
    {
        parent::__construct($elementName, null, $attributes);
        $this->setContent($elementContent);
        $this->setPersistantFreeze(false);
        $this->_type = 'xbutton';
    }

    function toHtml()
    {
        return '<button' . $this->getAttributes(true) . '>' . $this->_content . '</button>';
    }

    function getFrozenHtml()
    {
        return $this->toHtml();
    }

    function freeze()
    {
        return false;
    }

    function setName($name)
    {
        $this->updateAttributes(array(
            'name' => $name
        ));
    }

    function getName()
    {
        return $this->getAttribute('name');
    }

    function setValue($value)
    {
        $this->updateAttributes(array(
            'value' => $value
        ));
    }

    function getValue()
    {
        return $this->getAttribute('value');
    }

   /**
    * Sets the contents of the button element
    *
    * @param    string  Button content (HTML to add between <button></button> tags)
    */
    function setContent($content)
    {
        $this->_content = $content;
    }

    function onQuickFormEvent($event, $arg, &$caller)
    {
        if ('updateValue' != $event) {
            return parent::onQuickFormEvent($event, $arg, $caller);
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
    * Returns a 'safe' element's value
    *
    * The value is only returned if the button's type is "submit" and if this
    * particlular button was clicked
    */
    function exportValue(&$submitValues, $assoc = false)
    {
        if ('submit' == $this->getAttribute('type')) {
            return $this->_prepareValue($this->_findValue($submitValues), $assoc);
        } else {
            return null;
        }
    }
}
?>