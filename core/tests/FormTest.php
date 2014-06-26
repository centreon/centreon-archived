<?php
namespace Test\Centreon;

use Centreon\Internal\Config,
    Centreon\Internal\Di,
    Centreon\Internal\Template,
    Centreon\Internal\Form;

class FormTest extends \PHPUnit_Framework_TestCase
{
    private $datadir;
    private $formTpl;

    public function setUp()
    {
        $this->datadir = CENTREON_PATH . '/core/tests/data/';
        $this->formTpl = 'form/testForm.tpl';
        $config = new Config($this->datadir . '/test-template.ini');
        $di = new Di();
        $di->setShared('config', $config);
        $tpl = new Template();
        $di->setShared('template', $tpl);
        $tpl->setTemplateDir(CENTREON_PATH . '/core/tests/views/');
        parent::setUp();
    }
    
    public function tearDown()
    {
        Di::reset();
    }
    
    public function testAddButton()
    {
        $tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $arr = array(
            'type' => 'submit',
            'label' => 'Validate',
            'name' => 'testSubmit',
            'mandatory' => false
        );
        $form->addStatic($arr);
        $sm = $form->toSmarty();
        $tpl->assign('form', $sm['testSubmit']['html']);
        $printedResult = $tpl->fetch($this->formTpl);
        $this->assertContains(
            '<div class="form-group ">'.
            '<div class="col-sm-2" style="text-align:right">'.
            '<label class="label-controller" for="testSubmit">Validate</label>'.
            '</div><div class="col-sm-9"><</div></div>',
            $printedResult
        );
    }
    
    public function testSimpleCheckbox()
    {
        $tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $arr = array(
            'type' => 'checkbox',
            'label' => 'Simple checkbox',
            'name' => 'testCheckbox',
            'attributes' => json_encode(array('choices' => array('test' => 1))),
            'mandatory' => false
        );
        $form->addStatic($arr);
        $sm = $form->toSmarty();
        $tpl->assign('form', $sm['testCheckbox']['html']);
        $printedResult = $tpl->fetch($this->formTpl);
        $this->assertContains(
            '<div class="form-group "><div class="col-sm-2" style="text-align:right">'.
            '<label class="label-controller" for="testCheckbox">Simple checkbox</label>'.
            '</div><div class="col-sm-9"><label class="label-controller" for="testCheckbox1">&nbsp;'.
            '<input id="testCheckbox1" type="checkbox" name="testCheckbox" value=1  /> test</label>&nbsp;&nbsp;</div></div>',
            $printedResult
        );
    }
    
    public function testSimpleRadio()
    {
        $tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $arr = array(
            'type' => 'radio',
            'label' => 'Simple radio',
            'name' => 'testRadio',
            'attributes' => json_encode(array('choices' => array('test' => 1))),
            'mandatory' => false
        );
        $form->addStatic($arr);
        $sm = $form->toSmarty();
        $tpl->assign('form', $sm['testRadio']['html']);
        $printedResult = $tpl->fetch($this->formTpl);
        $this->assertContains(
            '<div class="form-group "><div class="col-sm-2" style="text-align:right">'.
            '<label class="label-controller" for="testRadio">Simple radio</label></div>'.
            '<div class="col-sm-9"><label class="label-controller" for="testRadio1">&nbsp;'.
            '<input id="testRadio1" type="radio" name="testRadio" value=1  /> test</label>&nbsp;&nbsp;</div></div>',
            $printedResult
        ); 
    }
    
    /*public function testSimpleSelect()
    {
        $tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $form->addSelect('testClassiqueSelect',  _("Test Select"), array(
            'This is test 1' => 'test1', 'This is test 2' => 'test2'));
        $tpl->assign('form', $form->toSmarty());
        $printedResult = $tpl->fetch('form/testAddSimpleSelect.tpl');
        $this->assertContains(
            '<div class="form-group">'.
            '<label class="sr-only" for="testClassiqueSelect">Test Select</label>'.
            '<select name="testClassiqueSelect">'.
            '<option value="test1">This is test 1</option>'.
            '<option value="test2">This is test 2</option>'.
            '</select>'.
            '</div>',
            $printedResult
        );
    }
    
    public function testMultiSelect()
    {
        $tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $selects['list'] = array(
          array(
              'name' => 'Select1',
              'label' => 'Select1',
              'value' => 'Select1'
          ),
          array(
              'name' => 'Select2',
              'label' => 'Select2',
              'value' => 'Select2'
          )
        );
        $form->addMultiSelect('testClassiqueSelect', 'testClassiqueSelect', '&nbsp;', $selects);
        $tpl->assign('form', $form->toSmarty());
        $printedResult = $tpl->fetch('form/testAddSimpleSelect.tpl');
        $this->assertContains(
            '<div class="form-group">'.
            '<label class="sr-only" for="testClassiqueSelect">testClassiqueSelect</label>'.
            '<div class="input-group">'.
            '<input id="testClassiqueSelect[Select1]" '.
            'type="select" name="testClassiqueSelect[Select1]" class="form-controler" />'.
            '<input id="testClassiqueSelect[Select2]" '.
            'type="select" name="testClassiqueSelect[Select2]" class="form-controler" />'.
            '</div>'.
            '</div>',
            $printedResult
        );
    }
    
    public function testAddHidden()
    {
        $tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $form->add('o', 'hidden', '', array('value' => '0'));
        $tpl->assign('form', $form->toSmarty());
        $printedResult = $tpl->fetch('form/testAddHidden.tpl');
        $this->assertContains(
            '<input '
            . 'name="o" '
            . 'type="hidden" '
            . 'value="0" '
            . '/>',
            $printedResult
        );
    }
    
    public function testAddReset()
    {
        $tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $form->add('testClassiqueInput', 'reset', _("Reset"));
        $tpl->assign('form', $form->toSmarty());
        $printedResult = $tpl->fetch($this->formTpl);
        $this->assertContains(
            '<div class="form-group">'.
            '<label class="sr-only" for="testClassiqueInput">testClassiqueInput</label>'.
            '<input id="testClassiqueInput" '.
            'type="reset" name="testClassiqueInput" value="Reset" class="form-controler" />'.
            '</div>',
            $printedResult
        );
    }*/
    
    public function testAddText()
    {
        $tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $arr = array(
            'type' => 'text',
            'label' => 'Test',
            'name' => 'testText',
            'mandatory' => false
        );
        $form->addStatic($arr);
        $sm = $form->toSmarty();
        $tpl->assign('form', $sm['testText']['html']);
        $printedResult = $tpl->fetch($this->formTpl);
        $this->assertContains(
            '<div class="form-group "><div class="col-sm-2" style="text-align:right">'.
            '<label class="label-controller" for="testText">Test</label></div>'.
            '<div class="col-sm-9"><span>'.
            '<input id="testText" type="text" name="testText" value="" class="form-control " placeholder="Test" />'.
            '<span></div></div>',
            $printedResult
        );
    }
}
