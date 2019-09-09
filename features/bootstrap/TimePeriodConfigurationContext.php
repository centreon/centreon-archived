<?php

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\TimeperiodConfigurationPage;
use Centreon\Test\Behat\Configuration\TimeperiodConfigurationListingPage;

class TimePeriodConfigurationContext extends CentreonContext
{
    private $currentPage;

    private $initialProperties = array(
        'name' => 'timePeriodName',
        'alias' => 'timePeriodAlias',
        'sunday' => '14:00-16:00',
        'monday' => '07:00-12:00,13:00-18:00',
        'tuesday' => '07:00-18:00',
        'wednesday' => '07:00-12:00,13:00-17:00',
        'thursday' => '07:00-18:00',
        'friday' => '07:00-18:00',
        'saturday' => '10:00-16:00',
        'templates' => 'none',
        'exceptions' => array(
            array(
                'day' => 'december 25',
                'timeRange' => '00:00-22:59,23:00-24:00'
            ),
            array(
                'day' => 'january 1',
                'timeRange' => '00:00-24:00'
            ),
            array(
                'day' => 'july 14',
                'timeRange' => '00:00-24:00'
            ),
            array(
                'day' => 'may 25',
                'timeRange' => '00:00-24:00'
            )
        )
    );

    private $duplicatedProperties = array(
        'name' => 'timePeriodName_1',
        'alias' => 'timePeriodAlias',
        'sunday' => '14:00-16:00',
        'monday' => '07:00-12:00,13:00-18:00',
        'tuesday' => '07:00-18:00',
        'wednesday' => '07:00-12:00,13:00-17:00',
        'thursday' => '07:00-18:00',
        'friday' => '07:00-18:00',
        'saturday' => '10:00-16:00',
        'templates' => 'none',
        'exceptions' => array(
            array(
                'day' => 'december 25',
                'timeRange' => '00:00-22:59,23:00-24:00'
            ),
            array(
                'day' => 'january 1',
                'timeRange' => '00:00-24:00'
            ),
            array(
                'day' => 'july 14',
                'timeRange' => '00:00-24:00'
            ),
            array(
                'day' => 'may 25',
                'timeRange' => '00:00-24:00'
            )
        )
    );

    private $AugustHolidays = array(
        'name' => 'timePeriodName',
        'alias' => 'timePeriodAlias',
        'sunday' => '14:00-16:00',
        'monday' => '07:00-12:00,13:00-18:00',
        'tuesday' => '07:00-18:00',
        'wednesday' => '07:00-12:00,13:00-17:00',
        'thursday' => '07:00-18:00',
        'friday' => '07:00-18:00',
        'saturday' => '10:00-16:00',
        'templates' => 'none',
        'exceptions' => array(
            array(
                'day' => 'august 1 - 31',
                'timeRange' => '00:00-24:00'
            )
        )
    );

