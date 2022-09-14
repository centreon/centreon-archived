Feature:
  In order to get categories from realtime
  As a logged in user
  I want to find categories using api

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

    Scenario: Host categories listing
    Given I am logged in
    And the following CLAPI import data:
    """
    HOST;ADD;test;Test host;127.0.0.1;generic-host;central;
    HC;ADD;host-cat1;host-cat1-alias
    HC;setparam;host-cat1;hc_activate;1
    HC;setmember;host-cat1;test
    """
    And the configuration is generated and exported
    And I wait until host "test" is monitored

    When I send a GET request to '/api/v22.10/monitoring/hosts/categories'
    Then the response code should be "200"
    And the JSON should be equal to:
    """
    {
        "result": [
            {
                "id": 1,
                "name": "host-cat1"
            }
        ],
        "meta": {
            "page": 1,
            "limit": 10,
            "search": {},
            "sort_by": {},
            "total": 1
        }
    }
    """