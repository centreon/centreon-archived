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
     * @return void
     */
    public function setCurl(Curl $curl): void
    {
        $this->curl = $curl;
    }

    /**
     * @return Curl
     */
    public function getCurl(): Curl
    {
        return $this->curl;
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
     * @param string $ip The IP address of the master
     * @param boolean $noCheckCertificate To do not check SLL CA on master
     * @param boolean $noProxy
     * @param (string|null|false)[] $data The information for the master how to contact the remote
     * @return string[]
     * @throws \ErrorException
     */
    public function pingMaster($ip, $data, $noCheckCertificate = false, $noProxy = false)
    {

        $url = "{$ip}/centreon/api/external.php?object=centreon_remote_server&action=addToWaitList";
        $repository = $this->dbManager->getRepository(InformationsRepository::class);
        $applicationKey = $repository->getOneByKey('appKey');
        $version = $repository->getOneByKey('version');

        if (empty($applicationKey)) {
            return [
                'status' => self::FAIL,
                'details' => self::NO_APP_KEY
            ];
        }

        try {
            $curlData = [
                'app_key' => $applicationKey->getValue(),
                'version' => $version->getValue(),
                'http_method' => $data['remoteHttpMethod'] ?? 'http',
                'http_port' => $data['remoteHttpPort'] ?? '',
                'no_check_certificate' => $data['remoteNoCheckCertificate'] ?? 0,
            ];

            if ($noCheckCertificate) {
                $this->getCurl()->setOpt(CURLOPT_SSL_VERIFYPEER, false);
            }
            if ($noProxy) {
                $this->getCurl()->setOpt(CURLOPT_PROXY, false);
            }

            $this->getCurl()->post($url, $curlData);

            if ($this->getCurl()->error) {
                switch ($this->getCurl()->error_code) {
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
