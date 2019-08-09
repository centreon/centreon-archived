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
