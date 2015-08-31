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
 */

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.url.php
 * Type:     modifier
 * Name:     url
 * Purpose:  outputs a full url
 * -------------------------------------------------------------
 */
function smarty_modifier_url($url) {
    $di = \Centreon\Internal\Di::getDefault();
    $config = $di->get('config');
    $fullUrl = rtrim($config->get('global','base_url'), '/').$url;
    return $fullUrl;
}
