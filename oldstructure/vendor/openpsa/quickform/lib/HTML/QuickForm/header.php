<?php
/**
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * A pseudo-element used for adding headers to form
 *
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 */
class HTML_QuickForm_header extends HTML_QuickForm_static
{
   /**
    * Class constructor
    *
    * @param string $elementName    Header name
    * @param string $text           Header text
    */
    public function __construct($elementName = null, $text = null)
    {
        parent::__construct($elementName, null, $text);
        $this->_type = 'header';
    }

    /**
     * Accepts a renderer
     *
     * @param HTML_QuickForm_Renderer    renderer object
     */
    public function accept(HTML_QuickForm_Renderer &$renderer, $required = false, $error = null)
    {
        $renderer->renderHeader($this);
    }
}
?>
