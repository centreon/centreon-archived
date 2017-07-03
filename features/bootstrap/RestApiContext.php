<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Administration\ImageListingPage;

class RestApiContext extends CentreonContext
{
    private $envfile;
    private $logfile;
    private $retval;

    /**
     *  @Given a Centreon server with REST API testing data
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

        // Synchronize images.
        $this->iAmLoggedIn();
        $page = new ImageListingPage($this);
        $page->synchronize();
    }

    /**
     *  @When REST API are called
     */
    public function restApiAreCalled()
    {
        $env = file_get_contents('tests/rest_api/rest_api.postman_environment.json');
        $env = str_replace(
            '@IP_CENTREON@',
            $this->container->getHost() . ':' . $this->container->getPort('80', 'web'),
            $env);
        $this->envfile = tempnam('/tmp', 'rest_api_env');
        file_put_contents($this->envfile, $env);
        $this->logfile = tempnam('/tmp', 'rest_api_log');
        exec(
            'newman run' .
            ' --no-color --disable-unicode --reporter-cli-no-assertions' .
            ' --environment ' . $this->envfile .
            ' tests/rest_api/rest_api.postman_collection.json' .
            ' > ' . $this->logfile,
            $output,
            $retval
        );
        $this->retval = $retval;
        unlink($this->envfile);
    }

    /**
     *  @Then they reply as per specifications
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
