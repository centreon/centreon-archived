<?php
/*
 * Copyright 2005-2017 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/select2.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
require_once __DIR__.'/centreonFormSelect2.class.php';

class CentreonForm 
{
    public $form;
    private $tpl;
    private $renderer;
    private $attibutes;
    private $separator;
    private $path;
    private $helpData;
    private $o;

    public function __construct($path, $p, $o)
    {
        $this->form = new HTML_QuickForm('Form', 'post', "?p=" . $p);
        $this->separator = '&nbsp;';
        $this->helpData = array();
        $this->path = $path;
        $this->o = $o;
        //$this->applyFilter('__ALL__', array('this', 'myTrim'));
        $this->setRequiredNote();
        $this->setFormAttributes();
        $this->s2Config = new CentreonFormSelect2();
        $this->initSmarty($path);
        $this->getHelpData();
    }

    public function getForm()
    {
        return $this->form;
    }

    private function setFormAttributes()
    {
        $this->attibutes['text'] = array("size" => "30");
        $this->attibutes['text-small'] = array("size" => "6");
        $this->attibutes['text-long'] = array("size" => "60");

        $this->attibutes['select'] = array("style" => "width: 270px; height: 100px;");
        $this->attibutes['select-small'] = array("style" => "width: 270px; height: 50px;");
        $this->attibutes['select-big'] = array("style" => "width: 270px; height: 130px;");
        $this->attibutes['textarea'] = array("rows" => "6", "cols" => "100");
        $this->attibutes['multiSelect'] = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';
    }

    private function getHelpData()
    {
        include_once($this->path."/help.php");

        $this->tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"');

        $helptext = "";
        foreach ($help as $key => $text) {
            $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
            $this->helpData[$key] = '<span style="display:none" id="help:'.$key.'">'.$text.'</span>';
        }
        $this->assign("helptext", $helptext);
    }

    public function applyFilter($filter, $function)
    {
        $this->form->applyFilter($filter, $function);
    }

    public function setRequiredNote()
    {
        $this->form->setRequiredNote("<i style='color: red;'>*</i>&nbsp;" . _("Required fields"));
    }

    public function freeze()
    {
        $this->form->freeze();
    }

    public function getSubmitValue($elem)
    {
        return $this->form->getSubmitValue($elem);
    }

    public function getSubmitValues()
    {
        return $this->form->getSubmitValues();
    }

    public function isSubmitted()
    {
        return $this->form->isSubmitted();
    }

    public function addHeader($name, $label)
    {
        $this->form->addElement('header', $name, $label);
    }

    public function addHidden($name, $value = null)
    {
        $redirect = $this->form->addElement('hidden', $name);
        if (isset($value)) {
            $redirect->setValue($value);            
        }
    }

    public function addInputText($name, $value, $attributeName = null)
    {
        if (!isset($attributeName) && !isset($this->attibutes[$attributeName])) {
            $attributeName = "text";
        }
        $input = $this->form->addElement('text', $name, $value, $this->attibutes[$attributeName]);
        return $input;
    }

    public function addInputTextarea($name, $label, $options = null)
    {
        if (!isset($options)) {
            $options = $this->attibutes['textarea'];
        }
        $textarea = $this->form->addElement('textarea', $name, $label, $options);
        return $textarea;
    }

    public function addInputSelect($name, $label, $values, $options = null)
    {
        return $this->form->addElement('select', $name, $label, $values, $options);
    }

    public function addCheckbox($name, $label, $values, $options)
    {
        foreach ($values as $key => $value) {
            $item[] = HTML_QuickForm::createElement('checkbox', $key, $this->separator, $value);
        }
        $checkbox = $this->form->addGroup($item, $name, $label, '&nbsp;&nbsp;');
        return $checkbox;
    }

    public function addRadioButton($name, $label, $values, $defaultValue = null, $options = null)
    {
        foreach ($values as $key => $value) {
            $item[] = HTML_QuickForm::createElement('radio', $name, null, $value, $key);
        }
        $input = $this->form->addGroup($item, $name, $label, $this->separator);
        $this->form->setDefaults(array($name => $defaultValue));
        return $input;
    }

    public function addSubmitButton($name, $label, $options = null)
    {
        if (!isset($options)) {
            $options = array();
        }
        $options["class"] = "btc bt_success";
        $input = $this->form->addElement("submit", $name, $label, $options);
        return $input;
    }

    public function addResetButton($name, $label, $options = null)
    {
        if (!isset($options)) {
            $options = array();
        }
        $options["class"] = "btc bt_default";
        $input = $this->form->addElement("reset", $name, $label, $options);
        return $input;
    }

    public function addSelect2($name, $label, $type, $defaultDatasetParams)
    {
        $value = $this->s2Config->getData($type, $defaultDatasetParams);
        return $this->form->addElement('select2', $name, $label, array(), $value);
    }

    public function validate()
    {
        return $this->form->validate();
    }

    public function setDefaults($values) 
    {
        $this->form->setDefaults($values);
    }

    public function registerRule($name, $type, $function)
    {
        $this->form->registerRule($name, $type, $function);
    }

    public function addRule($field, $message, $function)
    {
        $this->form->addRule($field, $message, $function);
    }

    public function display($file)
    {
        $this->render();
        $this->tpl->assign('form', $this->renderer->toArray());
        $this->tpl->display($file);
    }

    public function initSmarty($path)
    {
        $this->tpl = new Smarty();
        $this->tpl = initSmartyTpl($path, $this->tpl);
        $this->assign('o', $this->o);
        $this->addHidden('o', $this->o);
    }

    public function assign($name, $value)
    {
        $this->tpl->assign($name, $value);
    }

    public function render()
    {
        $this->renderer = new HTML_QuickForm_Renderer_ArraySmarty($this->tpl, true);
        $this->renderer->setRequiredTemplate('{$label}&nbsp;<i  style="color:red;" size="1">*</i>');
        $this->renderer->setErrorTemplate('<i style="color:red;">{$error}</i><br />{$html}');
        $this->form->accept($this->renderer);
    }

    public function getElement($element)
    {
        return $this->form->getElement($element);
    }

    public function myTrim($str)
    {
        $str = rtrim($str, '\\');
        return (trim($str));
    }

}
