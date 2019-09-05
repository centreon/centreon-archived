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
class HTML_QuickForm_checkbox_Custom extends HTML_QuickForm_checkbox
{
    public function toHtml()
    {
        if (empty($this->_text)) {
            $this->_text = ' ';
        }
        return '<div class="md-checkbox md-checkbox-inline">' . parent::toHtml() . '</div>';
    }
}
