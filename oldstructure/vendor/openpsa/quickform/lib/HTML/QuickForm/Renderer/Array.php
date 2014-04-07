<?php
/**
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Thomas Schulz <ths@4bconsult.de>
 * @copyright   2001-2011 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * A concrete renderer for HTML_QuickForm, makes an array of form contents
 *
 * Based on old HTML_QuickForm::toArray() code.
 *
 * The form array structure is the following:
 * <pre>
 * array(
 *   'frozen'           => 'whether the form is frozen',
 *   'javascript'       => 'javascript for client-side validation',
 *   'attributes'       => 'attributes for <form> tag',
 *   'requirednote      => 'note about the required elements',
 *   // if we set the option to collect hidden elements
 *   'hidden'           => 'collected html of all hidden elements',
 *   // if there were some validation errors:
 *   'errors' => array(
 *     '1st element name' => 'Error for the 1st element',
 *     ...
 *     'nth element name' => 'Error for the nth element'
 *   ),
 *   // if there are no headers in the form:
 *   'elements' => array(
 *     element_1,
 *     ...
 *     element_N
 *   )
 *   // if there are headers in the form:
 *   'sections' => array(
 *     array(
 *       'header'   => 'Header text for the first header',
 *       'name'     => 'Header name for the first header',
 *       'elements' => array(
 *          element_1,
 *          ...
 *          element_K1
 *       )
 *     ),
 *     ...
 *     array(
 *       'header'   => 'Header text for the Mth header',
 *       'name'     => 'Header name for the Mth header',
 *       'elements' => array(
 *          element_1,
 *          ...
 *          element_KM
 *       )
 *     )
 *   )
 * );
 * </pre>
 *
 * where element_i is an array of the form:
 * <pre>
 * array(
 *   'name'      => 'element name',
 *   'value'     => 'element value',
 *   'type'      => 'type of the element',
 *   'frozen'    => 'whether element is frozen',
 *   'label'     => 'label for the element',
 *   'required'  => 'whether element is required',
 *   'error'     => 'error associated with the element',
 *   'style'     => 'some information about element style (e.g. for Smarty)',
 *   // if element is not a group
 *   'html'      => 'HTML for the element'
 *   // if element is a group
 *   'separator' => 'separator for group elements',
 *   'elements'  => array(
 *     element_1,
 *     ...
 *     element_N
 *   )
 * );
 * </pre>
 *
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Thomas Schulz <ths@4bconsult.de>
 */
class HTML_QuickForm_Renderer_Array extends HTML_QuickForm_Renderer
{
    /**
     * An array being generated
     *
     * @var array
     */
    private $_ary;

    /**
     * Number of sections in the form (i.e. number of headers in it)
     *
     * @var integer
     */
    private $_sectionCount;

    /**
     * Current section number
     *
     * @var integer
     */
    private $_currentSection;

    /**
     * Array representing current group
     *
     * @var array
     */
    private $_currentGroup = null;

    /**
     * Additional style information for different elements
     *
     * @var array
     */
    private $_elementStyles = array();

    /**
     * true: collect all hidden elements into string; false: process them as usual form elements
     *
     * @var bool
     */
    private $_collectHidden = false;

    /**
     * true:  render an array of labels to many labels, $key 0 named 'label', the rest "label_$key"
     * false: leave labels as defined
     *
     * @var bool
     */
    private $_staticLabels = false;

    /**
     * Constructor
     *
     * @param  bool    true: collect all hidden elements into string; false: process them as usual form elements
     * @param  bool    true: render an array of labels to many labels, $key 0 to 'label' and the oterh to "label_$key"
     */
    public function __construct($collectHidden = false, $staticLabels = false)
    {
        $this->_collectHidden = $collectHidden;
        $this->_staticLabels  = $staticLabels;
    }

