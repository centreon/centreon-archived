<?php
/*
 * Copyright 2005-2015 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
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
