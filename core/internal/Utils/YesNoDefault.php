<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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

namespace Centreon\Internal\Utils;

/**
 * Utils class 
 *
 * @author Sylvestre Ho <sho@centreon.com>
 * @package Centreon
 * @subpackage Utils
 */
class YesNoDefault
{
    const YES = 1;
    const NO = 0;
    const DFLT = 2;

    /**
     * Convert yes / no / default int values to string
     *
     * @param int $val
     * @return string
     */
    public static function toString($val)
    {
        if ($val == self::YES) {
            return _('Yes');
        }
        if ($val == self::NO) {
            return _('No');
        }
        return "";
    }
}
