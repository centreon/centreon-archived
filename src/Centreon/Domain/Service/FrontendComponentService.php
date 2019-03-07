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

use Pimple\Container;

/**
 * Class to manage external frontend components provided by modules and widgets
 */
class FrontendComponentService
{
    /**
     * @var Container
     */
    private $di;

    /**
     * FrontendComponentService constructor
     *
     * @param string $di The dependency injector
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    /**
     * Dependency injector getter
     *
     * @return string The dependency injector
     */
    private function getDi(): Container
    {
        return $this->di;
    }

    /**
     * Get directory files grouped by directory matching regex
     *
     * @param string $dir the directory to explore
     * @param array $results the found files
     * @param string $regex the regex to match
     * @return array
     */
    private function getDirContents(string $dir, array &$results = [], string $regex = '/.*/'): array
    {
        $files = [];
        if (is_dir($dir)) {
            $files = scandir($dir);
        }

        foreach ($files as $key => $value) {
            $path = $dir . DIRECTORY_SEPARATOR . $value;
            if (!is_dir($path) && preg_match($regex, $path)) {
                // group files by directory
                $results[dirname($path)][] = basename($path);
            } elseif ($value != "." && $value != "..") {
                $this->getDirContents($path, $results, $regex);
            }
        }

        return $results;
    }

    /**
     * Get status for centreon instance (is remote or is not remote)
     *
     * @return array The list of hooks (js and css)
     */
    public function getHooks(): array
    {
        // get installed modules
        // @todo create serviceprovider in CentreonLegacy namespace
        $utilsFactory = new \CentreonLegacy\Core\Utils\Factory($this->di);
        $utils = $utilsFactory->newUtils();
        $moduleFactory = new \CentreonLegacy\Core\Module\Factory($this->di, $utils);
        $module = $moduleFactory->newInformation();
        $installedModules = $module->getInstalledList();

        // search in each installed modules if there are hooks
        $hooks = [];
        foreach (array_keys($installedModules) as $installedModule) {
            $modulePath = __DIR__ . '/../../../../www/modules/' . $installedModule . '/static/hooks';
            $files = [];
            $this->getDirContents($modulePath, $files, '/\.(js|css)$/');
            foreach ($files as $path => $hookFiles) {
                if (preg_match('/\/static\/hooks(\/.+)$/', $path, $hookMatches)) {
                    // parse hook name by removing beginning of the path
                    $hookName = $hookMatches[1];
                    // set relative path
                    $hookPath = str_replace(__DIR__ . '/../../../../www', '', $path);

                    // add hook parameters (js and css files)
                    $hookParameters = [];
                    foreach ($hookFiles as $hookFile) {
                        if (preg_match('/\.js$/', $hookFile)) {
                            $hookParameters['js'] = $hookPath . '/' . $hookFile;
                        } elseif (preg_match('/\.css$/', $hookFile)) {
                            $hookParameters['css'] = $hookPath . '/' . $hookFile;
                        }
                    }

                    if (!empty($hookParameters)) {
                        $hooks[$hookName][] = $hookParameters;
                    }
                }
            }
        }

        return $hooks;
    }

    /**
     * Get status for centreon instance (is master or is not master)
     *
     * @return array The list of pages (routes, js and css)
     */
    public function getPages(): array
    {
        // @todo get external pages from modules
        return [];
    }
}
