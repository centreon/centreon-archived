<?php

/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Class Kernel
 *
 * @package App
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     *
     * @var \App\Kernel
     */
    private static $instance;

    /**
     * @var string cache path
     */
    private $cacheDir = '/var/cache/centreon/symfony';

    /**
     * @var string Log path
     */
    private $logDir = '/var/log/centreon/symfony';

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /**
     * @return \App\Kernel
     */
    public static function createForWeb(): Kernel
    {
        if (self::$instance === null) {
            include_once __DIR__ . '/../config/bootstrap.php';
            if ($_SERVER['APP_DEBUG']) {
                umask(0000);

                Debug::enable();
            }
            self::$instance = new Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
            self::$instance->boot();
        }

        return self::$instance;
    }

    /**
     * Kernel constructor.
     *
     * @param string $environment
     * @param bool   $debug
     */
    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);
        if (defined('_CENTREON_LOG_')) {
            $this->logDir = _CENTREON_LOG_ . '/symfony';
        }
        if (defined('_CENTREON_CACHEDIR_')) {
            $this->cacheDir = _CENTREON_CACHEDIR_ . '/symfony';
        }
    }

    /**
     * @return iterable<mixed>
     */
    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir() . '/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    /**
     * @return string
     */
    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return $this->logDir;
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\Config\Loader\LoaderInterface        $loader
     *
     * @throws \Exception
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir() . '/config/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir() . '/config';

        $loader->load($confDir . '/{packages}/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}_' . $this->environment . self::CONFIG_EXTS, 'glob');
    }

    /**
     * @param \Symfony\Component\Routing\RouteCollectionBuilder $routes
     *
     * @throws \Symfony\Component\Config\Exception\LoaderLoadException
     */
    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir() . '/config';

        $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS, '/', 'glob');
    }
}
