Feature:
  In order to check the host severities
  As a logged in user
  I want to find host severities using api

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation (version: 2.1)

  Scenario: Host severities listing
    Given I am logged in
    And the following CLAPI import data:
    """
    HC;ADD;severity1;host-severity-alias
    HC;setparam;severity1;hc_comment;blabla bla
    HC;setparam;severity1;hc_activate;1
    HC;setseverity;severity1;42;logos/centreon.png
    """

    When I send a GET request to '/api/v2.1/configuration/hosts/severities'
    Then the response code should be "200"
    And the JSON should be equal to:
    """
    {
        "result": [
            {
                "id": 1,
                "name": "severity1",
                "alias": "host-severity-alias",
                "level": 42,
                "icon": {
                    "id": 1,
                    "name": "centreon",
                    "path": "img/media/logos/centreon.png",
                    "comment": "centreon logo"
                },
                "comments": "blabla bla",
                "is_activated": true
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
