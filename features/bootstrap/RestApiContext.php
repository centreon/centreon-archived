<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\ImageListingPage;

class RestApiContext extends CentreonContext
{
    private $envfile;
    private $logfile;
    private $retval;
    private $restCollection;
    private $logFilePrefix;

    /**
     * @Given a Centreon server with REST API testing data
     */
    public function aCentreonServerWithRestApiTestingData()
    {
        // Launch container.
        $this->launchCentreonWebContainer('web_fresh');

        // Copy images.
        $basedir = 'tests/rest_api/images';
        $imgdirs = scandir($basedir);
        foreach ($imgdirs as $dir) {
            if (($dir != '.') && ($dir != '..')) {
                $this->container->copyToContainer(
                    $basedir . '/' . $dir,
                    '/usr/share/centreon/www/img/media/' . $dir,
                    'web'
                );
            }
        }

        // Copy MIB.
        $this->container->copyToContainer(
            'tests/rest_api/IF-MIB.txt',
            '/usr/share/centreon/IF-MIB.txt',
            'web'
        );

        // Synchronize images.
        $this->iAmLoggedIn();
        $page = new ImageListingPage($this);
        $page->synchronize();
    }

    /**
     * @When REST API are called
     */
    public function restApiAreCalled()
    {
        $this->restCollection = 'rest_api.postman_collection.json';
        $this->logFilePrefix = 'rest_api_log';
        $this->callRestApi();
    }

    /**
     * @When realtime REST API are called
     */
    public function realtimeRestApiAreCalled()
    {
        $this->restCollection = 'realtime_rest_api.postman_collection.json';
        $this->logFilePrefix = 'realtime_rest_api_log';
        $this->callRestApi();
    }

    /**
     * launch newman for api tests
     */
    public function callRestApi()
    {
        var_dump(sys_get_temp_dir());
        $env = file_get_contents('tests/rest_api/rest_api.postman_environment.json');
        $env = str_replace(
            '@IP_CENTREON@',
            $this->container->getHost() . ':' . $this->container->getPort('80', 'web'),
            $env
        );
        $this->envfile = tempnam(sys_get_temp_dir(), 'rest_api_env');
        file_put_contents($this->envfile, $env);
        $this->logfile = tempnam(sys_get_temp_dir(), $this->logFilePrefix);
        exec(
            'newman run' .
            ' tests/rest_api/' . $this->restCollection .
            ' --no-color --disable-unicode --reporter-cli-no-assertions' .
            ' --environment ' . $this->envfile .
            ' > ' . $this->logfile,
            $output,
            $retval
        );
        $this->retval = $retval;
        unlink($this->envfile);
    }

    /**
     * @Then they reply as per specifications
     */
    public function theyReplyAsPerSpecifications()
    {
        if (!($this->retval == 0)) {
            copy(
                $this->logfile,
                $this->composeFiles['log_directory'] . '/' . basename($this->logfile) . '.txt'
            );
            unlink($this->logfile);
            throw new \Exception(
                'REST API are not working properly. Check newman log file for more details.'
            );
        }
        unlink($this->logfile);
    }
}
