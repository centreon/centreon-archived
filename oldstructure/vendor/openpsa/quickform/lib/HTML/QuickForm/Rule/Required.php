<?php
/**
 * @package     HTML_QuickForm
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * Required elements validation
 *
 * @package     HTML_QuickForm
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 */
class HTML_QuickForm_Rule_Required extends HTML_QuickForm_Rule
{
    /**
     * Checks if an element is empty
     *
     * @param     string    $value      Value to check
     * @param     mixed     $options    Not used yet
     * @return    boolean   true if value is not empty
     */
    public function validate($value, $options = null)
    {
        if (is_array($value))
        {
            return !empty($value);
        }
        if ((string)$value == '') {
            return false;
        }
        return true;
    }

    function getValidationScript($options = null)
    {
        return array('', "{jsVar} == ''");
    }
}
?>
