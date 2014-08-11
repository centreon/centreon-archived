<?php
namespace Test\Centreon;

use Centreon\Internal\Config;
use Centreon\Internal\Di;
use Centreon\Internal\Template;
use Centreon\Internal\Form;
use Centreon\Internal\Router;

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
        $di->set(
            'router',
            function () {
                $modulesToParse = array();
                foreach (glob(CENTREON_PATH . "/modules/*Module") as $moduleTemplateDir) {
                    $modulesToParse[] = basename($moduleTemplateDir);
                }
                $router = new Router();
                $router->parseRoutes($modulesToParse);
                return $router;
            }
        );
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
            '<div class="form-group "><div class="col-sm-2" style="text-align:right">'
            . '<label class="label-controller" for="testCheckbox">Simple checkbox</label>'
            . '</div><div class="col-sm-9"><label class="label-controller" for="testCheckbox1">&nbsp;'
            . '<input id="testCheckbox1" type="checkbox" name="testCheckbox" value=1  /> test</label>'
            . '&nbsp;&nbsp;</div></div>',
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

    /**
     *
     * @todo
     */
    public function testAddEmail()
    {
        /*
        $tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $arr = array(
            'type' => 'email',
            'label' => 'Test',
            'name' => 'testEmail',
            'mandatory' => false
        );
        $form->addStatic($arr);
        $sm = $form->toSmarty();
        $tpl->assign('form', $sm['testEmail']['html']);
        $printedResult = $tpl->fetch($this->formTpl);
        $this->assertContains(
            '<div class="form-group "><div class="col-sm-2" style="text-align:right">'.
            '<label class="label-controller" for="testEmail">Test</label></div>'.
            '<div class="col-sm-9"><span>'.
            '<input id="testEmail" type="text" name="testEmail" value="" class="form-control " placeholder="Test" />'.
            '<span></div></div>',
            $printedResult
        );
         */
    }

    public function testAddFloat()
    {
        $tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $arr = array(
            'type' => 'float',
            'label' => 'Test',
            'name' => 'testFloat',
            'mandatory' => false
        );
        $form->addStatic($arr);
        $sm = $form->toSmarty();
        $tpl->assign('form', $sm['testFloat']['html']);
        $printedResult = $tpl->fetch($this->formTpl);
        $this->assertContains(
            '<div class="form-group "><div class="col-sm-2" style="text-align:right">'.
            '<label class="label-controller" for="testFloat">Test</label></div>'.
            '<div class="col-sm-9"><span>'.
            '<input id="testFloat" type="text" name="testFloat" value="" class="form-control " placeholder="Test" />'.
            '<span></div></div>',
            $printedResult
        );
    }

    public function testAddInt()
    {
        $tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $arr = array(
            'type' => 'integer',
            'label' => 'Test',
            'name' => 'testInt',
            'mandatory' => false
        );
        $form->addStatic($arr);
        $sm = $form->toSmarty();
        $tpl->assign('form', $sm['testInt']['html']);
        $printedResult = $tpl->fetch($this->formTpl);
        $this->assertContains(
            '<div class="form-group "><div class="col-sm-2" style="text-align:right">'.
            '<label class="label-controller" for="testInt">Test</label></div>'.
            '<div class="col-sm-9"><span>'.
            '<input id="testInt" type="text" name="testInt" value="" class="form-control " placeholder="Test" />'.
            '<span></div></div>',
            $printedResult
        );
    }

    /**
     *
     * @todo
     */
    public function testAddIp()
    {
        /*$tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $arr = array(
            'type' => 'ipaddress',
            'label' => 'Test',
            'name' => 'testIp',
            'mandatory' => false
        );
        $form->addStatic($arr);
        $sm = $form->toSmarty();
        $tpl->assign('form', $sm['testIp']['html']);
        $printedResult = $tpl->fetch($this->formTpl);
        $this->assertContains(
            '<div class="form-group "><div class="col-sm-2" style="text-align:right">'.
            '<label class="label-controller" for="testIp">Test</label></div>'.
            '<div class="col-sm-9"><span>'.
            '<input id="testIp" type="text" name="testIp" value="" class="form-control " placeholder="Test" />'.
            '<span></div></div>',
            $printedResult
        );*/
    }

    /**
     *
     * @todo
     */
    public function testAddPassword()
    {
        /*$tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $arr = array(
            'type' => 'password',
            'label' => 'Test',
            'name' => 'testPass',
            'mandatory' => false
        );
        $form->addStatic($arr);
        $sm = $form->toSmarty();
        $tpl->assign('form', $sm['testPass']['html']);
        $printedResult = $tpl->fetch($this->formTpl);
        $this->assertContains(
            '<div class="form-group "><div class="col-sm-2" style="text-align:right">'.
            '<label class="label-controller" for="testPass">Test</label></div>'.
            '<div class="col-sm-9">'.
            '<input id="testPass" type="password" name="testPass" class="form-control" placeholder="testPass" />'.
            '</div></div>',
            $printedResult
        );*/
    }

    /**
     *
     * @todo
     */
    public function testSelect()
    {

    }

    public function testAddTextarea()
    {
        $tpl = Di::getDefault()->get('template');
        $form = new Form('testForm');
        $arr = array(
            'type' => 'textarea',
            'label' => 'Test',
            'name' => 'testTextarea',
            'mandatory' => false
        );
        $form->addStatic($arr);
        $sm = $form->toSmarty();
        $tpl->assign('form', $sm['testTextarea']['html']);
        $printedResult = $tpl->fetch($this->formTpl);
        $this->assertContains(
            '<div class="form-group "><div class="col-sm-2" style="text-align:right">'
            . '<label class="label-controller" for="testTextarea">Test</label></div>'
            . '<div class="col-sm-9">'
            . '<textarea id="testTextarea" '
            . 'name="testTextarea" class="form-control " rows="3" placeholder="testTextarea" >'
            . ' </textarea></div></div>',
            $printedResult
        );
    }
}
