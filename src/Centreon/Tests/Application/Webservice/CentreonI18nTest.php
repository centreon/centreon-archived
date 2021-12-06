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
use Centreon\Tests\Resources\Traits;
use Centreon\Application\Webservice\CentreonI18n;
use Centreon\Domain\Service\I18nService;
use Centreon\ServiceProvider;

/**
 * @group Centreon
 * @group Webservice
 */
class CentreonI18nTest extends TestCase
{
    use Traits\WebServiceAuthorizePublicTrait;

    protected function setUp(): void
    {
        // dependencies
        $this->container = new Container([
            ServiceProvider::CENTREON_I18N_SERVICE => $this->createMock(I18nService::class),
        ]);

        $this->webservice = $this->createPartialMock(CentreonI18n::class, [
            'loadDb',
            'loadArguments',
            'loadToken',
            'query',
        ]);

        // load dependencies
        $this->webservice->setDi($this->container);
    }

    public function testGetName()
    {
        $this->assertEquals('centreon_i18n', CentreonI18n::getName());
    }

    public function testGetTranslation()
    {
        $value = ['test OK'];
        $this->container->offsetGet(ServiceProvider::CENTREON_I18N_SERVICE)
            ->method('getTranslation')
            ->willReturn($value);

        $this->assertEquals($value, $this->webservice->getTranslation());
    }

    public function testGetTranslationWithException()
    {
        $this->container->offsetGet(ServiceProvider::CENTREON_I18N_SERVICE)
            ->method('getTranslation')
            ->will($this->returnCallback(function () {
                throw new \Exception('');
            }));

        $this->expectException(\Exception::class);

        $this->webservice->getTranslation();
    }
}
