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

namespace Centreon;

use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use CentreonLegacy\ServiceProvider as LegacyServiceProvider;
use Centreon\Application\Webservice;
use Centreon\Infrastructure\Provider\AutoloadServiceProviderInterface;
use Centreon\Infrastructure\Service;
use Centreon\Infrastructure\Event;
use Centreon\Infrastructure\Service\CentreonWebserviceService;
use Centreon\Infrastructure\Service\CentreonClapiService;
use Centreon\Infrastructure\Service\CentcoreConfigService;
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use Centreon\Domain\Service\I18nService;
use Centreon\Domain\Service\FrontendComponentService;
use Centreon\Domain\Service\AppKeyGeneratorService;
use Centreon\Domain\Service\BrokerConfigurationService;
use Centreon\Domain\Repository\CfgCentreonBrokerRepository;
use Centreon\Domain\Repository\CfgCentreonBrokerInfoRepository;
use CentreonClapi\CentreonACL;
use Centreon\Application\Validation;
use Symfony\Component\Validator;
use Symfony\Component\Validator\Constraints;
use CentreonACL as CACL;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

class ServiceProvider implements AutoloadServiceProviderInterface
{
    //webservices
    public const CENTREON_WEBSERVICE = 'centreon.webservice';
    public const MENU_WEBSERVICE = 'centreon.menu.webservice';

    //services
    public const CENTREON_PAGINATION = 'centreon.pagination';
    public const CENTREON_I18N_SERVICE = 'centreon.i18n_service';
    public const CENTREON_FRONTEND_COMPONENT_SERVICE = 'centreon.frontend_component_service';
    public const CENTREON_BROKER_CONFIGURATION_SERVICE = 'centreon.broker_configuration_service';

    //repositories
    public const CENTREON_BROKER_REPOSITORY = 'centreon.broker_repository';
    public const CENTREON_BROKER_INFO_REPOSITORY = 'centreon.broker_info_repository';

    //managers and infrastructure services
    public const CENTREON_DB_MANAGER = 'centreon.db-manager';
    public const CENTREON_CLAPI = 'centreon.clapi';
    public const UPLOAD_MANGER = 'upload.manager';
    public const CENTREON_EVENT_DISPATCHER = 'centreon.event_dispatcher';
    public const CENTREON_USER = 'centreon.user';
    public const YML_CONFIG = 'yml.config';
    public const CENTREON_VALIDATOR_FACTORY = 'centreon.validator_factory';
    public const CENTREON_VALIDATOR_TRANSLATOR = 'centreon.validator_translator';
    public const VALIDATOR = 'validator';
    public const VALIDATOR_EXPRESSION = 'validator.expression';
    public const SERIALIZER = 'serializer';
    public const SERIALIZER_OBJECT_NORMALIZER = 'serializer.object.normalizer';
    public const CENTREON_ACL = 'centreon.acl';
    public const CENTREON_GLOBAL_ACL = 'centreon.global.acl';

