<?php
/**
 * @package     HTML_QuickForm
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * Checks that the length of value is within range
 *
 * @package     HTML_QuickForm
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 */
class HTML_QuickForm_Rule_Range extends HTML_QuickForm_Rule
{
    /**
     * @inheritdoc
     *
     * Validates a value using a range comparison
     */
    public function validate($value, $options = null)
    {
        $length = strlen($value);
        switch ($this->name) {
            case 'minlength': return ($length >= $options);
            case 'maxlength': return ($length <= $options);
            default:          return ($length >= $options[0] && $length <= $options[1]);
        }
    }

    function getValidationScript($options = null)
    {
        switch ($this->name) {
            case 'minlength':
                $test = '{jsVar}.length < '.$options;
                break;
            case 'maxlength':
                $test = '{jsVar}.length > '.$options;
                break;
            default:
                $test = '({jsVar}.length < '.$options[0].' || {jsVar}.length > '.$options[1].')';
        }
        return array('', "{jsVar} != '' && {$test}");
    }
}
?>