<?php

namespace CentreonRemote\Application\Clapi;

use Centreon\Domain\Repository\InformationsRepository;
use Centreon\Domain\Repository\TopologyRepository;
use Centreon\Domain\Repository\OptionsRepository;
use Pimple\Container;
use Centreon\Infrastructure\Service\CentreonClapiServiceInterface;
use ReflectionClass;

/**
 * Class to manage remote server with clapi (enable, disable, import)
 */
class CentreonRemoteServer implements CentreonClapiServiceInterface
{

    /**
     * @var Container
     * todo: extract only services we need to avoid using whole container
     */
    private $di;

    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    public static function getName() : string
    {
        return (new \ReflectionClass(__CLASS__))->getShortName();
    }

    /**
     * Clapi command to enable remote server
     *
     * @param string $ip ip address of the central server
     */
    public function enableRemote(string $string_ip)
    {
        /* Set default value */
        $noCheckCertificate = false;
        $data = array(
            'remoteHttpMethod'         => 'http',
            'remoteHttpPort'           => null,
            'remoteNoCheckCertificate' => false,
        );

        /* Check CLAPI */
        $options = explode (';', $string_ip);

        if (count($options) == 5) {
            $string_ip = $options[0];
            $noCheckCertificate = $options[1];
            $data['remoteHttpMethod'] = $options[2];
            $data['remoteHttpPort'] = $options[3];
            $data['remoteNoCheckCertificate'] = $options[4];
        } elseif (count($options) > 1) {
            echo "Argument error number. Check your commmand";
            return 1;
        }

        /* Extract host from URI */
        $aIPMaster = array();
        $pattern_extract_host = '/^[a-z][a-z0-9+\-.]*:\/\/([a-z0-9\-._~%!$&\'()*+,;=]+@)?([a-z0-9\-._~%]+|\[[a-z0-9\-._~%!$&\'()*+,;=:]+\])/';
        $ipList = explode(',', $string_ip);
        foreach ($ipList as $ip) {
            if (preg_match($pattern_extract_host, $ip, $matches)) {
                $ip = $matches[2];
            }
            $aIPMaster[] = $ip;
        }

        echo "Starting Centreon Remote enable process: \n";

        echo "Limiting Menu Access...";
        $result = $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getRepository(TopologyRepository::class)->disableMenus();
        echo (($result) ? 'Success' : 'Fail') . "\n";

        echo "Limiting Actions...";
        $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getRepository(InformationsRepository::class)->toggleRemote('yes');
        echo "Done\n";

        echo "Authorizing Master...";
        $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getRepository(InformationsRepository::class)->authorizeMaster(
            implode(',',$aIPMaster)
        );
        echo "Done\n";

        echo "Set 'remote' instance type...";
        system(
            "sed -i -r 's/(\\\$instance_mode?\s+=?\s+\")([a-z]+)(\";)/\\1remote\\3/' " . _CENTREON_ETC_ . "/conf.pm"
        );
        echo "Done\n";

        echo "Notifying Master...";
        $result = "";
        foreach ($ipList as $ip) {
            $result = $this->getDi()['centreon.notifymaster']->pingMaster(
                $ip,
                $noCheckCertificate,
                $data
            );
            if (!empty($result['status']) && $result['status'] == 'success') {
                echo "Success\n";
                break;
            }
        }
        if (empty($result['status']) || $result['status'] != 'success') {
            printf("Fail: %s\n", $result['details']);
        }

        echo "Centreon Remote enabling finished.\n";
    }

    /**
     * Clapi command to disable remote server
     */
    public function disableRemote(): void
    {
        echo "Starting Centreon Remote disable process: \n";

        echo "Restoring Menu Access...";
        $result = $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getRepository(TopologyRepository::class)->enableMenus();
        echo ($result) ? 'Success' : 'Fail' . "\n";

        echo "Restoring Actions...";
        $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->getRepository(InformationsRepository::class)->toggleRemote('no');
        echo 'Done'. "\n";

        echo "Restore 'central' instance type...";
        system(
            "sed -i -r 's/(\\\$instance_mode?\s+=?\s+\")([a-z]+)(\";)/\\1central\\3/' " . _CENTREON_ETC_ . "/conf.pm"
        );
        echo "Done\n";

        echo "Centreon Remote disabling finished.\n";
    }

    /**
     * Import files which are stored in import directory
     */
    public function import(): void
    {
        echo "Starting Centreon Remote import process: \n";
        echo "Importing...";

        try {
            $this->getDi()['centreon_remote.export']->import();
            echo "Success\n";
        } catch (\Exception $e) {
            echo "Fail: " . $e->getMessage() . "\n";
        }

        echo "Centreon Remote import finished.\n";
    }

    public function getDi(): Container
    {
        return $this->di;
    }
}
