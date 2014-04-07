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
class ezcBaseMetaDataPearTest extends ezcTestCase
{
    public static function testConstruct()
    {
        $r = new ezcBaseMetaData( 'pear' );
        self::assertType( 'ezcBaseMetaData', $r );
        self::assertType( 'ezcBaseMetaDataPearReader', self::readAttribute( $r, 'reader' ) );
    }

    public static function testGetBundleVersion()
    {
        $r = new ezcBaseMetaData( 'pear' );
        $release = $r->getBundleVersion();
        self::assertType( 'string', $release );
        self::assertRegexp( '@[0-9]{4}\.[0-9](\.[0-9])?@', $release );
    }

    public static function testIsComponentInstalled()
    {
        $r = new ezcBaseMetaData( 'pear' );
        self::assertTrue( $r->isComponentInstalled( 'Base' ) );
        self::assertFalse( $r->isComponentInstalled( 'DefinitelyNot' ) );
    }

    public static function testGetComponentVersion()
    {
        $r = new ezcBaseMetaData( 'pear' );
        $release = $r->getComponentVersion( 'Base' );
        self::assertType( 'string', $release );
        self::assertRegexp( '@[0-9]\.[0-9](\.[0-9])?@', $release );
        self::assertFalse( $r->getComponentVersion( 'DefinitelyNot' ) );
    }

    public static function testGetComponentDependencies1()
    {
        $r = new ezcBaseMetaData( 'pear' );
        $deps = array_keys( $r->getComponentDependencies() );
        self::assertContains( 'Base', $deps );
        self::assertContains( 'Cache', $deps );
        self::assertContains( 'Webdav', $deps );
        self::assertNotContains( 'Random', $deps );
    }

    public static function testGetComponentDependencies2()
    {
        $r = new ezcBaseMetaData( 'pear' );
        self::assertSame( array(), $r->getComponentDependencies( 'Base' ) );
        self::assertSame( array( 'Base' ), array_keys( $r->getComponentDependencies( 'Template' ) ) );
    }

    public static function testGetComponentDependencies3()
    {
        $r = new ezcBaseMetaData( 'pear' );
        self::assertContains( 'Base', array_keys( $r->getComponentDependencies( 'TemplateTranslationTiein' ) ) );
        self::assertContains( 'Template', array_keys( $r->getComponentDependencies( 'TemplateTranslationTiein' ) ) );
        self::assertContains( 'Translation', array_keys( $r->getComponentDependencies( 'TemplateTranslationTiein' ) ) );
    }

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite( 'ezcBaseMetaDataPearTest' );
    }
}
?>
