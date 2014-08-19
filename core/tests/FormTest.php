<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

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
