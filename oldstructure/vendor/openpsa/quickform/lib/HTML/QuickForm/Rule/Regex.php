<?php
/**
 * @package     HTML_QuickForm
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * Validates values using regular expressions
 *
 * @package     HTML_QuickForm
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 */
class HTML_QuickForm_Rule_Regex extends HTML_QuickForm_Rule
{
    /**
     * Array of regular expressions
     *
     * Array is in the format:
     * $_data['rulename'] = 'pattern';
     *
     * @var     array
     * @access  private
     */
    var $_data = array(
                    'lettersonly'   => '/^[a-zA-Z]+$/',
                    'alphanumeric'  => '/^[a-zA-Z0-9]+$/',
                    'numeric'       => '/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/',
                    'nopunctuation' => '/^[^().\/\*\^\?#!@$%+=,\"\'><~\[\]{}]+$/',
                    'nonzero'       => '/^-?[1-9][0-9]*/'
                    );

    /**
     * Validates a value using a regular expression
     *
     * @param     string    $value      Value to be checked
     * @param     string    $regex      Regular expression
     * @return    boolean   true if value is valid
     */
    public function validate($value, $regex = null)
    {
        // Fix for bug #10799: add 'D' modifier to regex
        if (isset($this->_data[$this->name])) {
            if (!preg_match($this->_data[$this->name] . 'D', $value)) {
                return false;
            }
        } else {
            if (!preg_match($regex . 'D', $value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Adds new regular expressions to the list
     *
     * @param     string    $name       Name of rule
     * @param     string    $pattern    Regular expression pattern
     */
    public function addData($name, $pattern)
    {
        $this->_data[$name] = $pattern;
    }

    function getValidationScript($options = null)
    {
        $regex = isset($this->_data[$this->name]) ? $this->_data[$this->name] : $options;

        // bug #12376, converting unicode escapes and stripping 'u' modifier
        if ($pos = strpos($regex, 'u', strrpos($regex, '/'))) {
            $regex = substr($regex, 0, $pos) . substr($regex, $pos + 1);
            $regex = preg_replace('/(?<!\\\\)(?>\\\\\\\\)*\\\\x{([a-fA-F0-9]+)}/', '\\u$1', $regex);
        }

        return array("  var regex = " . $regex . ";\n", "{jsVar} != '' && !regex.test({jsVar})");
    }
}
?>