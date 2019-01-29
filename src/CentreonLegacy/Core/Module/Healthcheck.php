<?php
/**
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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

namespace CentreonLegacy\Core\Module;

use Psr\Container\ContainerInterface;
use CentreonLegacy\ServiceProvider;

class Healthcheck
{

    /**
     * @var string
     */
    protected $modulePath;

    /**
     * Construct
     *
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct( ContainerInterface $services )
    {
        $this->modulePath = $services->get(ServiceProvider::CONFIGURATION)
            ->getModulePath()
        ;
    }

    public function checkOld( $module ): ?array
    {
        if (!preg_match('/^(?!\.)/', $module) || !is_dir($this->modulePath . $module)) {
            return null;
        }

        $response = [];
        $checklistDir = $this->modulePath . $module . '/checklist/';
        $warning = false;
        $critical = false;

        if (file_exists($checklistDir . 'requirements.php')) {
            $message = [];

            require_once $checklistDir . 'requirements.php';

            // Necessary to implement the expiration date column in list modules page
            if (!empty($licenseExpiration)) {
                $response['licenseExpiration'] = $licenseExpiration;
            }
            if ($critical || $warning) {
                if ($critical) {
                    $response['status'] = 'critical';
                } elseif ($warning) {
                    $response['status'] = 'warning';
                }

                foreach ($message as $errorMessage) {
                    $response['message'] = [
                        'ErrorMessage' => $errorMessage['ErrorMessage'],
                        'Solution' => $errorMessage['Solution'],
                    ];
                }
            } else {
                $response['status'] = 'ok';
                if (isset($customAction) && is_array($customAction)) {
                    $response['customAction'] = $customAction['action'];
                    $response['customActionName'] = $customAction['name'];
                }
            }
        } else {
            return null;
            $response['status'] = 'notfound';
        }

        return $response;
    }
}
