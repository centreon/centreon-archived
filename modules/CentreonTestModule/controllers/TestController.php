<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TestControler
 *
 * @author bsauveton
 */


namespace CentreonTest\Controllers;
use Centreon\Controllers\FormController;


class TestController extends FormController
{
 protected $objectDisplayName = 'Test';   
protected $repository = '\CentreonTest\Repository\TestRepository';
public static $objectName = 'test';
protected $objectClass = '';
 protected $datatableObject = '';


    public static $relationMap = array(

    );
    /**
     * 
     * @method get
     * @route /test
     */
    public function testAction()
    {
        echo 'test';
    }
    //put your code here
}