    /**
     * @When I create a time period with separated holidays dates excluded
     */
    public function iCreateATimePeriodWithSeparatedHolidaysDatesExcluded()
    {
        $this->currentPage = new TimeperiodConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @Then all properties of my time period are saved
     */
    public function allPropertiesOfMyTimePeriodAreSaved()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new TimeperiodConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->initialProperties['name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->initialProperties as $key => $value) {
                        if ($key != 'exceptions' && $value != $object[$key]) {
                            $this->tableau[] = $key;
                        }
                        if ($key == 'exceptions') {
                            $stringValue = '';
                            foreach ($value as $array) {
                                $stringValue = $stringValue . implode(',', $array) . ' ';
                            }
                            $stringObject = '';
                            foreach ($object[$key] as $array) {
                                $stringObject = $stringObject . implode(',', $array) . ' ';
                            }
                            if ($stringValue != $stringObject) {
                                $this->tableau[] = $key;
                            }
                        }
                    }
                    return count($this->tableau) == 0;
                },
                "Some properties are not being updated : ",
                5
            );
        } catch (\Exception $e) {
            $this->tableau = array_unique($this->tableau);
            throw new \Exception("Some properties are not being updated : " . implode(',', $this->tableau));
        }
    }

    /**
     * @When I create a time period with a range of dates to exclude
     */
    public function iCreateATimePeriodWithARangeOfDatesToExclude()
    {
        $this->currentPage = new TimeperiodConfigurationPage($this);
        $this->currentPage->setProperties($this->AugustHolidays);
        $this->currentPage->save();
    }

    /**
     * @Then all properties of my time period are saved with the exclusions
     */
    public function allPropertiesOfMyTimePeriodAreSavedWithTheExclusions()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new TimeperiodConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->AugustHolidays['name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->AugustHolidays as $key => $value) {
                        if ($key != 'exceptions' && $value != $object[$key]) {
                            $this->tableau[] = $key;
                        }
                        if ($key == 'exceptions') {
                            $stringValue = '';
                            foreach ($value as $array) {
                                $stringValue = $stringValue . implode(',', $array) . ' ';
                            }
                            $stringObject = '';
                            foreach ($object[$key] as $array) {
                                $stringObject = $stringObject . implode(',', $array) . ' ';
                            }
                            if (strcmp($stringValue, $stringObject) !== 0) {
                                $this->tableau[] = $key;
                            }
                        }
                    }
                    return count($this->tableau) == 0;
                },
                "Some properties are not being updated : ",
                5
            );
        } catch (\Exception $e) {
            $this->tableau = array_unique($this->tableau);
            throw new \Exception("Some properties are not being updated : " . implode(',', $this->tableau));
        }
    }

    /**
     * @Given an existing time period
     */
    public function anExistingTimePeriod()
    {
        $this->currentPage = new TimeperiodConfigurationPage($this);
        $this->currentPage->setProperties($this->initialProperties);
        $this->currentPage->save();
    }

    /**
     * @When I duplicate the time period
     */
    public function iDuplicateTheTimePeriod()
    {
        $this->currentPage = new TimeperiodConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Duplicate');
    }

    /**
     * @Then a new time period is created with identical properties except the name
     */
    public function aNewTimePeriodIsCreatedWithIdenticalPropertiesExceptTheName()
    {
        $this->tableau = array();
        try {
            $this->spin(
                function ($context) {
                    $this->currentPage = new TimeperiodConfigurationListingPage($this);
                    $this->currentPage = $this->currentPage->inspect($this->duplicatedProperties['name']);
                    $object = $this->currentPage->getProperties();
                    foreach ($this->duplicatedProperties as $key => $value) {
                        if ($key != 'exceptions' && $value != $object[$key]) {
                            $this->tableau[] = $key;
                        }
                        if ($key == 'exceptions') {
                            $stringValue = '';
                            foreach ($value as $array) {
                                $stringValue = $stringValue . implode(',', $array) . ' ';
                            }
                            $stringObject = '';
                            foreach ($object[$key] as $array) {
                                $stringObject = $stringObject . implode(',', $array) . ' ';
                            }
                            if (strcmp($stringValue, $stringObject) !== 0) {
                                $this->tableau[] = $key;
                            }
                        }
                    }
                    return count($this->tableau) == 0;
                },
                "Some properties are not being updated : ",
                5
            );
        } catch (\Exception $e) {
            $this->tableau = array_unique($this->tableau);
            throw new \Exception("Some properties are not being updated : " . implode(',', $this->tableau));
        }
    }

    /**
     * @When I delete the time period
     */
    public function iDeleteTheTimePeriod()
    {
        $this->currentPage = new TimeperiodConfigurationListingPage($this);
        $object = $this->currentPage->getEntry($this->initialProperties['name']);
        $checkbox = $this->assertFind('css', 'input[type="checkbox"][name="select[' . $object['id'] . ']"]');
        $this->currentPage->checkCheckbox($checkbox);
        $this->setConfirmBox(true);
        $this->selectInList('select[name="o1"]', 'Delete');
    }

    /**
     * @Then the time period disappears from the time periods list
     */
    public function theTimePeriodDisappearsFromTheTimePeriodsList()
    {
        $this->spin(
            function ($context) {
                $this->currentPage = new TimeperiodConfigurationListingPage($this);
                $object = $this->currentPage->getEntries();
                $bool = true;
                foreach ($object as $value) {
                    $bool = $bool && $value['name'] != $this->initialProperties['name'];
                }
                return $bool;
            },
            "The contact group was not deleted.",
            30
        );
    }
}
