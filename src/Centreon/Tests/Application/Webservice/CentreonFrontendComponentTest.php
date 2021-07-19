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

namespace Centreon\Tests\Application\Webservice;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Centreon\ServiceProvider;
use Centreon\Application\Webservice\CentreonFrontendComponent;
use Centreon\Domain\Service\FrontendComponentService;
use Centreon\Tests\Resources\Traits;

/**
 * @group Centreon
 * @group Webservice
 */
class CentreonFrontendComponentTest extends TestCase
{
    use Traits\WebServiceAuthorizePublicTrait;

    /**
     * Control value for the method getComponents
     *
     * @var array
     */
    protected $getComponentsValues = [
        'pages' => ['list of pages'],
        'hooks' => ['list of hooks'],
    ];

    protected function setUp()
    {
        // dependencies
        $container = new Container;
        $container[ServiceProvider::CENTREON_FRONTEND_COMPONENT_SERVICE] =
            $this->createMock(FrontendComponentService::class);

        (function (FrontendComponentService $service) {
            $service
                ->method('getPages')
                ->willReturn($this->getComponentsValues['pages']);
            $service
                ->method('getHooks')
                ->willReturn($this->getComponentsValues['hooks']);
        })($container[ServiceProvider::CENTREON_FRONTEND_COMPONENT_SERVICE]);

        $this->webservice = $this->createPartialMock(CentreonFrontendComponent::class, [
            'loadDb',
            'loadArguments',
            'loadToken',
            'query',
        ]);

        // load dependencies
        $this->webservice->setDi($container);
    }

    public function testGetComponents()
    {
        $this->webservice
            ->method('query')
            ->will($this->returnCallback(function () use (&$filters) {
                    return $filters;
            }));

        $this->assertEquals($this->getComponentsValues, $this->webservice->getComponents());
    }

    public function testGetName()
    {
        $this->assertEquals('centreon_frontend_component', CentreonFrontendComponent::getName());
    }
}
