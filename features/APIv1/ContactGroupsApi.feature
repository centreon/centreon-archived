#features/ContactGroupsApi.feature
@api
Feature: Check health of the ContactGroup APIs
  As an authorized user via the token
  I need to ensure my API handles proper actions and returns proper results

  Background:
    Given a Centreon server
    And I have a running instance of Centreon API

  @contactGroup
  Scenario: Healthcheck of ContactGroups APIs
    # List
    When I make a GET request to "/api/index.php?object=centreon_contact_groups&action=list"
    Then the response code should be 200
    And the response has a "result" property
    And the response has a "status" property
    And the property "result" has value
    """
    {
        "pagination":{
            "total":2,
            "offset":0,
            "limit":2
        },
        "entities":[
            {"id":3,"name":"Guest","activate":1},
            {"id":5,"name":"Supervisors","activate":1}
        ]
    }
    """