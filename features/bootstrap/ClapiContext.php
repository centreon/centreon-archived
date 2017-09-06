<?php

use Centreon\Test\Behat\CentreonContext;

class ClapiContext extends CentreonContext {

    protected $test;
    protected $object;
    protected $parameter;
    protected $file;

    public function exportClapi($file = null, $selectList = array(), $filter = null) {
        $cmd = "centreon -u admin -p centreon -e";
        if (!empty($selectList)) {
            foreach ($selectList as $select) {
                $cmd .= " --select='" . $select . "'";
            }
        }
        if ($filter) {
            $cmd .= " --filter='" . $filter . "'";
        }
        if ($file) {
            $cmd .= " > " . $file;
        }

        $output = $this->container->execute(
            $cmd, 'web_fresh'
        );
        return $output;
    }

    /**
     * @Given a configuration
     */
    public function aConfiguration() {
        $this->file['localpath'] = 'tests/clapi_export/clapi-export.txt';
        $this->file['init'] = '/tmp/clapi-export.txt';
        $this->file['compare'] = '/tmp/compare-clapi-export.txt';

        $this->container->copyToContainer(
            $this->file['localpath'], $this->file['init'], 'web_fresh'
        );
    }

    /**
     * @When I import this configuration
     */
    public function IimportThisConfiguration() {
        $cmd = "centreon -u admin -p centreon -i " . $this->file['init'];

        $this->container->execute(
            $cmd, 'web_fresh'
        );
    }

    /**
     * @When I export it
     */
    public function IexportIt() {
        $this->exportClapi($this->file['compare']);
    }

    /**
     * @Then The configuration exported is similar when it was imported
     */
    public function TheConfigurationExportedIsSimilarWhenItWasImported() 
    {
        if (file_get_contents($this->file['localpath'], FILE_USE_INCLUDE_PATH) !== file_get_contents($this->file['compare'], FILE_USE_INCLUDE_PATH)) {
            throw new \Exception('Configuration not imported');
        }
    }
}
