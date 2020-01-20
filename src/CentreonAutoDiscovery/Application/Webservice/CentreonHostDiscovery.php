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

namespace CentreonAutoDiscovery\Application\Webservice;

use CentreonAutoDiscovery\Domain\Entity\ConnectionParameter;
use CentreonAutoDiscovery\Domain\Entity\Pagination;
use CentreonAutoDiscovery\Domain\Entity\Security;
use CentreonAutoDiscovery\Domain\Service\HostDiscoveryService;
use CentreonAutoDiscovery\Infrastructure\Repository\HostDiscoveryRepositoryInterface;
use CentreonRemote\Infrastructure\Service\PollerInteractionService;

class CentreonHostDiscovery extends CentreonWebServiceAbstract
{
    private const PASSWORD_TOKEN = '<<hidden_password>>';

    /**
     * @var HostDiscoveryService
     */
    private $discoveryService;

    /**
     * @var HostDiscoveryRepositoryInterface
     */
    private $discoveryRepository;

    /**
     * @var $centreonHost \CentreonHost
     */
    private $centreonHost;

    /**
     * @var $pagination Pagination
     */
    private $pagination;

    /**
     * @var $centreonLog \centreonLogAction
     */
    private $centreonLog;

    /**
     * @var $security Security
     */
    private $security;

    /**
     * @var $pollerId int
     */
    private $pollerId;

    /**
     * @var $poller PollerInteractionService
     */
    private $poller;

    /**
     * @var bool Indicates if the license of EPP module is valid. Default to FALSE
     */
    private $isEPPLicenseValid = false;

    /**
     * CentreonHostDiscovery constructor.
     *
     * @param HostDiscoveryService $discoveryService
     * @param HostDiscoveryRepositoryInterface $discoveryRepository
     * @param \CentreonHost $host
     * @param \centreonLogAction $centreonLog
     * @param PollerInteractionService $poller
     */
    public function __construct(
        HostDiscoveryService $discoveryService,
        HostDiscoveryRepositoryInterface $discoveryRepository,
        \CentreonHost $host,
        \centreonLogAction $centreonLog,
        PollerInteractionService $poller
    ) {
        parent::__construct();
        $this->discoveryService = $discoveryService;
        $this->discoveryRepository = $discoveryRepository;
        $this->centreonHost = $host;
        $this->centreonLog = $centreonLog;
        $this->poller = $poller;
    }


    /**
     * Add a job.
     *
     * @param string $providerName provider name
     * @param int $connectionParametersId Id of connection parameters
     * @return int Return the new job id.
     */
    private function addJob(string $providerName, int $connectionParametersId): int
    {
        return $this->discoveryRepository->addJob(
            $providerName,
            $connectionParametersId
        );
    }

    /**
     * Add or update connection parameters
     *
     * @param string $providerName Provider name
     * @param string $connectionName Name of the connection parameters
     * @param array $connectionParameters Connection parameters details
     * @return int Return the existing connection parameters id of the newly created otherwise NULL
     * @throws \Exception
     */
    private function addOrUpdateConnectionParameters(
        string $providerName,
        string $connectionName,
        array $connectionParameters
    ): ?int {
        return $this->discoveryRepository->addOrUpdateConnectionParameters(
            $providerName,
            $connectionName,
            $connectionParameters
        );
    }

    /**
     * Retrieve the list of jobs.
     *
     * @return array [[
     *  'id' => ...,
     *  'alias' => ...,
     *  'author' => ...,
     *  'generate_date' => ...,
     *  'status' => ...,
     *  'duration' => ...,
     *  'discovered_items => ...,
     *  'connection_name' => ...], ...
     * ]
     */
    private function searchJobs(): array
    {
        return $this->discoveryRepository->getJobs(
            $this->pagination
        );
    }

