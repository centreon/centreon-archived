<?php
/**
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * HTML class for a password type field
 *
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 */
class HTML_QuickForm_password extends HTML_QuickForm_input
{
    /**
     * Class constructor
     *
     * @param     string    $elementName    (optional)Input field name attribute
     * @param     string    $elementLabel   (optional)Input field label
     * @param     mixed     $attributes     (optional)Either a typical HTML attribute string
     *                                      or an associative array
     */
    public function __construct($elementName=null, $elementLabel=null, $attributes=null)
    {
        parent::__construct($elementName, $elementLabel, $attributes);
        $this->setType('password');
    }

    /**
     * Sets size of password element
     *
     * @param     string    $size  Size of password field
     */
    public function setSize($size)
    {
        $this->updateAttributes(array('size'=>$size));
    }

    /**
     * Sets maxlength of password element
     *
     * @param     string    $maxlength  Maximum length of password field
     */
    public function setMaxlength($maxlength)
    {
        $this->updateAttributes(array('maxlength'=>$maxlength));
    }

    /**
     * Returns the value of field without HTML tags (in this case, value is changed to a mask)
     *
     * @return    string
     */
    public function getFrozenHtml()
    {
        $value = $this->getValue();
        return ('' != $value? '**********': '&nbsp;') .
               $this->_getPersistantData();
    }
}
?>
