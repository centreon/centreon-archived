Feature:
  In order to get severities from realtime
  As a logged in user
  I want to find severities using api

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

    Scenario: Host severities listing
    Given I am logged in
    And the following CLAPI import data:
    """
    HOST;ADD;test;Test host;127.0.0.1;generic-host;central;
    HC;ADD;severity1;severity1-alias
    HC;setparam;severity1;hc_activate;1
    HC;setmember;severity1;test
    HC;setseverity;severity1;42;logos/centreon.png
    """
    And the configuration is generated and exported
    And I wait until host "test" is monitored

    When I send a GET request to '/api/v22.10/monitoring/severities/host'
    Then the response code should be "200"
    And the JSON should be equal to:
    """
    {
        "result": [
            {
                "id": 1,
                "name": "severity1",
                "level": 42,
                "type": "host",
                "icon": {
                    "id": 1,
                    "name": "centreon",
                    "url": "logos/centreon.png"
                }
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

    Scenario: Service severities listing
    Given I am logged in
    And the following CLAPI import data:
    """
    HOST;ADD;test;Test host;127.0.0.1;generic-host;central;
    SERVICE;ADD;test;test_service1;Ping-LAN;
    SC;ADD;severity1;severity1-alias
    SC;setparam;severity1;sc_activate;1
    SC;addservicetemplate;severity1;Ping-LAN
    SC;setseverity;severity1;42;logos/centreon.png
    """
    And the configuration is generated and exported
    And I wait until host "test" is monitored
    And I wait until service "test_service1" from host "test" is monitored

    When I send a GET request to '/api/v22.10/monitoring/severities/service'
    Then the response code should be "200"
    And the JSON should be equal to:
    """
    {
        "result": [
            {
                "id": 5,
                "name": "severity1",
                "level": 42,
                "type": "service",
                "icon": {
                    "id": 1,
                    "name": "centreon",
                    "url": "logos/centreon.png"
                }
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