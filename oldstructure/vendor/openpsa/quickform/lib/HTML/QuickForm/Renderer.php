<?php
/**
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * An abstract base class for QuickForm renderers
 *
 * The class implements a Visitor design pattern
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 */
abstract class HTML_QuickForm_Renderer
{
   /**
    * Called when visiting a form, before processing any form elements
    *
    * @param    HTML_QuickForm  a form being visited
    */
    abstract public function startForm(&$form);

   /**
    * Called when visiting a form, after processing all form elements
    *
    * @param    HTML_QuickForm  a form being visited
    */
    abstract public function finishForm(&$form);

   /**
    * Called when visiting a header element
    *
    * @param    HTML_QuickForm_header   a header element being visited
    */
    abstract public function renderHeader(&$header);

   /**
    * Called when visiting an element
    *
    * @param    HTML_QuickForm_element  form element being visited
    * @param    bool                    Whether an element is required
    * @param    string                  An error message associated with an element
    */
    abstract public function renderElement(&$element, $required, $error);

   /**
    * Called when visiting a hidden element
    *
    * @param    HTML_QuickForm_element  a hidden element being visited
    */
    abstract public function renderHidden(&$element);

   /**
    * Called when visiting a raw HTML/text pseudo-element
    *
    * Only implemented in Default renderer. Usage of 'html' elements is
    * discouraged, templates should be used instead.
    *
    * @param    HTML_QuickForm_html     a 'raw html' element being visited
    */
    abstract public function renderHtml(&$data);

   /**
    * Called when visiting a group, before processing any group elements
    *
    * @param    HTML_QuickForm_group    A group being visited
    * @param    bool                    Whether a group is required
    * @param    string                  An error message associated with a group
    */
    abstract public function startGroup(&$group, $required, $error);

   /**
    * Called when visiting a group, after processing all group elements
    *
    * @param    HTML_QuickForm_group    A group being visited
    * @return   void
    */
    abstract public function finishGroup(&$group);
}
?>