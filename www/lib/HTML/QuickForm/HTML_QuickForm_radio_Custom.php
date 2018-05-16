<?php

class HTML_QuickForm_radio_Custom extends HTML_QuickForm_radio
{

    /**
     * Tries to find the element value from the values array
     * This is a modified version of the original _findValue()
     * Which has changes for loading the default values
     *
     * @param array $values
     *
     * @return mixed
     */
    protected function _findValue(&$values)
    {
        if (empty($values)) {
            return null;
        }
        $elementName = $this->getName();
        if (isset($values[$elementName])) {
            return $values[$elementName];
        } elseif (strpos($elementName, '[')) {
            $myVar = "['" . str_replace(
                    array('\\', '\'', ']', '['), array('\\\\', '\\\'', '', "']['"),
                    $elementName
                ) . "']";

            /* patch for centreon */
            if (preg_match('/\[(.+)\]$/', $elementName, $matches)) {
                if (isset($values[$matches[1]]) && !isset($values[$matches[1]][$matches[1]])) {
                    return $values[$matches[1]];
                }
            }
            /* end of patch */

            return eval("return (isset(\$values$myVar)) ? \$values$myVar : null;");
        } else {
            return null;
        }
    }
}
