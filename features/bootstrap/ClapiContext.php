<?php

use Centreon\Test\Behat\CentreonContext;

class ClapiContext extends CentreonContext
{
    protected $test;
    protected $object;
    protected $parameter;
    protected $file;

    public function exportClapi($file = null, $selectList = array(), $filter = null)
    {
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
            $cmd,
            'web'
        );
        return $output;
    }

    /**
     * @Given a Clapi configuration file
     */
    public function aClapiConfigurationFile()
    {
        $this->file['localpath'] = 'tests/clapi_export/clapi-configuration.txt';
        $this->file['init'] = '/tmp/clapi-export.txt';
        $this->file['compare'] = '/tmp/compare-clapi-export.txt';

        $this->container->copyToContainer(
            $this->file['localpath'],
            $this->file['init'],
            'web'
        );
    }

    /**
     * @Given it was imported
     */
    public function itWasImported()
    {
        $cmd = "centreon -u admin -p centreon -i " . $this->file['init'];

        $this->container->execute(
            $cmd,
            'web'
        );
    }

    /**
     * @When I export the configuration through Clapi
     */
    public function IExportTheConfigurationThroughClapi()
    {
        $this->exportClapi($this->file['compare']);
    }

    /**
     * @Then the exported file is similar to the imported filed
     */
    public function theExportedFileIsSimilarToTheImportedFiled()
    {
        $fileLocal = trim(file_get_contents($this->file['localpath'], FILE_USE_INCLUDE_PATH));
        $fileCompare = trim(file_get_contents($this->file['compare'], FILE_USE_INCLUDE_PATH));

        if ($fileLocal != $fileCompare) {
            exec(
                'diff ' . $this->file['localpath'] . ' ' . $this->file['compare'],
                $output
            );
            file_put_contents(
                $this->composeFiles['log_directory'] . '/' .
                    date('Y-m-d-H-i') . '-diffClapi.txt',
                implode("\n", $output)
            );
            throw new \Exception('Configuration not imported');
        }
    }
}
