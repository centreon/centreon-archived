Feature:
  In order to check the host categories
  As a logged in user
  I want to find host categories using api

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Host categories listing
    Given I am logged in
    And the following CLAPI import data:
    """
    HC;ADD;host-cat1;host-cat1-alias
    HC;setparam;host-cat1;hc_comment;blabla bla
    HC;setparam;host-cat1;hc_activate;1
    """

    When I send a GET request to '/api/v21.10/configuration/hosts/categories'
    Then the response code should be "200"
    And the JSON should be equal to:
    """
    {
        "result": [
            {
                "id": 1,
                "name": "host-cat1",
                "alias": "host-cat1-alias",
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