<?php
/**
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * A pseudo-element used for adding raw HTML to form
 *
 * Intended for use with the default renderer only, template-based
 * ones may (and probably will) completely ignore this
 *
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 * @deprecated  Please use the templates rather than add raw HTML via this element
 */
class HTML_QuickForm_html extends HTML_QuickForm_static
{
   /**
    * Class constructor
    *
    * @param string $text   raw HTML to add
    */
    function __construct($text = null)
    {
        parent::__construct(null, null, $text);
        $this->_type = 'html';
    }

   /**
    * Accepts a renderer
    *
    * @param HTML_QuickForm_Renderer    renderer object (only works with Default renderer!)
    */
    public function accept(HTML_QuickForm_Renderer &$renderer, $required = false, $error = null)
    {
        $renderer->renderHtml($this);
    }
}
?>
