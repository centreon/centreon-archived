Feature: Rest add command
  As a Centreon user
  I want to add a command by the rest API
  So the new command is available

  @critical
  Scenario: Simple add command
    Given a Centreon server with REST API testing data
    When call REST API "add-command" with data "Test1" on "mon-web-api-test-3.4-wip:centos6"
    Then they reply as per specifications
