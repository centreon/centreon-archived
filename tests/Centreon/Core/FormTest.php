<?php
namespace Test\Centreon\Core;

use Centreon\Core\Config,
    Centreon\Core\Di,
    Centreon\Core\Template,
    Centreon\Core\Form;

class FormTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $config = new Config(DATA_DIR . '/test-template.ini');
        $di = new Di();
        $di->setShared('config', $config);
        $tpl = new Template();
        $di->setShared('template', $tpl);
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
        $form->add('testClassiqueInput', 'button' , _("Save"), array("onClick" => "validForm();"));
        $tpl->assign('form', $form->toSmarty());
        $printedResult = $tpl->fetch('form/testAddButton.tpl');
        $this->assertContains(
            '<input '
            .'id="testClassiqueInput" '
            . 'name="testClassiqueInput" '
            . 'value="Save" '
            . 'type="button" '
            . '/>',
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
        $printedResult = $tpl->fetch('form/testAddReset.tpl');
        $this->assertContains(
            '<input '
            . 'name="testClassiqueInput" '
            . 'value="Reset" '
            . 'type="reset" '
            .'id="testClassiqueInput" '
            . '/>',
            $printedResult
        );
    }
    
    public function testAddText()
    {
        $tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $form->add('testClassiqueInput');
        $tpl->assign('form', $form->toSmarty());
        $printedResult = $tpl->fetch('form/testAddText.tpl');
        $this->assertContains(
            '<input '
            . 'name="testClassiqueInput" '
            . 'type="text" '
            . 'id="testClassiqueInput" '
            . 'class="input-medium" '
            . 'label="testClassiqueInput" '
            . '/>',
            $printedResult
        );
    }
}
