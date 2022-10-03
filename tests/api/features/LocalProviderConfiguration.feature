Feature: Local Provider Configuration API
  In order to use Local Provider Configuration API
  As a logged user
  I need to be able to update and retreive Local Provider Configuration information

  Background:
    Given a running instance of Centreon Web API
    And the endpoints are described in Centreon Web API documentation

  Scenario: Update and retrieve Local Provider Configuration information
    Given I am logged in

    # Valid PUT request
    When I send a PUT request to '/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 13,
          "has_uppercase": true,
          "has_lowercase": true,
          "has_number": true,
          "has_special_character": false,
          "attempts": 9,
          "blocking_duration": 1200,
          "password_expiration": {
            "expiration_delay": 15552000,
            "excluded_users": [
              "admin"
            ]
          },
          "can_reuse_passwords": true,
          "delay_before_new_password": 3600
        }
      }
    """
    Then the response code should be "204"

    # Valid GET request
    When I send a GET request to '/administration/authentication/providers/local'
    Then the response code should be "200"
    And the JSON should be equal to:
    """
      {
        "password_security_policy": {
          "password_min_length": 13,
          "has_uppercase": true,
          "has_lowercase": true,
          "has_number": true,
          "has_special_character": true,
          "attempts": 9,
          "blocking_duration": 1200,
          "password_expiration": {
            "expiration_delay": 0,
            "excluded_users": [
              "admin"
            ]
          },
          "can_reuse_passwords": true,
          "delay_before_new_password": 3600
        }
      }
    """

    # Invalid PUT request: missing required property
    When I send a PUT request to '/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 6,
          "has_lowercase": true,
          "has_number": true,
          "has_special_character": false,
          "attempts": 9,
          "blocking_duration": 1200,
          "password_expiration": {
            "expiration_delay": 15552000,
            "excluded_users": [
              "admin"
            ]
          },
          "can_reuse_passwords": true,
          "delay_before_new_password": 3600
        }
      }
    """
    Then the response code should be "500"
    And the JSON should be equal to:
    """
    {
      "code": 500,
      "message": "[password_security_policy.has_uppercase] The property has_uppercase is required\n"
    }
    """

    # Invalid PUT request: password_min_length is lower than minimum allowed value
    When I send a PUT request to '/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 6,
          "has_uppercase": true,
          "has_lowercase": true,
          "has_number": true,
          "has_special_character": false,
          "attempts": 9,
          "blocking_duration": 1200,
          "password_expiration": {
            "expiration_delay": 15552000,
            "excluded_users": [
              "admin"
            ]
          },
          "can_reuse_passwords": true,
          "delay_before_new_password": 3600
        }
      }
    """
    Then the response code should be "500"
    And the JSON should be equal to:
    """
      {
        "code": 500,
        "message": "[password_security_policy.password_min_length] Must have a minimum value of 8\n"
      }
    """

    # Invalid PUT request: password_min_length is greater than maximum allowed value
    When I send a PUT request to '/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 130,
          "has_uppercase": true,
          "has_lowercase": true,
          "has_number": true,
          "has_special_character": false,
          "attempts": 9,
          "blocking_duration": 1200,
          "password_expiration": {
            "expiration_delay": 15552000,
            "excluded_users": [
              "admin"
            ]
          },
          "can_reuse_passwords": true,
          "delay_before_new_password": 3600
        }
      }
    """
    Then the response code should be "500"
    And the JSON should be equal to:
    """
      {
        "code": 500,
        "message": "[password_security_policy.password_min_length] Must have a maximum value of 128\n"
      }
    """

    # Invalid PUT request: attemps is lower than allowed value
    When I send PUT request to '/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 130,
          "has_uppercase": true,
          "has_lowercase": true,
          "has_number": true,
          "has_special_character": false,
          "attempts": 0,
          "blocking_duration": 1200,
          "password_expiration": {
            "expiration_delay": 15552000,
            "excluded_users": [
              "admin"
            ]
          },
          "can_reuse_passwords": true,
          "delay_before_new_password": 3600
        }
      }
    """
    Then the response code should be "500"
    And the JSON should be equal to:
    """
      {
        "code": 500,
          "message": "[password_security_policy.attempts] Must have a minimum value of 1\n"
      }
    """

    # Invalid PUT request: attemps is greater than allowed value
    When I send PUT request to '/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 130,
          "has_uppercase": true,
          "has_lowercase": true,
          "has_number": true,
          "has_special_character": false,
          "attempts": 11,
          "blocking_duration": 1200,
          "password_expiration": {
            "expiration_delay": 15552000,
            "excluded_users": [
              "admin"
            ]
          },
          "can_reuse_passwords": true,
          "delay_before_new_password": 3600
        }
      }
    """
    Then the response code should be "500"
    And the JSON should be equal to:
    """
      {
        "code": 500,
        "message": "[password_security_policy.attempts] Must have a maximum value of 10\n"
      }
    """

    # Invalid PUT request: blocking duration exceeds the allowed value
    When I send PUT request to '/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 130,
          "has_uppercase": true,
          "has_lowercase": true,
          "has_number": true,
          "has_special_character": false,
          "attempts": 11,
          "blocking_duration": 604801,
          "password_expiration": {
            "expiration_delay": 15552000,
            "excluded_users": [
              "admin"
            ]
          },
          "can_reuse_passwords": true,
          "delay_before_new_password": 3600
        }
      }
    """
    Then the response code should be "500"
    And the JSON should be equal to:
    """
      {
        "code": 500,
        "message": "[password_security_policy.blocking_duration] Must have a maximum value of 604800\n"
      }
    """

    # Invalid PUT request: expiration_delay is lower than the allowed value
    When I send PUT request to '/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 130,
          "has_uppercase": true,
          "has_lowercase": true,
          "has_number": true,
          "has_special_character": false,
          "attempts": 11,
          "blocking_duration": 604800,
          "password_expiration": {
            "expiration_delay": 604799,
            "excluded_users": [
              "admin"
            ]
          },
          "can_reuse_passwords": true,
          "delay_before_new_password": 3600
        }
      }
    """
    Then the response code should be "500"
    And the JSON should be equal to:
    """
      {
        "code": 500,
        "message": "[password_security_policy.password_expiration.expiration_delay] Must have a minimum value of 604800\n"
      }
    """