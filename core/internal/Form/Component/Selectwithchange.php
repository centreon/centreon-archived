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

namespace Centreon\Internal\Form\Component;

use Centreon\Internal\Di;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Selectwithchange extends Select
{
    /**
     * 
     * @param array $element
     * @return array
     */
    public static function renderHtmlInput(array $element)
    {
        $extraData = 'data-callback="' . $element['label_initCallback'] . '"';
        if (isset($element['label_additionalRoute']) && trim($element['label_additionalRoute'])) {
            $element['label_additionalRoute'] = Di::getDefault()->get('router')->getPathFor($element['label_additionalRoute'], $element['label_extra']);
            $extraData .= ' data-extra-url="' . $element['label_additionalRoute'] . '"';
        }
        $render = parent::renderHtmlInput($element);
        $render['html'] = preg_replace('/<input(.*)/', '<input ' . $extraData . '$1', $render['html']);
        $render['js'] .= '$("#' . $element['name'] . '").on("change", function () {
              var callback = $(this).data("callback");
              var url = $(this).data("extra-url");
              url = (url === undefined ? "" : url);

              var callbackFunction = window[callback];
              if ( typeof callbackFunction === "function" ) {
                  callbackFunction($(this).select2("data"), $(this), url);
              }
            });';

        return array(
            'html' => $render['html'],
            'js' => $render['js']
        );
    }
}
