<?php
namespace Centreon;

use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Centreon\Infrastructure\Provider\AutoloadServiceProviderInterface;
use Centreon\Infrastructure\Service;
use Centreon\Infrastructure\Service\CentreonWebserviceService;
use Centreon\Infrastructure\Service\CentreonClapiService;
use Centreon\Infrastructure\Service\CentcoreConfigService;
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use Centreon\Domain\Service\AppKeyGeneratorService;
use Centreon\Domain\Service\BrokerConfigurationService;
use Centreon\Domain\Repository\CfgCentreonBrokerRepository;
use Centreon\Domain\Repository\CfgCentreonBrokerInfoRepository;
use CentreonClapi\CentreonACL;

class ServiceProvider implements AutoloadServiceProviderInterface
{
    const CENTREON_WEBSERVICE = 'centreon.webservice';
    const CENTREON_DB_MANAGER = 'centreon.db-manager';

    /**
     * Register Centreon services
     *
     * @param \Pimple\Container $pimple
     */
    public function register(Container $pimple): void
    {
        $pimple[static::CENTREON_WEBSERVICE] = function(Container $container): CentreonWebserviceService {
            $service = new CentreonWebserviceService;

            return $service;
        };

        $pimple[static::CENTREON_WEBSERVICE]->add(Application\Webservice\TopologyWebservice::class);

        if (defined('OpenApi\UNDEFINED') !== false) {
            $pimple[static::CENTREON_WEBSERVICE]->add(\Centreon\Application\Webservice\OpenApiWebservice::class);
        }

        $pimple['centreon.clapi'] = function(Container $container): CentreonClapiService {
            $service = new CentreonClapiService;

            return $service;
        };

        $pimple[static::CENTREON_DB_MANAGER] = function(Container $container): CentreonDBManagerService {
            $services = [
                'realtime_db',
                'configuration_db',
            ];

            $locator = new ServiceLocator($container, $services);
            $service = new CentreonDBManagerService($locator);

            return $service;
        };

        $pimple['centreon.user'] = function(Container $container): \CentreonUser {
            if (php_sapi_name() !== 'cli' && session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            return $_SESSION['centreon']->user;
        };

        $pimple['centreon.keygen'] = function(Container $container) : AppKeyGeneratorService {
            $service = new AppKeyGeneratorService();

            return $service;
        };

        $pimple['centreon.acl'] = function(Container $container) : CentreonACL {
            $service = new CentreonACL($container);

            return $service;
        };

        $pimple['centreon.config'] = function(Container $container) : CentcoreConfigService {
            $service = new CentcoreConfigService();

            return $service;
        };

        /**
         * Repositories
         */

        $pimple['centreon.broker_repository'] = function(Container $container): CfgCentreonBrokerRepository {
            $service = new CfgCentreonBrokerRepository($container['configuration_db']);

            return $service;
        };

        $pimple['centreon.broker_info_repository'] = function(Container $container): CfgCentreonBrokerInfoRepository {
            $service = new CfgCentreonBrokerInfoRepository($container['configuration_db']);

            return $service;
        };

        /**
         * Services
         */

        $pimple['centreon.broker_configuration_service'] = function(Container $container): BrokerConfigurationService {
            $service = new BrokerConfigurationService($container['configuration_db']);
            $service->setBrokerInfoRepository($container['centreon.broker_info_repository']);

            return $service;
        };

        $pimple['upload.manager'] = function (Container $pimple): Service\UploadFileService {
            $services = [];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Service\UploadFileService($locator, $_FILES);

            return $service;
        };
    }

    public static function order() : int {
        return 1;
    }
}
