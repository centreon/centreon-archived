<?php
namespace phpDocumentor\Composer;

require __DIR__ . '/../../../../vendor/composer/composer/tests/Composer/Test/TestCase.php';

use Composer\Installer\LibraryInstaller;
use Composer\Util\Filesystem;
use Composer\Test\TestCase;
use Composer\Composer;
use Composer\Config;
use Composer\Package\RootPackage;

/**
 * @covers phpDocumentor\Composer\UnifiedAssetInstaller
 */
class UnifiedAssetInstallerTest extends TestCase
{
    protected $composer;
    protected $config;
    protected $vendorDir;
    protected $binDir;
    protected $dm;
    protected $repository;
    protected $io;
    protected $fs;
    protected $package;

    protected function setUp()
    {
        $this->fs = new Filesystem;

        $this->composer = new Composer();
        $this->config = new Config();
        $this->composer->setConfig($this->config);

        $this->package = new RootPackage('phpdocumentor/phpdocumentor', '2.0.0', '2.0.0');
        $this->composer->setPackage($this->package);

        $this->vendorDir = realpath(sys_get_temp_dir()).DIRECTORY_SEPARATOR
            .'composer-test-vendor';
        $this->ensureDirectoryExistsAndClear($this->vendorDir);

        $this->binDir = realpath(sys_get_temp_dir()).DIRECTORY_SEPARATOR
            .'composer-test-bin';
        $this->ensureDirectoryExistsAndClear($this->binDir);

        $this->config->merge(
            array(
                'config' => array(
                    'vendor-dir' => $this->vendorDir,
                    'bin-dir'    => $this->binDir,
                ),
            )
        );

        $this->dm = $this->getMockBuilder('Composer\Downloader\DownloadManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->composer->setDownloadManager($this->dm);

        $this->repository = $this->getMock(
            'Composer\Repository\InstalledRepositoryInterface'
        );
        $this->io = $this->getMock('Composer\IO\IOInterface');
    }

    protected function tearDown()
    {
        $this->fs->removeDirectory($this->vendorDir);
        $this->fs->removeDirectory($this->binDir);
    }

    /**
     * @covers phpDocumentor\Composer\UnifiedAssetInstaller::getInstallPath
     */
    public function testGetInstallPath()
    {
        $library = new UnifiedAssetInstaller($this->io, $this->composer);
        $package = $this->createPackageMock();

        // UnifiedAssetInstaller does not support targetDir
        $package
            ->expects($this->never())
            ->method('getTargetDir');

        $package
            ->expects($this->atLeastOnce())
            ->method('getPrettyName')
            ->will($this->returnValue('phpdocumentor/template-mock'));

        $this->assertEquals(
            'data/templates/mock',
            $library->getInstallPath($package)
        );
    }


    /**
     * @covers phpDocumentor\Composer\UnifiedAssetInstaller::getInstallPath
     */
    public function testGetInstallPathWhenVendored()
    {
        $composer = $this->createComposerMock();
        $rootPackage = new RootPackage('not/phpdocumentor-base-install', '1.0.0.0', '1.0.0');
        $composer->setPackage($rootPackage);

        $library = new UnifiedAssetInstaller($this->io, $composer);

        $package = $this->createPackageMock();

        $package
            ->expects($this->atLeastOnce())
            ->method('getPrettyName')
            ->will($this->returnValue('phpdocumentor/template-mock'));

        $this->assertEquals(
            $this->vendorDir.'/phpdocumentor/templates/mock',
            $library->getInstallPath($package)
        );
    }

    /**
     * @covers phpDocumentor\Composer\UnifiedAssetInstaller::getInstallPath
     * @expectedException InvalidArgumentException
     */
    public function testGetInstallPathWithInvalidPackageName()
    {
        $library = new UnifiedAssetInstaller($this->io, $this->composer);
        $package = $this->createPackageMock();

        $package
            ->expects($this->once())
            ->method('getPrettyName')
            ->will($this->returnValue('a/b'));

        $library->getInstallPath($package);
    }

    /**
     * @covers phpDocumentor\Composer\UnifiedAssetInstaller::supports
     */
    public function testSupports()
    {
        $library = new UnifiedAssetInstaller($this->io, $this->composer);

        $this->assertTrue($library->supports('phpdocumentor-template'));
        $this->assertFalse($library->supports('phpdocumentor-plugin'));
        $this->assertFalse($library->supports('library'));
    }

    protected function createPackageMock()
    {
        return $this->getMockBuilder('Composer\Package\Package')
            ->setConstructorArgs(array(md5(rand()), '1.0.0.0', '1.0.0'))
            ->getMock();
    }

    protected function createComposerMock()
    {
        $composer = new Composer();
        $composer->setConfig($this->config);
        $composer->setDownloadManager($this->dm);

        return $composer;
    }
}
