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
     * @param string $parametersString parameters string made of
     * a comma separated list of hosts/urls/ip adresses representing the central,
     * a boolean to enable/disable certificate check to contact the central,
     * the method to use to contact the remote (http or https),
     * the http port to use to contact the remote,
     * a boolean to enable/disable certificate check to contact the remote,
     * a boolean to enable/disable the use of proxy to contact the central
     * @return int|void
     */
    public function enableRemote(string $parametersString)
    {
        /* Set default value */
        $noCheckCertificate = false;
        $data = array(
            'remoteHttpMethod' => 'http',
            'remoteHttpPort' => null,
            'remoteNoCheckCertificate' => false,
        );
        $urlString = $noProxy = '';

        /* Check CLAPI */
        $options = explode(';', $parametersString);

        if (count($options) === 6) {
            $urlString = $options[0];
            $noCheckCertificate = $options[1];
            $data['remoteHttpMethod'] = $options[2];
            $data['remoteHttpPort'] = $options[3];
            $data['remoteNoCheckCertificate'] = $options[4];
            $noProxy = $options[5];
        } elseif (count($options) > 1) {
            echo "Expecting 6 parameters, received " . count($options) . "\n";
            return 1;
        }

        /* Extract host from URI */
        $hostList = array();
        $pattern_extract_host = '/^[htps:\/]*([a-z0-9.-]+)[:0-9]*$/';
        $urlList = explode(',', $urlString);
        foreach ($urlList as $url) {
            if (preg_match($pattern_extract_host, $url, $matches)) {
                $hostList[] = $matches[1];
            }
        }

        echo "Starting Centreon Remote enable process:\n";

        echo "Limiting Menu Access...               ";
        $result =
            $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]
                ->getRepository(TopologyRepository::class)
                ->disableMenus();
        echo (($result) ? 'Success' : 'Fail') . "\n";

        echo "Limiting Actions...                   ";
        $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]
            ->getRepository(InformationsRepository::class)
            ->toggleRemote('yes');
        echo "Done\n";

        echo "Authorizing Master...                 ";
        $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]
            ->getRepository(InformationsRepository::class)
            ->authorizeMaster(
                implode(',', $hostList)
            );
        echo "Done\n";

        echo "Set 'remote' instance type...         ";
        system(
            "sed -i -r 's/(\\\$instance_mode?\s+=?\s+\")([a-z]+)(\";)/\\1remote\\3/' " . _CENTREON_ETC_ . "/conf.pm"
        );
        echo "Done\n";

        echo "Notifying Master...\n";
        $result = "";
        foreach ($urlList as $host) {
            echo "  Trying host '$host'... ";
            $result = $this->getDi()['centreon.notifymaster']->pingMaster(
                $host,
                $data,
                $noCheckCertificate,
                $noProxy
            );
            if (!empty($result['status']) && $result['status'] == 'success') {
                echo "Success\n";
                break;
            }
            printf("Fail [Details: %s]\n", $result['details']);
        }

        echo "Centreon Remote enabling finished.\n";
    }

    /**
     * Clapi command to disable remote server
     */
    public function disableRemote(): void
    {
        echo "Starting Centreon Remote disable process:\n";

        echo "Restoring Menu Access...              ";
        $result =
            $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]
                ->getRepository(TopologyRepository::class)
                ->enableMenus();
        echo ($result) ? 'Success' : 'Fail' . "\n";

        echo "Restoring Actions...                  ";
        $this->getDi()[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]
            ->getRepository(InformationsRepository::class)
            ->toggleRemote('no');
        echo 'Done'. "\n";

        echo "Restore 'central' instance type...    ";
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
        echo date("Y-m-d H:i:s") . " - INFO - Starting Centreon Remote import process...\n";

        try {
            $this->getDi()['centreon_remote.export']->import();
            echo date("Y-m-d H:i:s") . " - INFO - Import succeed\n";
        } catch (\Exception $e) {
            echo date("Y-m-d H:i:s") . " - ERROR - Import failed\n";
            echo date("Y-m-d H:i:s") . " - ERROR - Error message: " . $e->getMessage() . "\n";
        }

        echo date("Y-m-d H:i:s") . " - INFO - Centreon Remote import process finished.\n";
    }

    public function getDi(): Container
    {
        return $this->di;
    }
}
