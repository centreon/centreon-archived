<?php
/**
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * HTML class for a submit type element
 *
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 */
class HTML_QuickForm_submit extends HTML_QuickForm_input
{
    /**
     * Class constructor
     *
     * @param     string    Input field name attribute
     * @param     string    Input field value
     * @param     mixed     Either a typical HTML attribute string or an associative array
     */
    public function __construct($elementName=null, $value=null, $attributes=null)
    {
        parent::__construct($elementName, null, $attributes);
        $this->setValue($value);
        $this->setType('submit');
    }

    /**
     * Freeze the element so that only its value is returned
     */
    public function freeze()
    {
        return false;
    }

   /**
    * Only return the value if it is found within $submitValues (i.e. if
    * this particular submit button was clicked)
    */
    function exportValue(&$submitValues, $assoc = false)
    {
        return $this->_prepareValue($this->_findValue($submitValues), $assoc);
    }
}
?>
