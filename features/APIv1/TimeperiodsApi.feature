#features/TimeperiodsApi.feature
@api
Feature: Check health of the Timeperiod APIs
  As an authorized user via the token
  I need to ensure my API handles proper actions and returns proper results

  Background:
    Given a Centreon server
    And I have a running instance of Centreon API

  @timeperiod
  Scenario: Healthcheck of Timeperiod APIs
    # List
    When I make a GET request to "/api/index.php?object=centreon_timeperiod&action=list"
    Then the response code should be 200
    And the response has a "result" property
    And the response has a "status" property
    And the property "result" has value
    """
    {
        "pagination":{
            "total":4,
            "offset":0,
            "limit":4
        },
        "entities":[
            {"id":1,"name":"24x7","alias":"Always"},
            {"id":2,"name":"none","alias":"Never"},
            {"id":3,"name":"nonworkhours","alias":"Non-Work Hours"},
            {"id":4,"name":"workhours","alias":"Work hours"}
        ]
    }
    """