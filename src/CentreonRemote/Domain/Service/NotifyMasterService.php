<?php

namespace CentreonRemote\Domain\Service;

use Centreon\Domain\Repository\InformationsRepository;
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use Curl\Curl;

class NotifyMasterService
{

    /**
     * fail constants
     */
    const CANT_RESOLVE_HOST = 'Could not resolve the host';
    const CANT_CONNECT = 'Could not connect';
    const TIMEOUT = 'Timeout';
    const UNKNOWN_ERROR = 'Unknown Error';
    const WRONG_IP = 'Wrong IP';
    const NO_APP_KEY = 'No Application Key found';

    /**
     * statuses
     */
    const SUCCESS = 'success';
    const FAIL = 'fail';

    /**
     * @var CentreonDBManagerService
     */
    private $dbManager;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @return Curl
     */
    public function setCurl(Curl $curl): void
    {
        $this->curl = $curl;
    }

    /**
     * NotifyMasterService constructor.
     *
     * @param CentreonDBManagerService $dbManager
     */
    public function __construct(CentreonDBManagerService $dbManager)
    {
        $this->dbManager = $dbManager;
    }

    /**
     * Ping the master IP requesting to be slave for it.
     * @param string $ip
     * @return array
     * @throws \ErrorException
     */
    public function pingMaster($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return [
                'status' => self::FAIL,
                'details' => self::WRONG_IP
            ];
        }

        $url = "{$ip}/centreon/api/external.php?object=centreon_remote_server&action=addToWaitList";
        $repository = $this->dbManager->getRepository(InformationsRepository::class);
        $applicationKey = $repository->getOneByKey('appKey');
        $version = $repository->getOneByKey('version');

        if (empty($applicationKey)){
            return [
                'status' => self::FAIL,
                'details' => self::NO_APP_KEY
            ];
        }

        try {
            $curlData = [
                'app_key' => $applicationKey->getValue(),
                'version' => $version->getValue(),
            ];
            $this->curl->post($url, $curlData);

            if ($curl->error) {
                switch ($curl->error_code) {
                    case 6:
                        $details = self::CANT_RESOLVE_HOST;
                        break;
                    case 7:
                        $details = self::CANT_CONNECT;
                        break;
                    case 28:
                        $details = self::TIMEOUT;
                        break;
                    default:
                        $details = self::UNKNOWN_ERROR;
                        break;
                }

                return [
                    'status' => 'fail',
                    'details' => $details
                ];
            }
        } catch (\ErrorException $e) {
            return [
                'status' => self::FAIL,
                'details' => self::UNKNOWN_ERROR
            ];
        }

        return ['status' => self::SUCCESS];
    }
}