<?php

use Centreon\Test\Behat\CentreonContext;

class ClapiContext extends CentreonContext
{
    private const CLAPI_ACTIONS_ORDER = [
        "ACLMENU",
        "ACLACTION",
        "INSTANCE",
        "TP",
        "VENDOR",
        "CMD",
        "RESOURCECFG",
        "CENTBROKERCFG",
        "ENGINECFG",
        "CONTACTTPL",
        "CONTACT",
        "TRAP",
        "HTPL",
        "CG",
        "LDAP",
        "HOST",
        "STPL",
        "HC",
        "HG",
        "SERVICE",
        "SC",
        "ACLRESOURCE",
        "ACLGROUP",
    ];

    private const CONFIGURATION_EXPORT_FILENAME = 'clapi-export.txt';
    protected $test;
    protected $object;
    protected $parameter;
    protected $file;

    public function exportClapi($file = null, $selectList = array(), $filter = null)
    {
        $cmd = 'centreon -u admin -p Centreon!2021 -e';
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
        $cmd = "centreon -u admin -p Centreon!2021 -i " . $this->file['init'];

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

    /**
     * @When the user uses the clapi export command
     */
    public function theUserUsesTheClapiExportCommand()
    {
        $this->exportClapi(sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::CONFIGURATION_EXPORT_FILENAME);
    }

    /**
     * @Then a valid clapi configuration file should be generated
     */
    public function aValidClapiConfigurationFileShouldBeGenerated()
    {
        $exportFileLines = file(sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::CONFIGURATION_EXPORT_FILENAME);
        array_pop($exportFileLines);
        foreach ($exportFileLines as $key => $line) {
            $clapiCommand = explode(';', $line);
            if (count($clapiCommand) < 3) {
                throw new \Exception('Wrong export line format, too few arguments : line ' . $key . ' : ' . $line);
            }
        }
    }

    /**
     * @Then it should contain the supported configuration objects
     */
    public function itShouldContainTheSupportedConfigurationObjects()
    {
        $exportFileLines = file(sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::CONFIGURATION_EXPORT_FILENAME);
        array_shift($exportFileLines);
        array_pop($exportFileLines);
        $clapiActions = [];
        foreach ($exportFileLines as $line) {
            $clapiCommand = explode(';', $line);
            $clapiActions[] = $clapiCommand[0];
        }
        $clapiActions = array_merge(array_unique($clapiActions));
        if (self::CLAPI_ACTIONS_ORDER !== $clapiActions) {
            throw new \Exception(
                'Clapi actions order is not the same as the one in the file : ' . implode(', ', $clapiActions)
            );
        }
    }

    /**
     * @When the user uses the clapi import command
     */
    public function theUserUsesTheClapiImportCommand()
    {
        $this->container->execute(
            'centreon -u admin -p Centreon!2021 -i /tmp/' .
            self::CONFIGURATION_EXPORT_FILENAME,
            'web'
        );
    }

    /**
     * @Then the configuration objects should be added to the central configuration
     */
    public function theConfigurationObjectsShouldBeAddedToTheCentralConfiguration()
    {
        $this->exportClapi(sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::CONFIGURATION_EXPORT_FILENAME);
        $exportFileLines = file(sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::CONFIGURATION_EXPORT_FILENAME);
        array_shift($exportFileLines);
        array_pop($exportFileLines);
        $clapiAddedActions = [];
        foreach ($exportFileLines as $line) {
            if (strpos($line, 'ADD;') !== false) {
                $clapiCommand = explode(';', $line);
                $clapiAddedActions[] = $clapiCommand[0];
            }
        }
        $clapiAddedActions = array_merge(array_unique($clapiAddedActions));

        if ($clapiAddedActions !== self::CLAPI_ACTIONS_ORDER) {
            throw new \Exception(
                'Clapi actions order is not the same as the one in the file : ' .
                implode(', ', array_diff($clapiAddedActions))
            );
        }
    }
}
