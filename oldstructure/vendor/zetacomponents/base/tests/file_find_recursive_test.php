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
class ezcBaseFileFindRecursiveTest extends ezcTestCase
{
    public function testRecursive1()
    {
        $expected = array (
            0 => 'File/CREDITS',
            1 => 'File/ChangeLog',
            2 => 'File/DESCRIPTION',
            3 => 'File/design/class_diagram.png',
            4 => 'File/design/design.txt',
            5 => 'File/design/file.xml',
            6 => 'File/design/file_operations.png',
            7 => 'File/design/md5.png',
            8 => 'File/design/requirements.txt',
            9 => 'File/src/file.php',
            10 => 'File/src/file_autoload.php',
            11 => 'File/tests/file_calculate_relative_path_test.php',
            12 => 'File/tests/file_find_recursive_test.php',
            13 => 'File/tests/file_remove_recursive_test.php',
            14 => 'File/tests/suite.php',
        );
        self::assertEquals( $expected, ezcBaseFile::findRecursive( "File", array(), array( '@/docs/@', '@svn@', '@\.swp$@' ), $stats ) );
        self::assertEquals( array( 'size' => 134399, 'count' => 15 ), $stats );
    }

    public function testRecursive2()
    {
        $expected = array (
            0 => './File/CREDITS',
            1 => './File/ChangeLog',
            2 => './File/DESCRIPTION',
            3 => './File/design/class_diagram.png',
            4 => './File/design/design.txt',
            5 => './File/design/file.xml',
            6 => './File/design/file_operations.png',
            7 => './File/design/md5.png',
            8 => './File/design/requirements.txt',
            9 => './File/src/file.php',
            10 => './File/src/file_autoload.php',
            11 => './File/tests/file_calculate_relative_path_test.php',
            12 => './File/tests/file_find_recursive_test.php',
            13 => './File/tests/file_remove_recursive_test.php',
            14 => './File/tests/suite.php',
        );
        self::assertEquals( $expected, ezcBaseFile::findRecursive( ".", array( '@^\./File/@' ), array( '@/docs/@', '@\.svn@', '@\.swp$@' ), $stats ) );
        self::assertEquals( array( 'size' => 134399, 'count' => 15 ), $stats );
    }

    public function testRecursive3()
    {
        $expected = array (
            0 => 'File/design/class_diagram.png',
            1 => 'File/design/file_operations.png',
            2 => 'File/design/md5.png',
        );
        self::assertEquals( $expected, ezcBaseFile::findRecursive( "File", array( '@\.png$@' ), array( '@\.svn@' ), $stats ) );
        self::assertEquals( array( 'size' => 20859, 'count' => 3 ), $stats );
    }

    public function testRecursive4()
    {
        $expected = array (
            0 => 'File/design/class_diagram.png',
            1 => 'File/design/design.txt',
            2 => 'File/design/file.xml',
            3 => 'File/design/file_operations.png',
            4 => 'File/design/md5.png',
            5 => 'File/design/requirements.txt',
        );
        self::assertEquals( $expected, ezcBaseFile::findRecursive( "File", array( '@/design/@' ), array( '@\.svn@' ), $stats ) );
        self::assertEquals( array( 'size' => 117499, 'count' => 6 ), $stats );
    }

    public function testRecursive5()
    {
        $expected = array (
            0 => 'File/design/design.txt',
            1 => 'File/design/requirements.txt',
            2 => 'File/src/file.php',
            3 => 'File/src/file_autoload.php',
            4 => 'File/tests/file_calculate_relative_path_test.php',
            5 => 'File/tests/file_find_recursive_test.php',
            6 => 'File/tests/file_remove_recursive_test.php',
            7 => 'File/tests/suite.php',
        );
        self::assertEquals( $expected, ezcBaseFile::findRecursive( "File", array( '@\.(php|txt)$@' ), array( '@/docs/@', '@\.svn@' ) ) );
    }

    public function testRecursive6()
    {
        $expected = array();
        self::assertEquals( $expected, ezcBaseFile::findRecursive( "File", array( '@xxx@' ) ) );
    }

    public function testNonExistingDirectory()
    {
        $expected = array();
        try
        {
            ezcBaseFile::findRecursive( "NotHere", array( '@xxx@' ) );
        }
        catch ( ezcBaseFileNotFoundException $e )
        {
            self::assertEquals( "The directory file 'NotHere' could not be found.", $e->getMessage() );
        }
    }

    public function testStatsEmptyArray()
    {
        $expected = array (
            0 => 'File/design/class_diagram.png',
            1 => 'File/design/design.txt',
            2 => 'File/design/file.xml',
            3 => 'File/design/file_operations.png',
            4 => 'File/design/md5.png',
            5 => 'File/design/requirements.txt',
        );
        $stats = array();
        self::assertEquals( $expected, ezcBaseFile::findRecursive( "File", array( '@/design/@' ), array( '@\.svn@' ), $stats ) );
        self::assertEquals( array( 'size' => 117499, 'count' => 6 ), $stats );
    }

    public static function suite()
    {
         return new PHPUnit_Framework_TestSuite( "ezcBaseFileFindRecursiveTest" );
    }
}
?>
