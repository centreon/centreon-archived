Feature: REST API
  As a Centreon user
  I want to use the REST API
  So that I can control my Centreon from a remote location

  @critical
  Scenario Outline: Usage
    Given a Centreon server with REST API testing data
    When the REST API "<api>" is called
    Then it replies as per specifications

    Examples:
      | api                                           |
      | config.command.add-setparam-show              |
      | config.host.add-setparam-show                 |
      | config.poller.add-setparam-show               |
      | config.poller.gethosts                        |
      | config.timeperiod.add-setparam-show           |
      | config.timeperiod.exclusion                   |