    /**
     * Returns the resultant array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_ary;
    }

    /**
     * @inheritDoc
     */
    public function startForm(&$form)
    {
        $this->_ary = array(
            'frozen'            => $form->isFrozen(),
            'javascript'        => $form->getValidationScript(),
            'attributes'        => $form->getAttributes(true),
            'requirednote'      => $form->getRequiredNote(),
            'errors'            => array()
        );
        if ($this->_collectHidden) {
            $this->_ary['hidden'] = '';
        }
        $this->_elementIdx     = 1;
        $this->_currentSection = null;
        $this->_sectionCount   = 0;
    }

    /**
     * @inheritDoc
     */
    public function renderHeader(&$header)
    {
        $this->_ary['sections'][$this->_sectionCount] = array(
            'header' => $header->toHtml(),
            'name'   => $header->getName()
        );
        $this->_currentSection = $this->_sectionCount++;
    }

    /**
     * @inheritDoc
     */
    public function renderElement(&$element, $required, $error)
    {
        $elAry = $this->_elementToArray($element, $required, $error);
        if (!empty($error)) {
            $this->_ary['errors'][$elAry['name']] = $error;
        }
        $this->_storeArray($elAry);
    }

    /**
     * @inheritDoc
     */
    public function renderHidden(&$element)
    {
        if ($this->_collectHidden) {
            $this->_ary['hidden'] .= $element->toHtml() . "\n";
        } else {
            $this->renderElement($element, false, null);
        }
    }

    /**
     * @inheritDoc
     */
    public function startGroup(&$group, $required, $error)
    {
        $this->_currentGroup = $this->_elementToArray($group, $required, $error);
        if (!empty($error)) {
            $this->_ary['errors'][$this->_currentGroup['name']] = $error;
        }
    }

    /**
     * @inheritDoc
     */
    public function finishGroup(&$group)
    {
        $this->_storeArray($this->_currentGroup);
        $this->_currentGroup = null;
    }

    /**
     * Creates an array representing an element
     *
     * @param  HTML_QuickForm_element    element being processed
     * @param  bool                      Whether an element is required
     * @param  string                    Error associated with the element
     * @return array
     */
    private function _elementToArray(&$element, $required, $error)
    {
        $ret = array(
            'name'      => $element->getName(),
            'value'     => $element->getValue(),
            'type'      => $element->getType(),
            'frozen'    => $element->isFrozen(),
            'required'  => $required,
            'error'     => $error
        );
        // render label(s)
        $labels = $element->getLabel();
        if (is_array($labels) && $this->_staticLabels) {
            foreach($labels as $key => $label) {
                $key = is_int($key)? $key + 1: $key;
                if (1 === $key) {
                    $ret['label'] = $label;
                } else {
                    $ret['label_' . $key] = $label;
                }
            }
        } else {
            $ret['label'] = $labels;
        }

        // set the style for the element
        if (isset($this->_elementStyles[$ret['name']])) {
            $ret['style'] = $this->_elementStyles[$ret['name']];
        }
        if ('group' == $ret['type']) {
            $ret['separator'] = $element->_separator;
            $ret['elements']  = array();
        } else {
            $ret['html']      = $element->toHtml();
        }
        return $ret;
    }


    /**
     * Stores an array representation of an element in the form array
     *
     * @param array  Array representation of an element
     */
    private function _storeArray($elAry)
    {
        // where should we put this element...
        if (is_array($this->_currentGroup) && ('group' != $elAry['type'])) {
            $this->_currentGroup['elements'][] = $elAry;
        } elseif (isset($this->_currentSection)) {
            $this->_ary['sections'][$this->_currentSection]['elements'][] = $elAry;
        } else {
            $this->_ary['elements'][] = $elAry;
        }
    }

   /**
    * Sets a style to use for element rendering
    *
    * @param mixed      element name or array ('element name' => 'style name')
    * @param string     style name if $elementName is not an array
    */
    public function setElementStyle($elementName, $styleName = null)
    {
        if (is_array($elementName)) {
            $this->_elementStyles = array_merge($this->_elementStyles, $elementName);
        } else {
            $this->_elementStyles[$elementName] = $styleName;
        }
    }

    /**
     * @inheritDoc
     */
    public function finishForm(&$form)
    {
    }

    /**
     * @inheritDoc
     */
    public function renderHtml(&$data)
    {
    }
}
?>
