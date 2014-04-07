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
class ezcBaseFileCopyRecursiveTest extends ezcTestCase
{
    protected function setUp()
    {
        $this->tempDir = $this->createTempDir( __CLASS__ );
        mkdir( $this->tempDir . '/dir1' );
        mkdir( $this->tempDir . '/dir2' );
        mkdir( $this->tempDir . '/dir2/dir1' );
        mkdir( $this->tempDir . '/dir2/dir1/dir1' );
        mkdir( $this->tempDir . '/dir2/dir2' );
        mkdir( $this->tempDir . '/dir4' );
        mkdir( $this->tempDir . '/dir5' );
        mkdir( $this->tempDir . '/dir6' );
        mkdir( $this->tempDir . '/dir7' );
        mkdir( $this->tempDir . '/dir7/0' );
        file_put_contents( $this->tempDir . '/dir1/file1.txt', 'test' );
        file_put_contents( $this->tempDir . '/dir1/file2.txt', 'test' );
        file_put_contents( $this->tempDir . '/dir1/.file3.txt', 'test' );
        file_put_contents( $this->tempDir . '/dir2/file1.txt', 'test' );
        file_put_contents( $this->tempDir . '/dir2/dir1/file1.txt', 'test' );
        file_put_contents( $this->tempDir . '/dir2/dir1/dir1/file1.txt', 'test' );
        file_put_contents( $this->tempDir . '/dir2/dir1/dir1/file2.txt', 'test' );
        file_put_contents( $this->tempDir . '/dir2/dir2/file1.txt', 'test' );
        file_put_contents( $this->tempDir . '/dir4/file1.txt', 'test' );
        file_put_contents( $this->tempDir . '/dir4/file2.txt', 'test' );
        file_put_contents( $this->tempDir . '/dir5/file1.txt', 'test' );
        file_put_contents( $this->tempDir . '/dir5/file2.txt', 'test' );
        file_put_contents( $this->tempDir . '/dir6/file1.txt', 'test' );
        file_put_contents( $this->tempDir . '/dir6/file2.txt', 'test' );
        chmod( $this->tempDir . '/dir4/file1.txt', 0 );
        chmod( $this->tempDir . '/dir5', 0 );
        chmod( $this->tempDir . '/dir6', 0400 );
    }

    protected function tearDown()
    {
        chmod( $this->tempDir . '/dir5', 0700 );
        chmod( $this->tempDir . '/dir6', 0700 );
        $this->removeTempDir();
    }

    public function testRecursiveCopyEmptyDir()
    {
        ezcBaseFile::copyRecursive( 
            $this->tempDir . '/dir1',
            $this->tempDir . '/dest'
        );

        $this->assertEquals( 
            count( ezcBaseFile::findRecursive( $this->tempDir . '/dir1' ) ),
            count( ezcBaseFile::findRecursive( $this->tempDir . '/dest' ) ) 
        );

        $this->assertSame(
            0775,
            fileperms( $this->tempDir . '/dest' ) & 0777,
            'Directory mode should equal 0775.'
        );
    }

    public function testRecursiveCopyFile()
    {
        ezcBaseFile::copyRecursive( 
            $this->tempDir . '/dir1/file1.txt',
            $this->tempDir . '/dest'
        );

        $this->assertTrue(
            is_file( $this->tempDir . '/dest' )
        );

        $this->assertSame(
            0664,
            fileperms( $this->tempDir . '/dest' ) & 0777,
            'File mode should equal 0664.'
        );
    }

    public function testRecursiveCopyEmptyDirMode()
    {
        ezcBaseFile::copyRecursive( 
            $this->tempDir . '/dir1',
            $this->tempDir . '/dest',
            -1,
            0777,
            0777
        );

        $this->assertEquals( 
            count( ezcBaseFile::findRecursive( $this->tempDir . '/dir1' ) ),
            count( ezcBaseFile::findRecursive( $this->tempDir . '/dest' ) ) 
        );

        $this->assertSame(
            0777,
            fileperms( $this->tempDir . '/dest' ) & 0777,
            'Directory mode should equal 0777.'
        );
    }

    public function testRecursiveCopyFileMode()
    {
        ezcBaseFile::copyRecursive( 
            $this->tempDir . '/dir1/file1.txt',
            $this->tempDir . '/dest',
            -1,
            0777,
            0777
        );

        $this->assertTrue(
            is_file( $this->tempDir . '/dest' )
        );

        $this->assertSame(
            0777,
            fileperms( $this->tempDir . '/dest' ) & 0777,
            'File mode should equal 0777.'
        );
    }

    public function testRecursiveCopyFullDir()
    {
        ezcBaseFile::copyRecursive( 
            $this->tempDir . '/dir2',
            $this->tempDir . '/dest'
        );

        $this->assertEquals( 
            count( ezcBaseFile::findRecursive( $this->tempDir . '/dir2' ) ),
            count( ezcBaseFile::findRecursive( $this->tempDir . '/dest' ) ) 
        );
    }

    public function testRecursiveCopyFullDirDepthZero()
    {
        ezcBaseFile::copyRecursive( 
            $this->tempDir . '/dir2',
            $this->tempDir . '/dest',
            0
        );

        $this->assertEquals( 
            0,
            count( ezcBaseFile::findRecursive( $this->tempDir . '/dest' ) ) 
        );

        $this->assertTrue(
            is_dir( $this->tempDir . '/dest' )
        );
    }

    public function testRecursiveCopyFullDirLimitedDepth()
    {
        ezcBaseFile::copyRecursive( 
            $this->tempDir . '/dir2',
            $this->tempDir . '/dest',
            2
        );

        $this->assertEquals( 
            3,
            count( ezcBaseFile::findRecursive( $this->tempDir . '/dest' ) ) 
        );
    }

    public function testRecursiveCopyFailureNotExisting()
    {
        try
        {
            ezcBaseFile::copyRecursive( 
                $this->tempDir . '/not_existing',
                $this->tempDir . '/dest'
            );
        }
        catch ( ezcBaseFileNotFoundException $e )
        {
            return;
        }

        $this->fail( 'Expected ezcBaseFileNotFoundException.' );
    }

    public function testRecursiveCopyFailureNotReadable()
    {
        ezcBaseFile::copyRecursive( 
            $this->tempDir . '/dir5',
            $this->tempDir . '/dest'
        );

        $this->assertFalse(
            is_dir( $this->tempDir . '/dest' )
        );

        $this->assertFalse(
            is_file( $this->tempDir . '/dest' )
        );
    }

    public function testRecursiveCopyFailureNotWriteable()
    {
        try
        {
            ezcBaseFile::copyRecursive( 
                $this->tempDir . '/dir2',
                $this->tempDir . '/dir4'
            );
        }
        catch ( ezcBaseFilePermissionException $e )
        {
            return;
        }

        $this->fail( 'Expected ezcBaseFilePermissionException.' );
    }

    public static function suite()
    {
         return new PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    public function testRecursiveCopyDirCalled0()
    {
        ezcBaseFile::copyRecursive( 
            $this->tempDir . '/dir7',
            $this->tempDir . '/dest'
        );

        $this->assertEquals( 
            count( ezcBaseFile::findRecursive( $this->tempDir . '/dir7' ) ),
            count( ezcBaseFile::findRecursive( $this->tempDir . '/dest' ) ) 
        );

        $this->assertTrue( is_dir( $this->tempDir . '/dest/0' ) );
    }
}
?>
