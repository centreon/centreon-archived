<?php
/**
 * @package Base
 * @subpackage Tests
 * @version //autogentag//
 * @copyright Copyright (C) 2005-2009 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

require_once 'init/base_init_callback.php';
require_once 'init/base_init_class.php';

/**
 * @package Base
 * @subpackage Tests
 */
class ezcBaseInitTest extends ezcTestCase
{
    public function setUp()
    {
        testBaseInitClass::$instance = null;
    }

    public function testCallbackWithClassThatDoesNotExists()
    {
        try
        {
            ezcBaseInit::setCallback( 'testBaseInit', 'classDoesNotExist' );
            $this->fail( "Expected exception not thrown." );
        }
        catch ( ezcBaseInitInvalidCallbackClassException $e )
        {
            $this->assertEquals( "Class 'classDoesNotExist' does not exist, or does not implement the 'ezcBaseConfigurationInitializer' interface.", $e->getMessage() );
        }
    }

    public function testCallbackWithClassThatDoesNotImplementTheInterface()
    {
        try
        {
            ezcBaseInit::setCallback( 'testBaseInit', 'ezcBaseFeatures' );
            $this->fail( "Expected exception not thrown." );
        }
        catch ( ezcBaseInitInvalidCallbackClassException $e )
        {
            $this->assertEquals( "Class 'ezcBaseFeatures' does not exist, or does not implement the 'ezcBaseConfigurationInitializer' interface.", $e->getMessage() );
        }
    }

    public function testCallback1()
    {
        $obj = testBaseInitClass::getInstance();
        $this->assertEquals( false, $obj->configured );
    }

    public function testCallback2()
    {
        ezcBaseInit::setCallback( 'testBaseInit', 'testBaseInitCallback' );
        $obj = testBaseInitClass::getInstance();
        $this->assertEquals( true, $obj->configured );
    }
    
    public function testCallback3()
    {
        try
        {
            ezcBaseInit::setCallback( 'testBaseInit', 'testBaseInitCallback' );
            $this->fail( "Expected exception not thrown." );
        }
        catch ( ezcBaseInitCallbackConfiguredException $e )
        {
            $this->assertEquals( "The 'testBaseInit' is already configured with callback class 'testBaseInitCallback'.", $e->getMessage() );
        }
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite("ezcBaseInitTest");
    }
}
?>
