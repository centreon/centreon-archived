<?php

namespace Centreon\Tests;

use PHPUnit\Framework\TestCase;
use Centreon\Test\Mock;
use Pimple\Container;
use Centreon\ServiceProvider;
use Centreon\Domain;
use Centreon\Infrastructure\Service;
use Centreon\Infrastructure\Event\EventDispatcher;
use CentreonClapi\CentreonACL;
use Centreon\Application\Validation;
use CentreonLegacy\ServiceProvider as LegacyServiceProvider;
use CentreonLegacy\Core\Module\Information;
use Symfony\Component\Validator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group Centreon
 * @group ServiceProvider
 */
class ServiceProviderTest extends TestCase
{

    protected $container;
    protected $provider;

    protected function setUp()
    {
        $this->provider = new ServiceProvider();
        $this->container = new Container;
        $this->container['realtime_db'] = $this->container['configuration_db'] = new Mock\CentreonDB;
        $this->container['translator'] = $this->container['configuration_db'] = new Mock\CentreonDB;
        $this->container[LegacyServiceProvider::CENTREON_LEGACY_MODULE_INFORMATION] = 
            $this->createMock(Information::class);
        $this->container['finder'] = $this->createMock(Finder::class);
        $this->container['filesystem'] = $this->createMock(Filesystem::class);

        $this->provider->register($this->container);
    }

    /**
     * @covers \Centreon\ServiceProvider::register
     * @covers \Centreon\ServiceProvider::registerValidator
     */
    public function testCheckServicesByList()
    {
        $services = $this->container->keys();

        $checkList = [
            ServiceProvider::CENTREON_WEBSERVICE => Service\CentreonWebserviceService::class,
            ServiceProvider::CENTREON_CLAPI => Service\CentreonClapiService::class,
            ServiceProvider::CENTREON_DB_MANAGER => Service\CentreonDBManagerService::class,
            ServiceProvider::UPLOAD_MANGER => Service\UploadFileService::class,
            ServiceProvider::CENTREON_PAGINATION => Service\CentreonPaginationService::class,
            'centreon.keygen' => Domain\Service\AppKeyGeneratorService::class,
            'centreon.acl' => CentreonACL::class,
            'centreon.config' => Service\CentcoreConfigService::class,
            ServiceProvider::CENTREON_BROKER_CONFIGURATION_SERVICE => Domain\Service\BrokerConfigurationService::class,
            // @todo must be exclude form DI
            ServiceProvider::CENTREON_BROKER_REPOSITORY => Domain\Repository\CfgCentreonBrokerRepository::class,
            ServiceProvider::CENTREON_BROKER_INFO_REPOSITORY =>
                Domain\Repository\CfgCentreonBrokerInfoRepository::class,
            // Validators
            ServiceProvider::VALIDATOR => Validator\Validator\ValidatorInterface::class,
            ServiceProvider::CENTREON_VALIDATOR_FACTORY => Validation\CentreonValidatorFactory::class,
            ServiceProvider::CENTREON_VALIDATOR_TRANSLATOR => Validation\CentreonValidatorTranslator::class,
            ServiceProvider::VALIDATOR_EXPRESSION => Validator\Constraints\ExpressionValidator::class,
            // I18N service
            ServiceProvider::CENTREON_I18N_SERVICE => Domain\Service\I18nService::class,
            // Service for frontend hooks and pages
            ServiceProvider::CENTREON_FRONTEND_COMPONENT_SERVICE => Domain\Service\FrontendComponentService::class,
            ServiceProvider::CENTREON_EVENT_DISPATCHER => EventDispatcher::class,
        ];

        // check list of services
        foreach ($checkList as $serviceName => $className) {
            $this->assertTrue($this->container->offsetExists($serviceName));

            $service = $this->container->offsetGet($serviceName);

            $this->assertInstanceOf($className, $service);
        }
    }

    /**
     * @covers \Centreon\ServiceProvider::register
     */
    public function testUserService()
    {
        $this->assertTrue($this->container->offsetExists('centreon.user'));

        (new Mock\Centreon())->generateSession();

        $service = $this->container->offsetGet('centreon.user');

        $this->assertInstanceOf(\CentreonUser::class, $service);
    }

    /**
     * @covers \Centreon\ServiceProvider::order
     */
    public function testOrder()
    {
        $this->assertEquals(1, $this->provider::order());
    }
}
