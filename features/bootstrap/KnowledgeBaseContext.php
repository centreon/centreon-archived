<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\HostConfigurationPage;
use Centreon\Test\Behat\Configuration\KBServiceListingPage;
use Centreon\Test\Behat\Configuration\ServiceConfigurationPage;
use Centreon\Test\Behat\Administration\KBParametersPage;

/**
 * Defines application features from the specific context.
 */
class KnowledgeBaseContext extends CentreonContext
{
    public function __construct()
    {
        parent::__construct();
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

        $containerId = $this->container->getContainerId('mediawiki', false);
        $page = new KBParametersPage($this);
        $page->setProperties(
            array(
                'kb_wiki_url' => 'http://' . $containerId,
                'kb_wiki_account' => 'WikiSysop',
                'kb_wiki_password' => 'centreon'
            )
        );
        $page->save();
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
            'passive_checks_enabled' => 1
        ));
        $hostPage->save();
        $this->restartAllPollers();
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
            'passive_checks_enabled' => 1
        ));
        $servicePage->save();
        $this->restartAllPollers();
    }

    /**
     * @Given the knowledge configuration page with procedure
     */
    public function theKnowledgeConfigurationPageWithProcedure()
    {
        $this->aHostConfigured();
        $this->iAddAProcedureConcerningThisHostInMediawiki();
        $this->aLinkTowardThisHostProcedureIsAvailableInConfiguration();
    }

    /**
     * @When I add a procedure concerning this host in MediaWiki
     */
    public function iAddAProcedureConcerningThisHostInMediawiki()
    {
        /* Go to the page to options page */
        $this->visit('/main.php?p=61001');

        $this->assertFind('css', '.list_two td:nth-child(5) a:nth-child(1)')->click();

        $this->spin(
            function ($context) {
                $windowNames = $context->getSession()->getWindowNames();
                return count($windowNames) > 1;
            },
            'Wiki procedure window is not opened.',
            10
        );
        $windowNames = $this->getSession()->getWindowNames();
        $this->getSession()->switchToWindow($windowNames[1]);

        /* Add wiki page */
        $checkurl = 'Host_:_' . $this->hostName;
        $currenturl = urldecode($this->getSession()->getCurrentUrl());
        if (!strstr($currenturl, $checkurl)) {
            throw new Exception(
                'Redirected to wrong page: ' . $currenturl .
                ', should have contain ' . $checkurl . '.'
            );
        }

        $this->assertFind('css', '#wpTextbox1')->setValue('add wiki host page');
        $this->assertFind('css', 'input[name="wpSave"]')->click();

        /* cron */
        $this->container->execute("php /usr/share/centreon/cron/centKnowledgeSynchronizer.php", 'web');

        /* Apply config */
        $this->restartAllPollers();
    }

    /**
     * @When I add a procedure concerning this service in MediaWiki
     */
    public function iAddAProcedureConcerningThisServiceInMediawiki()
    {
        // Create wiki page.
        $page = new KBServiceListingPage($this);
        $page->createWikiPage(array('host' => $this->serviceHostName, 'service' => $this->serviceName));

        $this->spin(
            function ($context) {
                $windowNames = $context->getSession()->getWindowNames();
                return count($windowNames) > 1;
            },
            'Wiki procedure window is not opened.',
            10
        );
        $windowNames = $this->getSession()->getWindowNames();
        $this->getSession()->switchToWindow($windowNames[1]);

        // Check that wiki page is valid.
        $checkurl = 'Service_:_' . $this->serviceHostName . '_/_' . $this->serviceName;
        $currenturl = urldecode($this->getSession()->getCurrentUrl());
        if (!strstr($currenturl, $checkurl)) {
            throw new Exception(
                'Redirected to wrong page: ' . $currenturl .
                ', should have contain ' . $checkurl . '.'
            );
        }

        $this->assertFind('css', '#wpTextbox1')->setValue('add wiki service page');
        $this->assertFind('css', 'input[name="wpSave"]')->click();

        /* cron */
        $this->container->execute("php /usr/share/centreon/cron/centKnowledgeSynchronizer.php", 'web');

        /* Apply config */
        $this->restartAllPollers();
    }

    /**
     * @When I delete a wiki procedure
     */
    public function iDeleteAWikiProcedure()
    {
        /* Go to the page to options page */
        $this->visit('/main.php?p=61001');
        $this->assertFind('css', '.list_two td:nth-child(5) a:nth-child(4)')->click();
    }

    /**
     * @Then a link towards this host procedure is available in configuration
     */
    public function aLinkTowardThisHostProcedureIsAvailableInConfiguration()
    {
        /* check url config */
        $this->visit('/main.php?p=60101');
        $this->assertFind('css', '.list_two td:nth-child(2) a:nth-child(1)')->click();
        $this->assertFind('css', '#c5 a:nth-child(1)')->click();
        $fieldValue = $this->assertFind('css', 'input[name="ehi_notes_url"]');
        $originalValue = $fieldValue->getValue();

        if (!strstr(
            $originalValue,
            './include/configuration/configKnowledge/proxy/proxy.php?host_name=$HOSTNAME$'
        )
        ) {
            throw new Exception('Bad url');
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
        $this->assertFind('css', '#c5 a:nth-child(1)')->click();
        $fieldValue = $this->assertFind('css', 'input[name="esi_notes_url"]');
        $originalValue = $fieldValue->getValue();

        if (!strstr(
            $originalValue,
            './include/configuration/configKnowledge/proxy/proxy.php?' .
            'host_name=$HOSTNAME$&service_description=$SERVICEDESC$'
        )
        ) {
            throw new Exception('Bad url');
        }
    }

    /**
     * @Then the page is deleted and the option disappear
     */
    public function thePageIsDeletedAndTheOptionDisappear()
    {
        $this->spin(
            function ($context) {
                if (' No wiki page defined ' == $this->assertFind('css', '.list_two td:nth-child(4) font')->getHtml()) {
                    return true;
                } else {
                    return false;
                }
            },
            'Delete option id display'
        );
    }
}
