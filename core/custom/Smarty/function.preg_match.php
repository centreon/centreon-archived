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

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {preg_match} function plugin
 *
 * Type:     function
 * Name:     preg_match
 * Purpose:  offers php preg_match function inside a template
 * @author Damiano Venturin
 * @param array parameters
 * @param object $template template object
 * @return boolean, matches array in template
 */
function smarty_function_preg_match($params, $template)
{
    $flags = (empty($params['flags']) ? 0 : $params['flags']);
    $offset = (empty($params['offset']) ? 0 : $params['offset']);
    $match = preg_match("/".$params['pattern']."/", $params['subject'], $matches, $flags, $offset);
    $template->assign('matches', $matches);
    return $match;
}
?>
