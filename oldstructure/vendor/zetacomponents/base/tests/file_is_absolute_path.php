<?php
/**
 * @copyright Copyright (C) 2005-2009 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogentag//
 * @filesource
 * @package Base
 * @subpackage Tests
 */

/**
 * @package Base
 * @subpackage Tests
 */
class ezcBaseFileIsAbsoluteTest extends ezcTestCase
{
    public static function testAbsoluteWindows1()
    {
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c:\\winnt\\winnt.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c:\winnt\winnt.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c:\\winnt', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c:\\winnt.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c:\winnt.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c:\\winnt.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c:\table.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:winnt', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c\\winnt.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '\\winnt.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '\winnt.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'winnt.sys', 'Windows' ) );

        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '\\server\share\foo.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\\\server\share\foo.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '\\tequila\share\foo.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\\\tequila\share\foo.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '\\tequila\thare\foo.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\\\tequila\thare\foo.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '\\server\\share\foo.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\\\server\\share\foo.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '\\tequila\\share\foo.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\\\tequila\\share\foo.sys', 'Windows' ) );

        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '\etc\init.d\apache', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '\\etc\\init.d\\apache', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'etc\init.d\apache', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'etc\\init.d\\apache', 'Windows' ) );
    }

    public static function testAbsoluteWindows2()
    {
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c://winnt//winnt.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c:/winnt/winnt.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c://winnt', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c://winnt.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c:/winnt.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c://winnt.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c:/table.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:winnt', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c//winnt.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '//winnt.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '/winnt.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'winnt.sys', 'Windows' ) );

        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//server/share/foo.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '////server/share/foo.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//tequila/share/foo.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '////tequila/share/foo.sys', 'Windows' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//tequila/thare/foo.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '////tequila/thare/foo.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '//server//share/foo.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '////server//share/foo.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '//tequila//share/foo.sys', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '////tequila//share/foo.sys', 'Windows' ) );

        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '/etc/init.d/apache', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '//etc//init.d//apache', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'etc/init.d/apache', 'Windows' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'etc//init.d//apache', 'Windows' ) );
    }

    public static function testAbsoluteWindows3()
    {
        if ( ezcBaseFeatures::os() !== 'Windows' )
        {
            self::markTestSkipped( 'Test is for Windows only' );
        }

        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c://winnt//winnt.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c:/winnt/winnt.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c://winnt' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c://winnt.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c:/winnt.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c://winnt.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'c:/table.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:winnt' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c//winnt.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '//winnt.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '/winnt.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'winnt.sys' ) );

        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//server/share/foo.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '////server/share/foo.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//tequila/share/foo.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '////tequila/share/foo.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//tequila/thare/foo.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '////tequila/thare/foo.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '//server//share/foo.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '////server//share/foo.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '//tequila//share/foo.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '////tequila//share/foo.sys' ) );

        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '/etc/init.d/apache' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( '//etc//init.d//apache' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'etc/init.d/apache' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'etc//init.d//apache' ) );
    }

    public static function testAbsoluteLinux1()
    {
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:\\winnt\\winnt.sys', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:\winnt\winnt.sys', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:\\winnt', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:\\winnt.sys', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:\winnt.sys', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:\\winnt.sys', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:\table.sys', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:winnt', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c\\winnt.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\winnt.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\winnt.sys', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'winnt.sys', 'Linux' ) );

        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\server\share\foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\\\server\share\foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\tequila\share\foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\\\tequila\share\foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\tequila\thare\foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\\\tequila\thare\foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\server\\share\foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\\\server\\share\foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\tequila\\share\foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\\\tequila\\share\foo.sys', 'Linux' ) );

        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\etc\init.d\apache', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '\\etc\\init.d\\apache', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'etc\init.d\apache', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'etc\\init.d\\apache', 'Linux' ) );
    }

    public static function testAbsoluteLinux2()
    {
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c://winnt//winnt.sys', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:/winnt/winnt.sys', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c://winnt', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c://winnt.sys', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:/winnt.sys', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c://winnt.sys', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:/table.sys', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:winnt', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c//winnt.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//winnt.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '/winnt.sys', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'winnt.sys', 'Linux' ) );

        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//server/share/foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '////server/share/foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//tequila/share/foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '////tequila/share/foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//tequila/thare/foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '////tequila/thare/foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//server//share/foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '////server//share/foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//tequila//share/foo.sys', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '////tequila//share/foo.sys', 'Linux' ) );

        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '/etc/init.d/apache', 'Linux' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//etc//init.d//apache', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'etc/init.d/apache', 'Linux' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'etc//init.d//apache', 'Linux' ) );
    }

    public static function testAbsoluteLinux3()
    {
        if ( ezcBaseFeatures::os() === 'Windows' )
        {
            self::markTestSkipped( 'Test is for unix-like systems only' );
        }

        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c://winnt//winnt.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:/winnt/winnt.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c://winnt' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c://winnt.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:/winnt.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c://winnt.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:/table.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c:winnt' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'c//winnt.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//winnt.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '/winnt.sys' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'winnt.sys' ) );

        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//server/share/foo.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '////server/share/foo.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//tequila/share/foo.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '////tequila/share/foo.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//tequila/thare/foo.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '////tequila/thare/foo.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//server//share/foo.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '////server//share/foo.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//tequila//share/foo.sys' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '////tequila//share/foo.sys' ) );

        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '/etc/init.d/apache' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( '//etc//init.d//apache' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'etc/init.d/apache' ) );
        self::assertEquals( false, ezcBaseFile::isAbsolutePath( 'etc//init.d//apache' ) );
    }

    public static function testAbsoluteStreamWrapper()
    {
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'phar://test.phar/foo' ) );
        self::assertEquals( true, ezcBaseFile::isAbsolutePath( 'http://example.com/file' ) );
    }

    public static function suite()
    {
         return new PHPUnit_Framework_TestSuite( "ezcBaseFileIsAbsoluteTest" );
    }
}
?>
