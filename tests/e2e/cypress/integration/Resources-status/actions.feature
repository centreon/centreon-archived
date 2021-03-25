Feature: Actions on Resources
  As a user
  I want to apply actions on the Resources
  So that my IT infrastructure stays sane

  Background:
    Given a valid centreon user account
    And I can access this page
    And there are available resources

  Scenario: I can acknowledge a problematic Resource
    When I select the acknowledge action on a problematic Resource
    Then The problematic Resource is displayed as acknowledged

  Scenario: I can set a downtime on a problematic Resource
    When I select the downtime action on a problematic Resource
    Then The problematic Resource is displayed as in downtime
