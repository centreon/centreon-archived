<?php
/*
 * Centreon
 *
 * Source Copyright 2005-2019 Centreon
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more informations : contact@centreon.com
 *
 */

namespace CentreonAutoDiscovery;

use CentreonAutoDiscovery\Domain\Entity\Pagination;
use CentreonAutoDiscovery\Domain\Entity\Security;
use CentreonAutoDiscovery\Infrastructure\Repository\HostDiscoveryRepository;
use CentreonLicense\Infrastructure\Service\LicenseService;
use Pimple\Container;
use CentreonAutoDiscovery\Application\Webservice\CentreonHostDiscovery;
use Centreon\Infrastructure\Provider\AutoloadServiceProviderInterface;
use CentreonAutoDiscovery\Domain\Service\HostDiscoveryService;
use CentreonRemote\Infrastructure\Service\PollerInteractionService;

require_once __DIR__ . '/../../www/class/centreonHost.class.php';
require_once __DIR__ . '/../../www/class/centreonUser.class.php';
require_once __DIR__ . '/../../www/class/centreonLogAction.class.php';

class ServiceProvider implements AutoloadServiceProviderInterface
{
    /**
     * Register Centreon Auto Discovery services
     *
     * @param \Pimple\Container $pimple
     */
    public function register(Container $pimple): void
    {
        $pimple[\CentreonLicense\ServiceProvider::LM_PRODUCT_NAME] = 'epp';
        $pimple[\CentreonLicense\ServiceProvider::LM_HOST_CHECK] = true;

        $pimple['centreon.webservice']->add(CentreonHostDiscovery::class);

        $pimple[CentreonHostDiscovery::class] = function (Container $container): CentreonHostDiscovery {
            global $userInfos;
            // Used for the Centreon log system
            $GLOBALS['pearDBO'] = $container['realtime_db'];
            $db = $container['configuration_db'];

            $host = new \CentreonHost($db);
            $repository = new HostDiscoveryRepository($db);

            $hostDiscovery = new CentreonHostDiscovery(
                new HostDiscoveryService($repository, $host),
                $repository,
                $host,
                new \centreonLogAction((new \CentreonUser($userInfos))),
                new PollerInteractionService($container)
            );

            if (class_exists(\CentreonLicense\Infrastructure\Service\LicenseService::class)) {
                $service = new LicenseService(
                    $container[\CentreonLicense\ServiceProvider::LM_PRODUCT_NAME],
                    $container['configuration_db'],
                    $container[\CentreonLicense\ServiceProvider::LM_FINGERPRINT],
                    $container[\CentreonLicense\ServiceProvider::LM_SYSTEM],
                    $container[\CentreonLicense\ServiceProvider::LM_CRYPTO],
                    $container[\CentreonLicense\ServiceProvider::LM_IMP_API],
                    $container[\CentreonLicense\ServiceProvider::LM_COMPANY_TOKEN],
                    $container[\CentreonLicense\ServiceProvider::LM_HOST_CHECK]
                );
                $hostDiscovery->setEPPLicenseValid(
                    $service->validate()
                );
            }

            $hostDiscovery->setPagination(
                Pagination::fromGetParameters()
            );

            /*
             * We define the first secure key directly to avoid storing it as a
             * parameter and thus exposing it. The second key must be defined
             * by the code that will use the "Security" class.
             * This will avoid decrypting the data using only the security class.
             */
            $hostDiscovery->setSecurity(
                (new Security())
                    ->setFirstKey('EFXTJ0eNVC90vy1yTg1EQBnJqlOkIF+xToVqTmEw1I4=')
            );

            return $hostDiscovery;
        };

        $pimple['centreon.hostDiscoveryService'] = function (Container $container): HostDiscoveryService {
            $db = $container['configuration_db'];
            $hostDiscoveryService = new HostDiscoveryService(
                new HostDiscoveryRepository($db),
                new \CentreonHost($db)
            );

            $hostDiscoveryService->setSecurity(
                (new Security())
                    ->setFirstKey('EFXTJ0eNVC90vy1yTg1EQBnJqlOkIF+xToVqTmEw1I4=')
            );
            return $hostDiscoveryService;
        };
    }

    public static function order(): int
    {
        return 5;
    }
}
