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
 *
 */

namespace Centreon\Infrastructure\Provider;

use Centreon\Infrastructure\Provider\AutoloadServiceProviderInterface;
use ReflectionClass;
use Pimple\Container;
use Symfony\Component\Finder\Finder;
use Exception;

/**
 * Register all service providers
 */
class AutoloadServiceProvider
{
    public const ERR_TWICE_LOADED = 2001;

    /**
     * Register service providers
     *
     * @param \Pimple\Container $dependencyInjector
     * @return void
     */
    public static function register(Container $dependencyInjector): void
    {
        $providers = static::getProviders($dependencyInjector['finder']);

        foreach ($providers as $provider) {
            $dependencyInjector->register(new $provider);
        }
    }

    /**
     * Get a list of the service provider classes
     *
     * @param \Symfony\Component\Finder\Finder $finder
     * @return array
     */
    private static function getProviders(Finder $finder): array
    {
        $providers = [];
        $serviceProviders = $finder
            ->files()
            ->name('ServiceProvider.php')
            ->depth('== 1')
            ->in(__DIR__ . '/../../../../src')
        ;

        foreach ($serviceProviders as $serviceProvider) {
            $serviceProviderRelativePath = $serviceProvider->getRelativePath();

            $object = "{$serviceProviderRelativePath}\\ServiceProvider";

            if (!class_exists($object)) {
                continue;
            }

            static::addProvider($providers, $object);
        }

        asort($providers);

        return array_keys($providers);
    }

    /**
     * Add classes only implement the interface AutoloadServiceProviderInterface
     *
     * @param array $providers
     * @param string $object
     * @return void
     * @throws \Exception
     */
    private static function addProvider(array &$providers, string $object): void
    {
        if (array_key_exists($object, $providers)) {
            throw new Exception(sprintf('Provider %s is loaded', $object), static::ERR_TWICE_LOADED);
        }

        $interface = AutoloadServiceProviderInterface::class;
        $hasInterface = (new ReflectionClass($object))
            ->implementsInterface($interface);

        if ($hasInterface) {
            $providers[$object] = $object::order();
        }
    }
}
