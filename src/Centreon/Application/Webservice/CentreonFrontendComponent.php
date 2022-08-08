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
 */
namespace Centreon\Application\Webservice;

use Centreon\Infrastructure\Webservice;
use Centreon\Infrastructure\Webservice\WebserviceAutorizePublicInterface;
use Centreon\ServiceProvider;
use Pimple\Container;
use Pimple\Psr11\ServiceLocator;

class CentreonFrontendComponent extends Webservice\WebServiceAbstract implements WebserviceAutorizePublicInterface
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $services;

    /**
     * Name of web service object
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_frontend_component';
    }

    /**
     * @SWG\Get(
     *   path="/centreon/api/internal.php",
     *   operationId="getComponents",
     *   @SWG\Parameter(
     *       in="query",
     *       name="object",
     *       type="string",
     *       description="the name of the API object class",
     *       required=true,
     *       enum="centreon_configuration_remote",
     *   ),
     *   @SWG\Parameter(
     *       in="query",
     *       name="action",
     *       type="string",
     *       description="the name of the action in the API class",
     *       required=true,
     *       enum="components",
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="JSON with the external react components (pages, hooks)"
     *   )
     * )
     *
     * Get list with remote components
     *
     * @return array
     * @example [
     *            ['pages' => [
     *              '/my/module/route' => [
     *                'js' => '<my_module_path>/static/pages/my/module/route/index.js',
     *                'css' => '<my_module_path>/static/pages/my/module/route/index.css'
     *              ]
     *            ]],
     *            ['hooks' => [
     *              '/header/topCounter' => [
     *                [
     *                  'js' => '<my_module_path>/static/hooks/header/topCounter/index.js',
     *                  'css' => '<my_module_path>/static/hooks/header/topCounter/index.css'
     *                ]
     *              ]
     *            ]]
     *          ]
     */
    public function getComponents(): array
    {
        $service = $this->services->get(ServiceProvider::CENTREON_FRONTEND_COMPONENT_SERVICE);

        return [
            'pages' => $service->getPages(),
            'hooks' => $service->getHooks(),
        ];
    }

    /**
     * Extract services that are in use only
     *
     * @param \Pimple\Container $di
     */
    public function setDi(Container $di)
    {
        $this->services = new ServiceLocator($di, [
            ServiceProvider::CENTREON_FRONTEND_COMPONENT_SERVICE,
        ]);
    }
}
