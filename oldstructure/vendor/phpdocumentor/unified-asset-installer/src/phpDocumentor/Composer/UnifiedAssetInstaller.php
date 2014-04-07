<?php
/**
 * phpDocumentor
 *
 * PHP Version 5
 *
 * @copyright 2010-2013 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

class UnifiedAssetInstaller extends LibraryInstaller
{
    /**
     * Determines the install path for templates,
     *
     * The installation path is determined by checking whether the package is included in another composer configuration
     * or installed as part of the normal phpDocumentor installation.
     *
     * When the package is included as part of a different project it will be installed in the `data/templates` folder
     * of phpDocumentor (thus `/phpdocumentor/phpdocumentor/data/templates`); if it is installed as part of
     * phpDocumentor it will be installed in the root of the project (thus `/data/templates`).
     *
     * @param PackageInterface $package
     *
     * @throws \InvalidArgumentException if the name of the package does not start with `phpdocumentor/template-`.
     *
     * @return string a path relative to the root of the composer.json that is being installed.
     */
    public function getInstallPath(PackageInterface $package)
    {
        if ($this->extractPrefix($package) != 'phpdocumentor/template-') {
            throw new \InvalidArgumentException(
                'Unable to install template, phpdocumentor templates should '
                .'always start their package name with "phpdocumentor/template-"'
            );
        }

        return $this->getTemplateRootPath() . '/' . $this->extractShortName($package);
    }

    /**
     * Extract the first 23 characters of the package name; which is expected to be the prefix.
     *
     * @param PackageInterface $package
     *
     * @return string
     */
    protected function extractPrefix(PackageInterface $package)
    {
        return substr($package->getPrettyName(), 0, 23);
    }

    /**
     * Extract the everything after the first 23 characters of the package name; which is expected to be the short name.
     *
     * @param PackageInterface $package
     *
     * @return string
     */
    protected function extractShortName(PackageInterface $package)
    {
        return substr($package->getPrettyName(), 23);
    }

    /**
     * Returns the root installation path for templates.
     *
     * @return string a path relative to the root of the composer.json that is being installed where the templates
     *     are stored.
     */
    protected function getTemplateRootPath()
    {
        return ($this->composer->getPackage()->getName() === 'phpdocumentor/phpdocumentor')
            ? 'data/templates'
            : $this->vendorDir . '/phpdocumentor/templates'
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
      return (bool)('phpdocumentor-template' === $packageType);
    }
}