    /**
     * {@inheritdoc}
     */
    public function register(Container $pimple): void
    {

        //init global yml config from src/Centreon
        $pimple[static::YML_CONFIG] = function (Container $pimple) {
            return $pimple[\CentreonLegacy\ServiceProvider::CONFIGURATION]->getModuleConfig(__DIR__);
        };

        $pimple[static::CENTREON_WEBSERVICE] = function (Container $container): CentreonWebserviceService {
            $service = new CentreonWebserviceService();

            return $service;
        };

        $pimple[static::CENTREON_WEBSERVICE]
            ->add(Application\Webservice\TopologyWebservice::class)
            ->add(Application\Webservice\ContactGroupsWebservice::class)
            ->add(Application\Webservice\ImagesWebservice::class)
            ->add(Application\Webservice\AclGroupWebservice::class)
            // add webservice to get translation from centreon and its extensions
            ->add(Webservice\CentreonI18n::class)
            // add webservice to get frontend hooks and pages installed by modules and widgets
            ->add(Webservice\CentreonFrontendComponent::class);

        if (defined('OpenApi\UNDEFINED') !== false) {
            $pimple[static::CENTREON_WEBSERVICE]->add(\Centreon\Application\Webservice\OpenApiWebservice::class);
        }

        $pimple[static::CENTREON_I18N_SERVICE] = function (Container $pimple): I18nService {
            $pimple['translator']; // bind lang

            $service = new I18nService(
                $pimple[LegacyServiceProvider::CENTREON_LEGACY_MODULE_INFORMATION],
                $pimple['finder'],
                $pimple['filesystem']
            );

            return $service;
        };

        $pimple[static::CENTREON_FRONTEND_COMPONENT_SERVICE] = function (Container $pimple): FrontendComponentService {
            return new FrontendComponentService(
                new ServiceLocator(
                    $pimple,
                    FrontendComponentService::dependencies()
                )
            );
        };

        $pimple[static::CENTREON_CLAPI] = function (Container $container): CentreonClapiService {
            $service = new CentreonClapiService();

            return $service;
        };

        $pimple[static::CENTREON_DB_MANAGER] = function (Container $container): CentreonDBManagerService {
            $services = [
                'realtime_db',
                'configuration_db',
            ];

            $locator = new ServiceLocator($container, $services);
            $service = new CentreonDBManagerService($locator);

            return $service;
        };

        $pimple[static::CENTREON_PAGINATION] = function (Container $container): Service\CentreonPaginationService {
            $service = new Service\CentreonPaginationService(
                new ServiceLocator(
                    $container,
                    Service\CentreonPaginationService::dependencies()
                )
            );

            return $service;
        };

        $pimple['centreon.user'] = function (Container $container): ?\CentreonUser {
            // @codeCoverageIgnoreStart
            if (!empty($GLOBALS['centreon']->user) && $GLOBALS['centreon']->user instanceof \CentreonUser) {
                return $GLOBALS['centreon']->user;
            } elseif (php_sapi_name() !== 'cli' && session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            return $_SESSION['centreon']->user; // @codeCoverageIgnoreEnd
        };

        $pimple['centreon.keygen'] = function (): AppKeyGeneratorService {
            $service = new AppKeyGeneratorService();

            return $service;
        };

        $pimple[static::CENTREON_ACL] = function (Container $container): CentreonACL {
            $service = new CentreonACL($container);

            return $service;
        };


        $pimple[static::CENTREON_GLOBAL_ACL] = function (): CACL {
            $service = new CACL($_SESSION['centreon']->user->user_id, $_SESSION['centreon']->user->admin);

            return $service;
        };

        $pimple['centreon.config'] = function (): CentcoreConfigService {
            $service = new CentcoreConfigService();

            return $service;
        };

        /**
         * Repositories
         */

        // @todo class is available via centreon.db-manager
        $pimple[static::CENTREON_BROKER_REPOSITORY] = function (Container $container): CfgCentreonBrokerRepository {
            $service = new CfgCentreonBrokerRepository($container['configuration_db']);

            return $service;
        };

        // @todo class is available via centreon.db-manager
        $pimple[static::CENTREON_BROKER_INFO_REPOSITORY] =
            function (Container $container): CfgCentreonBrokerInfoRepository {
                $service = new CfgCentreonBrokerInfoRepository($container['configuration_db']);

                return $service;
            };

        /**
         * Services
         */

        $pimple[static::CENTREON_BROKER_CONFIGURATION_SERVICE] =
            function (Container $container): BrokerConfigurationService {
                $service = new BrokerConfigurationService($container['configuration_db']);
                $service->setBrokerInfoRepository($container[ServiceProvider::CENTREON_BROKER_INFO_REPOSITORY]);

                return $service;
            };

        $pimple[static::UPLOAD_MANGER] = function (Container $pimple): Service\UploadFileService {
            $services = [];

            $locator = new ServiceLocator($pimple, $services);
            $service = new Service\UploadFileService($locator, $_FILES);

            return $service;
        };

        $pimple[static::CENTREON_EVENT_DISPATCHER] = function (): Event\EventDispatcher {
            $eventDispatcher = new Event\EventDispatcher();
            $eventDispatcher->setDispatcherLoader(
                new Event\FileLoader(
                    __DIR__ . '/../../www/modules/',
                    'custom-module-form.php'
                )
            );

            return $eventDispatcher;
        };

        $this->registerValidator($pimple);

        $pimple[static::SERIALIZER_OBJECT_NORMALIZER] = function (): Serializer\Normalizer\ObjectNormalizer {
            $classMetadataFactory = new Serializer\Mapping\Factory\ClassMetadataFactory(
                new Serializer\Mapping\Loader\AnnotationLoader(new AnnotationReader())
            );

            return new Serializer\Normalizer\ObjectNormalizer(
                $classMetadataFactory,
                new Serializer\NameConverter\MetadataAwareNameConverter($classMetadataFactory),
                null,
                new ReflectionExtractor()
            );
        };

        $pimple[static::SERIALIZER] = function ($container): Serializer\Serializer {
            return new Serializer\Serializer([
                $container[static::SERIALIZER_OBJECT_NORMALIZER],
                new Serializer\Normalizer\ArrayDenormalizer(),
            ], [
                new Serializer\Encoder\JsonEncoder(),
            ]);
        };
    }

    /**
     * Register services related with validation
     *
     * @param \Pimple\Container $pimple
     */
    public function registerValidator(Container $pimple): void
    {
        $pimple[static::VALIDATOR] = function (Container $container): Validator\Validator\ValidatorInterface {
            return Validator\Validation::createValidatorBuilder()
                    ->addMethodMapping('loadValidatorMetadata')
                    ->setConstraintValidatorFactory($container[ServiceProvider::CENTREON_VALIDATOR_FACTORY])
                    ->setTranslator($container[ServiceProvider::CENTREON_VALIDATOR_TRANSLATOR])
                    ->getValidator();
        };

        $pimple[static::CENTREON_VALIDATOR_FACTORY] =
            function (Container $container): Validation\CentreonValidatorFactory {
                $service = new Validation\CentreonValidatorFactory($container);

                return $service;
            };

        $pimple[static::CENTREON_VALIDATOR_TRANSLATOR] =
            function (Container $container): Validation\CentreonValidatorTranslator {
                return new Validation\CentreonValidatorTranslator();
            };

        $pimple[static::VALIDATOR_EXPRESSION] = function (): Constraints\ExpressionValidator {
            return new Constraints\ExpressionValidator();
        };
    }

    /**
     * {@inheritdoc}
     */
    public static function order(): int
    {
        return 1;
    }
}
