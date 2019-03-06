<?php

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

    private function getDirContents($dir, &$results = [], $regex = '/.*/')
    {
        $files = [];
        if (is_dir($dir)) {
            $files = scandir($dir);
        }

        foreach ($files as $key => $value) {
            //$path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            $path = $dir . DIRECTORY_SEPARATOR . $value;
            if (!is_dir($path) && preg_match($regex, $path)) {
                $results[dirname($path)][] = basename($path);
            } else if ($value != "." && $value != "..") {
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
            //$relativePath = __DIR__ . '/../../../../www/';
            //$hook = 'modules/' . $installedModule . '/static/hooks/' . $this->arguments['path'] . '/index.js';
            $modulePath = __DIR__ . '/../../../../www/modules/' . $installedModule . '/static/hooks';
            $files = [];
            $this->getDirContents($modulePath, $files, '/\.(js|css)$/');
            foreach ($files as $path => $hookFiles) {
                if (preg_match('/\/static\/hooks(\/.+)$/', $path, $hookMatches)) {
                    $hookName = $hookMatches[1];
                    $hookPath = str_replace(__DIR__ . '/../../../../www', '', $path);
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

            /*
            $relativePath = __DIR__ . '/../../../../www/';
            $hook = 'modules/' . $installedModule . '/static/hooks/' . $this->arguments['path'] . '/index.js';
            if (file_exists($relativePath . $hook)) {
                $hooks[] = '/' . $hook;
            }
            */
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
        return [];
    }
}