<?php
/**
 * @package Base
 * @subpackage Tests
 * @version //autogentag//
 * @copyright Copyright (C) 2005-2009 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

require_once dirname( __FILE__ ) . '/test_options.php';

/**
 * @package Base
 * @subpackage Tests
 */
class ezcBaseOptionsTest extends ezcTestCase
{
    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite("ezcBaseOptionsTest");
    }

    public function testGetAccessFailure()
    {
        $opt = new ezcBaseTestOptions();
        try
        {
            echo $opt->properties;
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return;
        }
        $this->fail( "ezcBasePropertyNotFoundException not thrown on access to forbidden property \$properties" );
    }

    public function testGetOffsetAccessFailure()
    {
        $opt = new ezcBaseTestOptions();
        try
        {
            echo $opt["properties"];
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return;
        }
        $this->fail( "ezcBasePropertyNotFoundException not thrown on access to forbidden property \$properties" );
    }

    public function testSetOffsetAccessFailure()
    {
        $opt = new ezcBaseTestOptions();
        try
        {
            $opt["properties"] = "foo";
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            return;
        }
        $this->fail( "ezcBasePropertyNotFoundException not thrown on access to forbidden property \$properties" );
    }

    public function testConstructorWithParameters()
    {
        $options = new ezcBaseTestOptions( array( 'foo' => 'xxx' ) );
        $this->assertEquals( 'xxx', $options->foo );
    }

    public function testMerge()
    {
        $options = new ezcBaseTestOptions();
        $this->assertEquals( 'bar', $options->foo );
        $options->merge( array( 'foo' => 'xxx' ) );
        $this->assertEquals( 'xxx', $options->foo );
    }

    public function testOffsetExists()
    {
        $options = new ezcBaseTestOptions();
        $this->assertEquals( true, $options->offsetExists( 'foo' ) );
        $this->assertEquals( false, $options->offsetExists( 'bar' ) );
    }

    public function testOffsetSet()
    {
        $options = new ezcBaseTestOptions();
        $this->assertEquals( 'bar', $options->foo );
        $options->offsetSet( 'foo', 'xxx' );
        $this->assertEquals( 'xxx', $options->foo );
    }

    public function testOffsetUnset()
    {
        $options = new ezcBaseTestOptions();
        $this->assertEquals( 'bar', $options->foo );
        $options->offsetUnset( 'foo' );
        $this->assertEquals( null, $options->foo );
        $this->assertEquals( true, $options->offsetExists( 'foo' ) );
    }

    public function testAutoloadOptions()
    {
        $options = new ezcBaseAutoloadOptions();

        try
        {
            $options->no_such_property = 'value';
            $this->fail( 'Expected exception was not thrown.' );
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $this->assertEquals( "No such property name 'no_such_property'.", $e->getMessage() );
        }

        try
        {
            $options->preload = 'wrong value';
            $this->fail( 'Expected exception was not thrown.' );
        }
        catch ( ezcBaseValueException $e )
        {
            $this->assertEquals( "The value 'wrong value' that you were trying to assign to setting 'preload' is invalid. Allowed values are: bool.", $e->getMessage() );
        }
    }
}

?>