    /**
     * Search details of one job.
     *
     * @param int $jobId Job id to search
     * @return array
     * <code>
     * [
     *   "id" => int,
     *   "alias" => string,
     *   "generate_date" => string,
     *   "status" => int,
     *   "duration" => int,
     *   "hosts" => [
     *      [
     *          "id" => int,
     *          "name" => string,
     *          "exist" => bool,
     *          "templates" => [
     *              [
     *                  "id" => int,
     *                  "name" => string,
     *                  "key" => string
     *              ], ...
     *          ]
     *      ], ...
     *    ]
     * ]
     * </code>
     * @throws \RestInternalServerErrorException
     * @throws \RestUnauthorizedException
     */
    public function searchJobDetails(int $jobId): array
    {
        if (!$this->isEPPLicenseValid) {
            throw new \RestUnauthorizedException("Invalid license");
        }

        try {
            $jobDetails = $this->discoveryRepository->getJobDetails($jobId);
            if (!empty($jobDetails)) {
                $jobDetails['hosts'] = [];
                // @todo host should be an entity
                $hosts = $this->discoveryRepository->getHostsByJob($jobId, $this->pagination);

                if (!isset($jobDetails['provider_id']) || empty($hosts)) {
                    return $jobDetails;
                }

                $providerId = $jobDetails['provider_id'];
                $defaultTemplate = $this->discoveryRepository->findDefaultTemplateFromProviderId($providerId);
                if ($defaultTemplate) {
                    $defaultTemplate = $this->discoveryRepository->findCustomTemplateFromTemplateName($defaultTemplate);
                }
                $mappings = $this->discoveryRepository->getMappingsByJob($jobId);

                // get links between mappings and custom templates (or base template if not found)
                $customTemplateMappings = $this->discoveryService->getCustomTemplatesFromMappings($mappings);

                /*
                 * We will look for host-related templates based on host data and
                 * previously got template types.
                 */
                $hostDetails = [];
                foreach ($hosts as $host) {
                    $templates = [];
                    if (!empty($customTemplateMappings[$host['mapping_id']])) {
                        $templates = $customTemplateMappings[$host['mapping_id']];
                    } elseif ($defaultTemplate !== null) {
                        $templates = [$defaultTemplate];
                    }

                    $hostDetails[] = [
                        'id' => (int)$host['id'],
                        'name' => $host['name'],
                        'exist' => (bool)$host['exist'],
                        'duplicate_name' => ($host['count'] > 1),
                        'details' => json_decode($host['data'], true),
                        'templates' => $templates,
                    ];
                }
                $jobDetails['hosts'] = $hostDetails;
                unset($jobDetails['provider_id']);
            }
        } catch (\Exception $ex) {
            throw new \RestInternalServerErrorException($ex->getMessage());
        }

        return $jobDetails;
    }

    /**
     * @return array
     * <code>
     * [ "result" => [
     *    "id" => int,
     *    "alias" => string,
     *    "generate_date" => string,
     *    "status" => int,
     *    "duration" => int,
     *    "hosts" => [
     *        [
     *            "id" => int,
     *            "name" => string,
     *            "exist" => bool,
     *            "duplicate_name" => bool,
     *            "details" => string,
     *            "templates" => [
     *                [
     *                    "id" => int,
     *                    "name" => string,
     *                    "key" => string
     *                ],...
     *            ]
     *        ],...
     *    ],
     *    "_meta" => [
     *        "pagination" = [...]
     *    ]
     * ]
     * </code>
     * @throws \RestInternalServerErrorException
     * @throws \RestUnauthorizedException
     */
    public function getJobDetails(): array
    {
        if (!$this->isEPPLicenseValid) {
            throw new \RestUnauthorizedException("Invalid license");
        }
        try {
            $search = $this->pagination->getSearch();
            $jobId = $this->searchParameterValue('id', $search);
            if (is_null($jobId)) {
                throw new \RestBadRequestException('Missing id parameter');
            }
            return [
                'result' => $this->searchJobDetails($jobId),
                '_meta' => [
                    'pagination' => $this->pagination->toArray()
                ]
            ];
        } catch (\Exception $ex) {
            throw new \RestInternalServerErrorException($ex->getMessage());
        }
    }

