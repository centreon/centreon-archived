<?php

namespace CentreonRemote\Domain\Service;

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

    /**
     * statuses
     */
    const SUCCESS = 'success';
    const FAIL = 'fail';

    /**
     * NotifyMasterService constructor.
     */
    public function __construct()
    {
        /**
         * nothing to do here yet
         */
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

        /*
         * todo: fix this to use proper instance token
         */
        try {
            $curl = new Curl();
            $curl->post($ip, array(
                'uniqued' => uniqid(),
            ));
            if ($curl->error)
            {
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
                    'status'=>'fail',
                    'details'=>$details
                ];
            }
        } catch (\ErrorException $e) {
            return [
                'status'=> self::FAIL,
                'details'=> self::UNKNOWN_ERROR
            ];
        }

        return ['status'=>self::SUCCESS];
    }
}