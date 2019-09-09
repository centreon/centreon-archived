<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

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
                array('\\', '\'', ']', '['),
                array('\\\\', '\\\'', '', "']['"),
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

    /**
     * @inheritDoc
     */
    public function toHtml()
    {
        return '<div class="md-radio md-radio-inline">' . parent::toHtml() . '</div>';
    }
}
