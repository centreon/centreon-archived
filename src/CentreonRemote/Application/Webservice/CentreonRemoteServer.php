<?php
namespace CentreonRemote\Application\Webservice;

use CentreonWebService;
use Centreon\Infrastructure\Service\CentreonWebserviceServiceInterface;

class CentreonRemoteServer extends \CentreonWebService implements CentreonWebserviceServiceInterface
{

    /**
     * Name of web service object
     * 
     * @return string
     */
    public static function getName(): string
    {
        return 'centreon_remote_server';
    }

    /**
     * Add remote Centreon instance in waiting list
     * 
     * @return string
     * @throws \RestBadRequestException
     * @throws \RestConflictException
     */
    public function getAddToWaitList(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        if (!$ip) {
            throw new \RestBadRequestException('Can not access your IP address.');
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \RestBadRequestException('IP is not valid.');
        }

        $statement = $this->pearDB->prepare('SELECT COUNT(id) as count FROM `remote_servers` WHERE `ip` = :ip');
        $statement->execute([':ip' => $ip]);
        $result = $statement->fetch();

        if ((bool) $result['count']) {
            throw new \RestConflictException('IP already in wait list.');
        }

        $createdAt = date('Y-m-d H:i:s');
        $insertQuery = 'INSERT INTO `remote_servers` (`ip`, `is_connected`, `created_at`) ';
        $insertQuery .= "VALUES (:ip, 0, '{$createdAt}')";

        $insert = $this->pearDB->prepare($insertQuery);
        $insert->execute([':ip' => $ip]);

        return '';
    }

    public function authorize($action, $user, $isInternal = false)
    {
        return true;
    }
}
