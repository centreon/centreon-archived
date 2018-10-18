<?php
namespace Centreon\Infrastructure\Provider;

use Centreon\Infrastructure\Provider\AutoloadServiceProviderInterface;
use ReflectionClass;
use Pimple\Container;
use Symfony\Component\Finder\Finder;

class AutoloadServiceProvider
{

    const ERR_TWICE_LOADED = 2001;

    public static function register(Container $dependencyInjector): void
    {
        $providers = static::getProviders($dependencyInjector['finder']);

        foreach ($providers as $provider) {
            $dependencyInjector->register(new $provider);
        }
    }

    private static function getProviders(Finder $finder): array
    {
        $providers = [];
        $dependencyMatrix = [];

        $serviceProviders = $finder
            ->files()
            ->name('ServiceProvider.php')
            ->depth('== 1')
            ->in(_CENTREON_PATH_ . '/src')
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
        $providers = array_keys($providers);

        return $providers;
    }

    private static function addProvider(array &$providers, string $object): void
    {
        if (array_key_exists($object, $providers)) {
            throw new Exception(sprintf('Provider %s is loaded', $object), static::ERR_TWICE_LOADED);
        }

        $interface = AutoloadServiceProviderInterface::class;
        $hasInterface = (new ReflectionClass($object))
            ->implementsInterface($interface);

        if (!$hasInterface) {
            return;
        }

        $providers[$object] = $object::order();
    }
}