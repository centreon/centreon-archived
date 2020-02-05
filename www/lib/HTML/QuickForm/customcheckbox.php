<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * HTML class for a checkbox type field
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id$
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * HTML class for a checkbox type field
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Alexey Borzov <avb@php.net>
 * @version     Release: 3.2.14
 * @since       1.0
 */
class HTML_QuickForm_customcheckbox extends HTML_QuickForm_checkbox
{

    public $checkboxTemplate;


    public function toHtml()
    {
        $oldHtml = parent::toHtml();
        $matches = array(
            '{element}',
            '{id}'
        );
        $replacements = array(
            $oldHtml,
            $this->getAttribute('id')
        );
        return str_replace($matches, $replacements, $this->checkboxTemplate);
    }

    public function setCheckboxTemplate($checkboxTemplate)
    {
        $this->checkboxTemplate = $checkboxTemplate;
    }
}

if (class_exists('HTML_QuickForm')) {
    (new HTML_QuickForm)->registerElementType(
        'customcheckbox',
        'HTML/QuickForm/customcheckbox.php',
        'HTML_QuickForm_customcheckbox'
    );
}
