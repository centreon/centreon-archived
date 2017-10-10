Feature: Downtime DST
  As a Centreon user
  I want to be certain that the downtimes work correctly with DST
  To release quality products

  Background:
    Given I am logged in a Centreon server located at "Europe/Paris"
    And a passive service is monitored


# summer changing time

  @critical
  Scenario: recurrent downtime starting on summer changing time
    Given a recurrent downtime starting on summer changing time
    When downtime is approaching
    Then the downtime is scheduled

  @critical
  Scenario: recurrent downtime ending on summer changing time
    Given a recurrent downtime ending on summer changing time
    When downtime is approaching
    Then the downtime is scheduled

  @critical
  Scenario: recurrent downtime starting and ending on summer changing time
    Given a recurrent downtime starting and ending on summer changing time
    When downtime is approaching
    Then the downtime is not scheduled

  @critical
  Scenario: recurrent downtime during all day on summer changing date
    Given a recurrent downtime during all day on summer changing date
    When downtime is approaching
    Then the downtime is scheduled

  @critical
  Scenario: recurrent downtime of next day of summer changing date
    Given a recurrent downtime during all day on summer changing date is scheduled
    And a recurrent downtime of next day of summer changing date
    When downtime is approaching
    Then the downtime is scheduled


# winter changing time

  @critical
  Scenario: recurrent downtime starting on winter changing time
    Given a recurrent downtime starting on winter changing time
    When downtime is approaching
    Then the downtime is scheduled

  @critical
  Scenario: recurrent downtime ending on winter changing time
    Given a recurrent downtime ending on winter changing time
    When downtime is approaching
    Then the downtime is scheduled

  @critical
  Scenario: recurrent downtime starting and ending on winter changing time
    Given a recurrent downtime starting and ending on winter changing time
    When downtime is approaching
    Then the downtime is scheduled

  @critical
  Scenario: recurrent downtime during all day on winter changing date
    Given a recurrent downtime during all day on winter changing date
    When downtime is approaching
    Then the downtime is scheduled

  @critical
  Scenario: recurrent downtime of next day of winter changing date
    Given a recurrent downtime during all day on winter changing date is scheduled
    And a recurrent downtime of next day of winter changing date
    When downtime is approaching
    Then the downtime is scheduled