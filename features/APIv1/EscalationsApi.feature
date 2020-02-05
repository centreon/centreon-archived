#features/EscalationsApi.feature
@api @ui
Feature: Check health of the Escalation APIs
  As an authorized user via the token
  I need to ensure my API handles proper actions and returns proper results

  Background:
    Given I am logged in a Centreon server
    And I have a running instance of Centreon API

  @escalation
  Scenario: Healthcheck of Escalation APIs
    # Add a escalation via web UI
    Then use the page object "\Centreon\Test\Behat\Configuration\EscalationConfigurationPage" and set the properties below
        | name          | first_notification | last_notification  | notification_interval | contactgroups |
        | Escalation 01 | 5                  | 15                 | 8                     | Supervisors   |
    # List
    When I make a GET request to "/api/index.php?object=centreon_escalation&action=list"
    Then the response code should be 200
    And the response has a "result" property
    And the response has a "status" property
    And the property "result" has value
    """
    {
        "pagination":{
            "total":1,
            "offset":0,
            "limit":1
        },
        "entities":[
            {"id":1,"name":"Escalation 01"}
        ]}
    """