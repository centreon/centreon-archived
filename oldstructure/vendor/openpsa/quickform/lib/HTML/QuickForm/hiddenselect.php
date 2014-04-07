<?php
/**
 * @package     HTML_QuickForm
 * @author      Isaac Shepard <ishepard@bsiweb.com>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * Hidden select pseudo-element
 *
 * This class takes the same arguments as a select element, but instead
 * of creating a select ring it creates hidden elements for all values
 * already selected with setDefault or setConstant.  This is useful if
 * you have a select ring that you don't want visible, but you need all
 * selected values to be passed.
 *
 * @package     HTML_QuickForm
 * @author      Isaac Shepard <ishepard@bsiweb.com>
 */
class HTML_QuickForm_hiddenselect extends HTML_QuickForm_select
{
    /**
     * Class constructor
     *
     * @param     string    Select name attribute
     * @param     mixed     Label(s) for the select (not used)
     * @param     mixed     Data to be used to populate options
     * @param     mixed     Either a typical HTML attribute string or an associative array (not used)
     */
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null)
    {
        parent::__construct($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_type = 'hiddenselect';
        if (isset($options)) {
            $this->load($options);
        }
    }

    /**
     * Returns the SELECT in HTML
     *
     * @return    string
     */
    public function toHtml()
    {
        if (empty($this->_values)) {
            return '';
        }

        $tabs    = $this->_getTabs();
        $name    = $this->getPrivateName();
        $strHtml = '';

        foreach ($this->_values as $key => $val) {
            for ($i = 0, $optCount = count($this->_options); $i < $optCount; $i++) {
                if ($val == $this->_options[$i]['attr']['value']) {
                    $strHtml .= $tabs . '<input' . $this->_getAttrString(array(
                        'type'  => 'hidden',
                        'name'  => $name,
                        'value' => $val
                    )) . " />\n" ;
                }
            }
        }

        return $strHtml;
    }

   /**
    * This is essentially a hidden element and should be rendered as one
    */
    public function accept(HTML_QuickForm_Renderer &$renderer, $required = false, $error = null)
    {
        $renderer->renderHidden($this);
    }
}
?>
