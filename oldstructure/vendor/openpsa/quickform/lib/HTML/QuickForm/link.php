<?php
/**
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * HTML class for a link type field
 *
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 */
class HTML_QuickForm_link extends HTML_QuickForm_static
{
    /**
     * Link display text
     *
     * @var       string
     * @access    private
     */
    var $_text = "";

    /**
     * Class constructor
     *
     * @param     string    $elementLabel   (optional)Link label
     * @param     string    $href           (optional)Link href
     * @param     string    $text           (optional)Link display text
     * @param     mixed     $attributes     (optional)Either a typical HTML attribute string
     *                                      or an associative array
     */
    public function __construct($elementName=null, $elementLabel=null, $href=null, $text=null, $attributes=null)
    {
        parent::__construct($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = false;
        $this->_type = 'link';
        $this->setHref($href);
        $this->_text = $text;
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
     * Sets value for textarea element
     *
     * @param     string    $value  Value for password element
     */
    public function setValue($value)
    {
        return;
    }

    /**
     * Returns the value of the form element
     */
    public function getValue()
    {
        return;
    }

    /**
     * Sets the links href
     *
     * @param     string    $href
     */
    public function setHref($href)
    {
        $this->updateAttributes(array('href'=>$href));
    }

    /**
     * Returns the textarea element in HTML
     *
     * @return    string
     */
    public function toHtml()
    {
        $tabs = $this->_getTabs();
        $html = "$tabs<a".$this->_getAttrString($this->_attributes).">";
        $html .= $this->_text;
        $html .= "</a>";
        return $html;
    }

    /**
     * Returns the value of field without HTML tags (in this case, value is changed to a mask)
     *
     * @return    string
     */
    public function getFrozenHtml()
    {
        return;
    }
}
?>
