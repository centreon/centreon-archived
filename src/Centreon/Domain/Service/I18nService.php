<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */
namespace Centreon\Domain\Service;

use CentreonLegacy\Core\Module\Information;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class to manage translation of centreon and its extensions
 */
class I18nService
{
    /**
     * @var Information
     */
    private $modulesInformation;

    /**
     * @var String
     */
    private $lang;

    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * I18nService constructor
     *
     * @param Information $modulesInformation To get information from centreon modules
     */
    public function __construct(Information $modulesInformation, Finder $finder, Filesystem $filesystem)
    {
        $this->modulesInformation = $modulesInformation;
        $this->initLang();
        $this->finder = $finder;
        $this->filesystem = $filesystem;
    }

    /**
     * Initialize lang object to bind language
     *
     * @return void
     */
    private function initLang()
    {
        $this->lang = getenv('LANG');

        if (strstr($this->lang, '.UTF-8') === false) {
            $this->lang .= '.UTF-8';
        }
    }

    /**
     * Get translation from centreon and its extensions
     *
     * @return array
     */
    public function getTranslation(): array
    {
        $centreonTranslation = $this->getCentreonTranslation();
        $extensionsTranslation = $this->getExtensionsTranslation();

        return array_replace_recursive($centreonTranslation, $extensionsTranslation);
    }

    /**
     * Get translation from centreon
     *
     * @return array
     */
    private function getCentreonTranslation(): array
    {
        $data = [];

        $translationPath = __DIR__ . "/../../../../www/locale/{$this->lang}/LC_MESSAGES";
        $translationFile = "messages.ser";

        if ($this->filesystem->exists($translationPath . "/" . $translationFile)) {
            $files = $this->finder
                ->name($translationFile)
                ->in($translationPath);

            foreach ($files as $file) {
                $data = unserialize($file->getContents());
            }
        }

        return $data;
    }

    /**
     * Get translation from each installed module
     *
     * @return array
     */
    private function getExtensionsTranslation(): array
    {
        $data = [];

        // loop over each installed modules to get translation
        foreach (array_keys($this->modulesInformation->getInstalledList()) as $module) {
            $translationPath = __DIR__ . "/../../../../www/modules/{$module}/locale/{$this->lang}/LC_MESSAGES";
            $translationFile = "messages.ser";

            if ($this->filesystem->exists($translationPath . "/" . $translationFile)) {
                $files = $this->finder
                    ->name($translationFile)
                    ->in($translationPath);

                foreach ($files as $file) {
                    $data = array_replace_recursive(
                        $data,
                        unserialize($file->getContents())
                    );
                }
            }
        }

        return $data;
    }
}