    /**
     * Remove a parameter into the given array
     *
     * @param string $parameter parameter to remove
     * @param $array Array where the parameter will be removed
     */
    private function removeParameter(string $parameter, &$array): void
    {
        if (is_array($array) || is_object($array)) {
            foreach ($array as $key => &$value) {
                if ($key === $parameter) {
                    $array = (array)$array;
                    unset($array[$key]);
                } else {
                    if (is_array($value) || is_object($value)) {
                        $this->removeParameter($parameter, $value);
                        if (is_array($value) && empty($value)) {
                            unset($array[$key]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Return the value of parameter found in the array given.
     *
     * @param string $parameter Parameter to search in array
     * @param $array Array containing a list of parameters
     * @return mixed|null Return the value of the parameter otherwise NULL
     */
    private function searchParameterValue(string $parameter, $array)
    {
        $array = (array)$array;
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if ($key === $parameter) {
                    if (is_object($value)) {
                        $value = (array)$value;
                        return $value[key($value)];
                    } else {
                        return $value;
                    }
                } else {
                    if (is_array($value) || is_object($value)) {
                        return $this->searchParameterValue($parameter, $value);
                    } else {
                        return null;
                    }
                }
            }
        } else {
            return null;
        }
    }

    /**
     * @param Pagination $pagination
     */
    public function setPagination(Pagination $pagination): void
    {
        $this->pagination = $pagination;
    }

    /**
     * Sets the security class and sets the second secure key immediately after.
     *
     * @param Security $security
     */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
        $this->security->setSecondKey(
            'Vpk6Ap5d36L0MjoWtGn8i1Kk4a9JEjRxboFHzDuHyjJwJ69FKfvCycwXNVYOfZf2cXtj+L9Gyl9DVodu35afBA=='
        );
    }

    /**
     * Retreive the name of web service object
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_host_discovery';
    }

    /**
     * Get the list of available providers
     *
     * @return array
     * <code>
     *  [
     *      "result" => [
     *          [
     *              "id" => string,
     *              "label" => string,
     *              "version" => string,
     *              "icon" => string
     *          ],...
     *      ]
     *  ]
     * </code>
     * @throws \RestBadRequestException
     */
    public function getProviderParameters(): array
    {
        if (!isset($_GET['provider']) || !$_GET['provider']) {
            throw new \RestBadRequestException('Please send \'provider\' in the request.');
        }
        $providerTemplate = $this->discoveryRepository->getConnectionTemplateByProvider($_GET['provider']);

        $results = [];
        foreach ($providerTemplate as $parameter) {
            $results[] = $parameter->toArray();
        }


        return ['result' => $results];
    }

    /**
     * Decrypt credential entities.
     *
     * @param array $credentialEntities CredentialEntity[]
     */
    private function decryptConnectionParameters(array $credentialEntities): void
    {
        /**
         * @var $credentialEntities ConnectionParameter[]
         */
        foreach ($credentialEntities as $index => $credentialEntity) {
            $name = strtoupper($credentialEntity->getName());
            if (($credentialEntity->getType() === 'password') && !empty($credentialEntity->getValue())) {
                $credentialEntity->setValue(
                    $this->security->decrypt($credentialEntity->getValue())
                );
            }
        }
    }

    /**
     * Add remote Centreon instance in waiting list
     *
     * @return array
     * @throws \RestBadRequestException
     * @throws \RestInternalServerErrorException
     */
    public function postConnectionParameters(): array
    {
        if (!isset($_POST['provider']) || empty(trim($_POST['provider']))) {
            throw new \RestBadRequestException('Provider name undefined.');
        }
        if (!isset($_POST['name']) || empty(trim($_POST['name']))) {
            throw new \RestBadRequestException('Connection parameters name undefined.');
        }
        if (!isset($_POST['parameters']) || empty($_POST['parameters'])) {
            throw new \RestBadRequestException('Connection parameters undefined.');
        }

        $parameters = $_POST['parameters'];
        $template = $this->discoveryRepository->getConnectionTemplateByProvider($_POST['provider']);

        $encryptedCredentials = [];
        foreach ($parameters as $parameterName => $value) {

            $parameterType = $this->extractConnectionParameterType($parameterName, $template);
            if ($parameterType === 'password') {
                // If the password doesn't change, no worth to update it
                if ($value !== self::PASSWORD_TOKEN) {
                    $encryptedCredentials[$parameterName] = $this->security->crypt($value);
                }
            } else {
                $encryptedCredentials[$parameterName] = $value;
            }
        }

        try {
            $credentialId = $this->addOrUpdateConnectionParameters(
                (string)$_POST['provider'],
                (string)$_POST['name'],
                $encryptedCredentials
            );
            if (!is_null($credentialId)) {
                $jobId = $this->addJob((string)$_POST['provider'], $credentialId);
                return ['result' => ['id' => $jobId]];
            }
            return ['result' => []];
        } catch (\Exception $ex) {
            throw new \RestInternalServerErrorException($ex->getMessage());
        }
    }

    /**
     * Retrieve the list of connections parameters.
     *
     * @return array
     * @throws \RestBadRequestException
     */
    public function getConnectionParameters(): array
    {
        $credentials = [];

        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            if (!isset($_GET['provider']) || !$_GET['provider']) {
                throw new \RestBadRequestException('Please send \'provider\' in the request.');
            }

            $credentialType = $this->discoveryRepository->findProviderTypeByProviderName($_GET['provider']);
            $credentials = $this->discoveryRepository->getConnectionParametersByProviderType($credentialType);
        } else {
            /**
             * @var $credentials ConnectionParameter[]
             */
            $credentials = $this->discoveryRepository->getConnectionParameters((int)$_GET['id']);

            foreach ($credentials as $credential) {
                if ($credential->getType() === 'password') {
                    $credential->setValue(self::PASSWORD_TOKEN);
                } elseif (!is_null($credential->getValue())) {
                    $credential->getValue();
                }

                $credentials[] = $credential->toArray();
            }
        }

        return ['result' => $credentials];
    }

    /**
     * Add remote Centreon instance in waiting list
     *
     * @return array
     * @throws \RestInternalServerErrorException
     * @throws \RestUnauthorizedException
     */
    public function getJobs(): array
    {
        if (!$this->isEPPLicenseValid) {
            throw new \RestUnauthorizedException("Invalid license");
        }
        try {
            return [
                'result' => $this->searchJobs(),
                '_meta' => [
                    'pagination' => $this->pagination->toArray()
                ]
            ];
        } catch (\Exception $ex) {
            throw new \RestInternalServerErrorException($ex->getMessage());
        }
    }

    /**
     * Reschedule jobs (reset status to '0')
     *
     * @return array the list of updated job ids
     * @throws \RestUnauthorizedException
     * @throws \RestBadRequestException
     */
    public function postRescheduleJobs(): array
    {
        // check license
        if (!$this->isEPPLicenseValid) {
            throw new \RestUnauthorizedException("Invalid license");
        }

        // check parameters
        if (empty($_POST['jobs'])) {
            throw new \RestBadRequestException('Missing \'jobs\' parameter.');
        }

        $this->discoveryRepository->rescheduleJobs($_POST['jobs']);

        return ['result' => $_POST['jobs']];
    }

    /**
     * Add remote Centreon instance in waiting list
     *
     * @return array ["result" => ["id => string, "label" => string],...]
     */
    public function getProviders(): array
    {
        return ['result' => $this->discoveryRepository->getProviders()];
    }

    /**
     * Add remote Centreon instance in waiting list
     *
     * @throws \Exception
     * @throws \RestInternalServerErrorException
     *
     */
    public function postHost(): array
    {
        //@TODO move repository
        $this->pollerId = $this->discoveryRepository->getDefaultPoller();
        if (is_null($this->pollerId)) {
            throw new \RestInternalServerErrorException('No default poller defined');
        }

        $existingHostList = array_flip($this->centreonHost->getList());

        $resultToReturn = [];

        foreach ($_POST['host'] as $host) {
            $hostDetails = $this->discoveryRepository->getHost((int) $host['host']);
            if (is_null($hostDetails) || array_key_exists($hostDetails['name'], $existingHostList)) {
                continue;
            }
            $mappings = $this->discoveryRepository->getMappingsByJob($hostDetails['job_id']);
            if (empty($mappings)) {
                continue;
            }
            $connectionParameters = $this->discoveryRepository->getConnectionParametersByJob(
                (int) $hostDetails['job_id']
            );
            if (empty($connectionParameters)) {
                continue;
            }
            $this->decryptConnectionParameters($connectionParameters);
            $hostData = json_decode($hostDetails['data'], true);
            $macros = json_decode($this->discoveryRepository->findMacrosByJobId((int) $hostDetails['job_id']), true);

            $finalHost = $this->discoveryService->parseAttributes($mappings, $hostData);

            // check if mandatory fields are set (mapping_id, name, alias, ip_address)
            $matchingAttributes = array_intersect(
                array_keys($finalHost),
                ['mapping_id', 'host_name', 'host_alias', 'host_address']
            );
            if (count($matchingAttributes) !== 4) {
                continue;
            }

            // Enable host by default
            $finalHost["host_activate"]["host_activate"] = '1';
            $finalHost["host_register"] = '1';

            $index = 0;
            $macroInputs = [];
            $macroValues = [];
            $macroPasswords = [];
            $macroDescriptions = [];
            foreach ($macros as $macro) {
                $macroName = $macro['name'];
                $macroDescription = '';

                if ($macro['from'] === 'parameters') {
                    $credentialEntity = $this->searchConnectionParametersByName(
                        $connectionParameters,
                        $macro['value']
                    );
                    $macroValue = !is_null($credentialEntity)
                        ? $credentialEntity->getValue()
                        : '';
                } elseif ($macro['from'] === 'results') {
                    $macroValue = $hostData[$macro['value']];
                } else {
                    $macroValue = $macro['value'];
                }

                // manage specific macros relating to snmp
                if ($macroName === 'SNMPCOMMUNITY') {
                    $finalHost["host_snmp_community"] = $macroValue;
                    continue;
                } elseif ($macroName === 'SNMPVERSION') {
                    $finalHost["host_snmp_version"] = $macroValue;
                    continue;
                }

                $macroInputs[] = $macroName;
                $macroDescriptions[] = $macroDescription;
                $macroValues[] = $macroValue;
                if ($macro['type'] === 'password') {
                    $macroPasswords[$index] = 1;
                }

                $index++;
            }

            // insert host in database
            $centreonHostId = $this->centreonHost->insert($finalHost);
            $hostTemplateIds = $host['host_tpl_id'];
            $this->centreonHost->setTemplates($centreonHostId, $hostTemplateIds);

            $resultToReturn[] = [
                'host_id' => (int)$centreonHostId,
                'host_name' => $hostDetails['name']
            ];

            $this->centreonHost->insertMacro(
                $centreonHostId,
                $macroInputs,
                $macroValues,
                $macroPasswords,
                $macroDescriptions
            );
            $this->centreonHost->deployServices($centreonHostId);
            $this->centreonHost->setPollerInstance($centreonHostId, $this->pollerId);
            $this->centreonLog->insertLog("host", $centreonHostId, $finalHost['host_name'], "a", $finalHost);

            $resultToReturn[] = [
                'host_id' => (int)$centreonHostId,
                'host_name' => $hostDetails['name']
            ];
        }

        return ['result' => $resultToReturn];
    }

    /**
     * Add remote Centreon instance in waiting list
     *
     * @return array
     * @throws \Exception
     */
    public function postHostAndGenerate(): array
    {
        $result = $this->postHost();

        if (!empty($result['result'])) {
            try {
                $this->poller->generateAndExport([$this->pollerId]);
            } catch (\Exception $e) {
                throw new \RestInternalServerErrorException('Cannot generate configuration : ' . $e->getMessage());
            }
        }
        return $result;
    }

    /**
     * Search the credential from a credential list by comparing the credential name.
     *
     * @param array $connectionParameters List of connection parameters
     * @param string $name Value of the connection parameters name to search
     * @return ConnectionParameter|null Return credential entity if success otherwise NULL
     */
    private function searchConnectionParametersByName(
        array $connectionParameters,
        string $name
    ): ?ConnectionParameter {
        foreach ($connectionParameters as $connectionParameter) {
            if (!$connectionParameter instanceof ConnectionParameter) {
                continue;
            }
            if ($connectionParameter->getName() === $name) {
                return $connectionParameter;
            }
        }
        return null;
    }

    /**
     * Authorize to access to the action.
     * Actually we do not check the action,
     * it's a globally response for all actions based on the Topology name 'Discovery'
     *
     * @param string $action The action name
     * @param \CentreonUser $user The current user
     * @param boolean $isInternal If the api is call in internal
     *
     * @return boolean If the user has access to the action
     */
    public function authorize($action, $user, $isInternal = false): bool
    {
        if ($user) {
            return $user->admin
                ? true
                : $this->discoveryRepository->hasRightToUseApi($user);
        }
        return false;
    }

    /**
     * @param bool $isEPPLicenseValid
     */
    public function setEPPLicenseValid(bool $isEPPLicenseValid): void
    {
        $this->isEPPLicenseValid = $isEPPLicenseValid;
    }

    /**
     * Extract the type of the connection parameter based on the name of
     * connection parameter.
     *
     * @param string $parameterName Name of the parameter for which we want to retrieve the type
     * @param array $template Array containing the connection parameters
     * <code>
     * [
     *      [
     *          "name" => string,
     *          "description" => string,
     *          "mandatory" => bool,
     *          "type" => text,
     *          "value" => text,
     *          "locked" => bool,
     *          "hidden" => bool
     *      ],...
     * ]
     * </code>
     * @return string|null
     */
    private function extractConnectionParameterType(string $parameterName, array $template): ?string
    {
        foreach ($template as $connectionParameter) {
            if ($connectionParameter->getName() === $parameterName) {
                return $connectionParameter->getType();
            }
        }

        return null;
    }
}
