<?php
/**
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * HTML class for a radio type element
 *
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 */
class HTML_QuickForm_radio extends HTML_QuickForm_input
{
    /**
     * Radio display text
     *
     * @var       string
     * @access    private
     */
    var $_text = '';

    /**
     * Class constructor
     *
     * @param     string    Input field name attribute
     * @param     mixed     Label(s) for a field
     * @param     string    Text to display near the radio
     * @param     string    Input field value
     * @param     mixed     Either a typical HTML attribute string or an associative array
     */
    public function __construct($elementName=null, $elementLabel=null, $text=null, $value=null, $attributes=null)
    {
        parent::__construct($elementName, $elementLabel, $attributes);
        if (isset($value)) {
            $this->setValue($value);
        }
        $this->_persistantFreeze = true;
        $this->setType('radio');
        $this->_text = $text;
        $this->_generateId();
    }

    /**
     * Sets whether radio button is checked
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
     * Returns whether radio button is checked
     *
     * @return    string
     */
    public function getChecked()
    {
        return $this->getAttribute('checked');
    }

    /**
     * Returns the radio element in HTML
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
            return '<tt>(x)</tt>' .
                   $this->_getPersistantData();
        } else {
            return '<tt>( )</tt>';
        }
    }

    /**
     * Sets the radio text
     *
     * @param     string    $text  Text to display near the radio button
     */
    public function setText($text)
    {
        $this->_text = $text;
    }

    /**
     * Returns the radio text
     *
     * @return    string
     */
    public function getText()
    {
        return $this->_text;
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
                    $value = $this->_findValue($caller->_submitValues);
                    if (null === $value) {
                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                if (!is_null($value) && $value == $this->getValue()) {
                    $this->setChecked(true);
                } else {
                    $this->setChecked(false);
                }
                break;
            case 'setGroupValue':
                if ($arg == $this->getValue()) {
                    $this->setChecked(true);
                } else {
                    $this->setChecked(false);
                }
                break;
            default:
                parent::onQuickFormEvent($event, $arg, $caller);
        }
        return true;
    }

   /**
    * Returns the value attribute if the radio is checked, null if it is not
    */
    function exportValue(&$submitValues, $assoc = false)
    {
        $value = $this->_findValue($submitValues);
        if (null === $value) {
            $value = $this->getChecked()? $this->getValue(): null;
        } elseif ($value != $this->getValue()) {
            $value = null;
        }
        return $this->_prepareValue($value, $assoc);
    }
}
?>
