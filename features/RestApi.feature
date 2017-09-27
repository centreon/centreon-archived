Feature: REST API
  As an external developer
  I want REST API in Centreon Web
  So that I can develop software interfacing with Centreon Web

  @critical
  Scenario: REST API tests
    Given a Centreon server with REST API testing data
    When REST API are called
    Then they reply as per specifications
