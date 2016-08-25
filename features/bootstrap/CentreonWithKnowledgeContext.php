<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\ConfigurationPollersPage;
use Centreon\Test\Behat\HostConfigurationPage;
use Centreon\Test\Behat\ServiceConfigurationPage;

/**
 * Defines application features from the specific context.
 */
class CentreonWithKnowledgeContext extends CentreonContext
{
    private $pollers_page;

    public function __construct()
    {
        parent::__construct();
        $this->pollers_page = new ConfigurationPollersPage($this);
        $this->hostName = 'MediawikiHost';
        $this->serviceHostName = 'Centreon-Server';
        $this->serviceName = 'MediawikiService';
    }


    /**
     * @Given I am logged in a Centreon server with MediaWiki installed
     */
    public function iAmLoggedInACentreonServerWithWikiInstalled()
    {
        $this->launchCentreonWebContainer('web_kb');
        $this->container->waitForAvailableUrl(
            'http://' . $this->container->getHost() . ':' .
            $this->container->getPort(80, 'mediawiki') . '/index.php/Main_Page'
        );
        $this->iAmLoggedIn();
    }


    /**
     * @Given a host configured
     */
    public function aHostConfigured()
    {
        $hostPage = new HostConfigurationPage($this);
        $hostPage->setProperties(array(
            'name' => $this->hostName,
            'alias' => $this->hostName,
            'address' => 'localhost',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1));
        $hostPage->save();
        (new ConfigurationPollersPage($this))->restartEngine();
    }


    /**
     * @Given a service configured
     */
    public function aServiceConfigured()
    {
        $servicePage = new ServiceConfigurationPage($this);
        $servicePage->setProperties(array(
            'hosts' => $this->serviceHostName,
            'description' => $this->serviceName,
            'templates' => 'generic-service',
            'check_command' => 'check_centreon_dummy',
            'check_period' => '24x7',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1,
            'active_checks_enabled' => 0,
            'passive_checks_enabled' => 1));
        $servicePage->save();
        (new ConfigurationPollersPage($this))->restartEngine();
    }


    /**
     * @When I add a procedure concerning this host in MediaWiki
     */
    public function iAddAProcedureConcerningThisHostInMediawiki()
    {
        /* Go to the page to options page */
        $this->visit('/main.php?p=61001');
        $this->assertFind('css', '.list_two td:nth-child(5) a:nth-child(1)')->click();
        sleep(5);
        $windowNames = $this->getSession()->getWindowNames();
        if(count($windowNames) > 1) {
            $this->getSession()->switchToWindow($windowNames[1]);
        }

        /* Add wiki page */
        $checkurl = 'Host:'.$this->hostName;
        if( !strstr($this->getSession()->getCurrentUrl(), $checkurl)) {
            throw new Exception(' Mauvaise url');
        }

        $this->assertFind('css', '#wpTextbox1')->setValue('add wiki host page');
        $this->assertFind('css', 'input[name="wpSave"]')->click();

        /* cron */
        $this->container->execute("php /usr/share/centreon/cron/centKnowledgeSynchronizer.php", 'web');
        sleep(2);
        /* Apply config */
        (new ConfigurationPollersPage($this))->restartEngine();
    }


    /**
     * @When I add a procedure concerning this service in MediaWiki
     */
    public function iAddAProcedureConcerningThisServiceInMediawiki()
    {
        /* Go to the page to options page */
        $this->visit('/main.php?p=61002');
        $this->assertFind('css', '.list_one:nth-child(4) td:nth-child(6) a:nth-child(1)')->click();
        sleep(5);
        $windowNames = $this->getSession()->getWindowNames();
        if(count($windowNames) > 1) {
            $this->getSession()->switchToWindow($windowNames[1]);
        }

        /* Add wiki page */
        $checkurl = 'Service:'.$this->serviceHostName.'_'.$this->serviceName;
        if( !strstr(urldecode($this->getSession()->getCurrentUrl()), $checkurl)) {
           throw new Exception(' Mauvaise url');
        }

        $this->assertFind('css', '#wpTextbox1')->setValue('add wiki service page');
        $this->assertFind('css', 'input[name="wpSave"]')->click();

        /* cron */
        $this->container->execute("php /usr/share/centreon/cron/centKnowledgeSynchronizer.php", 'web');
        sleep(2);
        /* Apply config */
        (new ConfigurationPollersPage($this))->restartEngine();
    }


    /**
     * @Then a link towards this host procedure is available in configuration
     */
    public function aLinkTowardThisHostProcedureIsAvailableInConfiguration()
    {
        /* check url config */
        $this->visit('/main.php?p=60101');
        $this->assertFind('css', '.list_two td:nth-child(2) a:nth-child(1)')->click();
        sleep(2);
        $this->assertFind('css', '#c5 a:nth-child(1)')->click();
        sleep(2);
        $fieldValue = $this->assertFind('css', 'input[name="ehi_notes_url"]');
        $originalValue = $fieldValue->getValue();

        if( !strstr($originalValue, '/centreon/include/configuration/configKnowledge/proxy/proxy.php?host_name=$HOSTNAME$')) {
            throw new Exception(' Mauvaise url');
        }
    }


    /**
     * @Then a link towards this service procedure is available in configuration
     */
    public function aLinkTowardThisServiceProcedureIsAvailableInConfiguration()
    {
        /* check url config */
        $this->visit('/main.php?p=60201');
        $this->assertFind('css', '.list_one:nth-child(8) td:nth-child(3) a')->click();
        sleep(2);
        $this->assertFind('css', '#c5 a:nth-child(1)')->click();
        sleep(2);
        $fieldValue = $this->assertFind('css', 'input[name="esi_notes_url"]');
        $originalValue = $fieldValue->getValue();

        if( !strstr($originalValue, '/centreon/include/configuration/configKnowledge/proxy/proxy.php?host_name=$HOSTNAME$&service_description=$SERVICEDESC$')) {
            throw new Exception(' Mauvaise url');
        }
    }

}
