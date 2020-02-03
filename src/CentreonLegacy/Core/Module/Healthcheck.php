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
use DateTime;
use CentreonLegacy\Core\Module\Exception;

/**
 * Check module requirements and health
 */
class Healthcheck
{

    /**
     * @var string Path to the module
     */
    protected $modulePath;

    /**
     * @var array Collect error messages after check
     */
    protected $messages;

    /**
     * @var array Collect a custom action after check
     */
    protected $customAction;

    /**
     * @var \DateTime Collect date and time of a license expiration
     */
    protected $licenseExpiration;

    /**
     * Construct
     *
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->modulePath = $services->get(ServiceProvider::CONFIGURATION)
            ->getModulePath()
        ;
    }

    /**
     * Check module requirements and health
     *
     * @param string $module
     * @return bool|null
     * @throws Exception\HealthcheckNotFoundException
     * @throws Exception\HealthcheckCriticalException
     * @throws Exception\HealthcheckWarningException
     */
    public function check($module): ?bool
    {
        // reset messages stack
        $this->reset();

        if (!preg_match('/^(?!\.)/', $module)) {
            throw new Exception\HealthcheckNotFoundException("Incorrect module name {$module}");
        } elseif (!is_dir($this->modulePath . $module)) {
            throw new Exception\HealthcheckNotFoundException("Module did not exist {$this->modulePath} {$module}");
        }

        $checklistDir = $this->modulePath . $module . '/checklist/';
        $warning = false;
        $critical = false;

        if (file_exists($checklistDir . 'requirements.php')) {
            $message = [];
            $licenseExpiration = null;
            $customAction = null;

            $this->getRequirements($checklistDir, $message, $customAction, $warning, $critical, $licenseExpiration);

            // Necessary to implement the expiration date column in list modules page
            if (!empty($licenseExpiration)) {
                $this->licenseExpiration = new DateTime(date(DateTime::W3C, $licenseExpiration));
            }

            if (!$critical && !$warning) {
                $this->setCustomAction($customAction);

                return true;
            }

            $this->setMessages($message);

            if ($critical) {
                throw new Exception\HealthcheckCriticalException();
            } elseif ($warning) {
                throw new Exception\HealthcheckWarningException();
            }
        }

        throw new Exception\HealthcheckNotFoundException('The module\'s requirements did not exist');
    }

    /**
     * Load a file with requirements
     *
     * @codeCoverageIgnore
     * @param string $checklistDir
     * @param array $message
     * @param array $customAction
     * @param bool $warning
     * @param bool $critical
     * @param int $licenseExpiration
     */
    protected function getRequirements(
        $checklistDir,
        &$message,
        &$customAction,
        &$warning,
        &$critical,
        &$licenseExpiration
    ) {
        global $centreon_path;
        require_once $checklistDir . 'requirements.php';
    }

    /**
     * Made the check method compatible with moduleDependenciesValidator
     *
     * @param string $module
     * @return array|null
     */
    public function checkPrepareResponse($module): ?array
    {
        $result = null;

        try {
            $this->check($module);

            $result = [
                'status' => 'ok',
            ];

            if ($this->getCustomAction()) {
                $result = array_merge($result, $this->getCustomAction());
            }
        } catch (Exception\HealthcheckCriticalException $ex) {
            $result = [
                'status' => 'critical',
            ];

            if ($this->getMessages()) {
                $result = array_merge($result, [
                    'message' => $this->getMessages(),
                ]);
            }
        } catch (Exception\HealthcheckWarningException $ex) {
            $result = [
                'status' => 'warning',
            ];

            if ($this->getMessages()) {
                $result = array_merge($result, [
                    'message' => $this->getMessages(),
                ]);
            }
        } catch (Exception\HealthcheckNotFoundException $ex) {
            $result = [
                'status' => 'notfound',
            ];
        } catch (\Exception $ex) {
            $result = [
                'status' => 'critical',
                'message' => [
                    'ErrorMessage' => $ex->getMessage(),
                    'Solution' => '',
                ],
            ];
        }

        if ($this->getLicenseExpiration()) {
            $result['licenseExpiration'] = $this->getLicenseExpiration()->getTimestamp();
        }

        return $result;
    }

    /**
     * Reset collected data after check
     */
    public function reset()
    {
        $this->messages = null;
        $this->customAction = null;
        $this->licenseExpiration = null;
    }

    public function getMessages(): ?array
    {
        return $this->messages;
    }

    public function getCustomAction(): ?array
    {
        return $this->customAction;
    }

    public function getLicenseExpiration(): ?DateTime
    {
        return $this->licenseExpiration;
    }

    protected function setMessages(array $messages)
    {
        foreach ($messages as $errorMessage) {
            $this->messages = [
                'ErrorMessage' => $errorMessage['ErrorMessage'],
                'Solution' => $errorMessage['Solution'],
            ];
        }
    }

    protected function setCustomAction(array $customAction = null)
    {
        if ($customAction !== null) {
            $this->customAction = [
                'customAction' => $customAction['action'],
                'customActionName' => $customAction['name'],
            ];
        }
    }
}
