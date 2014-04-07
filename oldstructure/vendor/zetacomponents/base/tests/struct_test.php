<?php
/**
 * @package Base
 * @subpackage Tests
 * @version //autogentag//
 * @copyright Copyright (C) 2005-2009 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * @package Base
 * @subpackage Tests
 */
class ezcBaseStructTest extends ezcTestCase
{
    public function testBaseStructGetSet()
    {
        $struct = new ezcBaseStruct();

        try
        {
            $struct->no_such_property = 'value';
            $this->fail( 'Expected exception was not thrown.' );
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $this->assertEquals( "No such property name 'no_such_property'.", $e->getMessage() );
        }

        try
        {
            $value = $struct->no_such_property;
            $this->fail( 'Expected exception was not thrown.' );
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $this->assertEquals( "No such property name 'no_such_property'.", $e->getMessage() );
        }
    }

    public function testBaseRepositoryDirectorySetState()
    {
        $dir = ezcBaseRepositoryDirectory::__set_state( array( 'type' => ezcBaseRepositoryDirectory::TYPE_EXTERNAL, 'basePath' => '/tmp', 'autoloadPath' => '/tmp/autoload' ) );
        $this->assertEquals( ezcBaseRepositoryDirectory::TYPE_EXTERNAL, $dir->type );
        $this->assertEquals( '/tmp', $dir->basePath );
        $this->assertEquals( '/tmp/autoload', $dir->autoloadPath );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite( "ezcBaseStructTest" );
    }
}
?>
