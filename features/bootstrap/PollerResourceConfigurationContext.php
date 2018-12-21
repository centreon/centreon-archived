<?php
/**
 * Copyright 2005-2018 Centreon
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\Configuration\PollerResourceConfigurationPage;
use Centreon\Test\Behat\Configuration\PollerResourceConfigurationListingPage;
use Centreon\Test\Behat\Exception\ClosureException;

class PollerResourceConfigurationContext extends CentreonContext
{

    const POLLER_RESOURCE_NAME = 'pollername';

    /**
     * @var array Data used to create a new poller resource
     */
    protected $pollerResourceProperties = array(
        'resource_name' => '<button>%NAME%</button>',
        'resource_line' => '<button>macro</button>',
        'instance_id' => 'Central',
        'resource_activate' => '1',
        'resource_comment' => '<button>comments</button>'
    );


    public function __construct(array $parameters = array())
    {
        parent::__construct($parameters);
        $this->pollerResourceProperties['resource_name'] =
            str_replace(
                '%NAME%',
                self::POLLER_RESOURCE_NAME,
                $this->pollerResourceProperties['resource_name']
            );
    }

    /**
     * @When I add a poller resource
     */
    public function iAddAPollerResource()
    {
        $currentPage = new PollerResourceConfigurationPage($this);
        $currentPage->setProperties($this->pollerResourceProperties);
        $currentPage->save();
    }

    /**
     * @Then The html is not interpreted on the pollers resources list page
     */
    public function theHtmlIsNotInterpretedOnThePollersResourcesListPage()
    {
        $currentPage = new PollerResourceConfigurationListingPage($this);
        $this->spin(
            function ($context) use ($currentPage) {
                $pollersResources = $currentPage->getEntries();
                if (!empty($pollersResources)) {
                    foreach ($pollersResources as $pollerResourceName => $pollerResource) {
                        if (strpos($pollerResourceName, self::POLLER_RESOURCE_NAME) !== false) {
                            if ($pollerResource['resource_name'] !== $this->pollerResourceProperties['resource_name']) {
                                throw new ClosureException('XSS vulnerability detected on poller resource name');
                            }
                            if ($pollerResource['resource_line'] !== $this->pollerResourceProperties['resource_line']) {
                                throw new ClosureException('XSS vulnerability detected on macro');
                            }
                            if ($pollerResource['resource_comment'] !== $this->pollerResourceProperties['resource_comment']) {
                                throw new ClosureException('XSS vulnerability detected on comment');
                            }
                            return true;
                        }
                    }
                }
                return false;
            }
        );
    }
}
