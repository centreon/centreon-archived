<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\MetaServiceConfigurationPage;

/**
 * Defines application features from the specific context.
 */
class MetaServicesApiContext extends CentreonContext
{
    public function __construct()
    {
        parent::__construct();
        $this->jsonreturn = '';
        $this->metaName = 'testmeta';
    }

    /**
     * @Given I have a meta service
     */
    public function iHaveAMetaServices()
    {
        $metaservicePage = new MetaServiceConfigurationPage($this);
        $metaservicePage->setProperties(array(
            'name' => $this->metaName,
            'check_period' => '24x7',
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1
        ));
        $metaservicePage->save();
        $this->restartAllPollers();
    }


    /**
     * @When a call to API configuration services with s equal all is defined
     */
    public function aCallToApiConfigurationServicesWithParameterAll()
    {
        $param = 'all';
        $this->jsonreturn = $this->callToApiConfigurationServices($param);
    }


    /**
     * @When a call to API configuration services with s equal s is defined
     */
    public function aCallToApiConfigurationServicesWithParameterS()
    {
        $param = 's';
        $this->jsonreturn = $this->callToApiConfigurationServices($param);
    }


    /**
     * @When a call to API configuration services with s equal m is defined
     */
    public function aCallToApiConfigurationServicesWithParameterM()
    {
        $param = 'm';
        $this->jsonreturn = $this->callToApiConfigurationServices($param);
    }


    /**
     * @Then the table understands the services and the meta services
     */
    public function theTableUnderstandsTheServicesAndTheMetaServices()
    {
        $service = 0;
        $meta = 0;
        $json = json_decode($this->jsonreturn);

        $i = count($json->items) - 1;
        while ((($service == 0) && ($meta == 0)) || (0 <= $i)) {
            if ($json->items[$i]->text == 'Meta - ' . $this->metaName) {
                $meta = 1;
            } elseif (strstr($json->items[$i]->text, 'Centreon-Server -')) {
                $service = 1;
            }
            $i--;
        }

        if (($service == 0) || ($meta == 0)) {
            throw new Exception('Bad service');
        }
    }


    /**
     * @Then the table understands only the services
     */
    public function theTableUnderstandsOnlyTheServices()
    {
        $service = 0;
        $meta = 0;
        $json = json_decode($this->jsonreturn);

        $i = count($json->items) - 1;
        while ((($service == 0) && ($meta == 0)) || (0 <= $i)) {
            if ($json->items[$i]->text == 'Meta - ' . $this->metaName) {
                $meta = 1;
            } elseif (strstr($json->items[$i]->text, 'Centreon-Server -')) {
                $service = 1;
            }
            $i--;
        }

        if (($service == 0) || ($meta == 1)) {
            throw new Exception('Bad service');
        }
    }


    /**
     * @Then the table understands only the meta services
     */
    public function theTableUnderstandsOnlyTheMeta()
    {
        $service = 0;
        $meta = 0;
        $json = json_decode($this->jsonreturn);

        $i = count($json->items) - 1;
        while ((($service == 0) && ($meta == 0)) || (0 <= $i)) {
            if ($json->items[$i]->text == 'Meta - ' . $this->metaName) {
                $meta = 1;
            } elseif (strstr($json->items[$i]->text, 'Centreon-Server -')) {
                $service = 1;
            }
            $i--;
        }


        if (($service == 1) || ($meta == 0)) {
            throw new Exception('Bad service');
        }
    }


    public function callToApiConfigurationServices($param)
    {
        $apiPage = '/include/common/webServices/rest/internal.php?' .
            'object=centreon_configuration_service&action=list&page_limit=60&page=1&s=' . $param;
        $this->visit($apiPage);
        $this->getSession()->wait(1000);
        $json = strip_tags($this->getSession()->getPage()->getHtml());

        return $json;
    }
}
