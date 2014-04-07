<?php
/**
 * @package     HTML_QuickForm
 * @author      Wojciech Gdela <eltehaem@poczta.onet.pl>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * HTML class for static data
 *
 * @package     HTML_QuickForm
 * @author      Wojciech Gdela <eltehaem@poczta.onet.pl>
 */
class HTML_QuickForm_static extends HTML_QuickForm_element
{
    /**
     * Display text
     * @var       string
     * @access    private
     */
    var $_text = null;

    /**
     * Class constructor
     *
     * @param     string    $elementLabel   (optional)Label
     * @param     string    $text           (optional)Display text
     */
    public function __construct($elementName=null, $elementLabel=null, $text=null)
    {
        parent::__construct($elementName, $elementLabel);
        $this->_persistantFreeze = false;
        $this->_type = 'static';
        $this->_text = $text;
    }

    /**
     * Sets the element name
     *
     * @param     string    $name   Element name
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
     * Sets the text
     *
     * @param     string    $text
     */
    public function setText($text)
    {
        $this->_text = $text;
    }

    /**
     * Sets the text (uses the standard setValue call to emulate a form element.
     *
     * @param     string    $text
     */
    public function setValue($text)
    {
        $this->setText($text);
    }

    /**
     * Returns the static text element in HTML
     *
     * @return    string
     */
    public function toHtml()
    {
        return $this->_getTabs() . $this->_text;
    }

    /**
     * Returns the value of field without HTML tags
     *
     * @return    string
     */
    public function getFrozenHtml()
    {
        return $this->toHtml();
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
                // do NOT use submitted values for static elements
                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    $value = $this->_findValue($caller->_defaultValues);
                }
                if (null !== $value) {
                    $this->setValue($value);
                }
                break;
            default:
                parent::onQuickFormEvent($event, $arg, $caller);
        }
        return true;
    }

   /**
    * We override this here because we don't want any values from static elements
    */
    function exportValue(&$submitValues, $assoc = false)
    {
        return null;
    }
}
?>
