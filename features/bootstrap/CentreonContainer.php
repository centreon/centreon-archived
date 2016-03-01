<?php

/**
 *  Run a Centreon container and manage it.
 */
class CentreonContainer
{
    private $container_id;

    public function __construct()
    {
        $image = getenv('CENTREON_WEB_IMAGE');
        if (empty($image))
            throw new \Exception('Centreon Web image is not set (CENTREON_WEB_IMAGE environment variable).');
        $this->container_id = shell_exec('docker run -t -d -p 80 "' . $image . '" | tr -d "\n"');
        if (empty($this->container_id))
            throw new \Exception('Could not run Centreon Web container');
        $ch = curl_init('http://localhost:' . $this->getPort());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        while (!curl_exec($ch))
            sleep(1);
        curl_close($ch);
    }

    public function __destruct()
    {
        shell_exec('docker stop "' . $this->container_id . '"');
        shell_exec('docker rm "' . $this->container_id . '"');
    }

    public function getPort()
    {
        return shell_exec('docker port "' . $this->container_id . '" 80 | cut -d : -f 2 | tr -d "\n"');
    }
}

?>
