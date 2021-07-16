<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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
 *
 * For more information : contact@centreon.com
 *
 */

namespace Centreon\Test\Api\Context;

use Centreon\Test\Behat\Api\Context\ApiContext;

class UserFilterContext extends ApiContext
{
    /**
     * @var string
     */
    private $requestBody = '';

    /**
     * @Given I add a filter linked to hostgroup :hostgroupName
     */
    public function iAddAfilterLinkedToHostgroup(string $hostgroupName): void
    {
        $response = $this->iSendARequestTo(
            'GET',
            '/api/beta/monitoring/hostgroups'
        );
        $decodedResponse = json_decode($response->getBody()->__toString(), true);
        $hostgroupId = $decodedResponse['result'][0]['id'];

        $this->requestBody = '{
            "name":"my filter1",
            "criterias":[{
              "name": "host_groups",
              "type": "multi_select",
              "value": [
                {
                  "id": ' . $hostgroupId . ',
                  "name": "' . $hostgroupName . '"
                }
              ],
              "object_type": "host_groups"
            }]
        }';

        $this->iSendARequestToWithBody(
            'POST',
            '/api/beta/users/filters/events-view',
            $this->requestBody
        );
    }

    /**
     * @Given I update the filter with the creation values
     */
    public function iUpdateTheFilterWithTheCreationValues(): void
    {
        $this->iSendARequestToWithBody(
            'PUT',
            '/api/beta/users/filters/events-view/1',
            $this->requestBody
        );
    }
}
