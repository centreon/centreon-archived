Feature: Local Provider Configuration API
  In order to use Local Provider Configuration API
  As a logged user
  I need to be able to update and retreive Local Provider Configuration information

  Background:
    Given a running instance of Centreon Web API

  Scenario: Update and retrieve Local Provider Configuration information
    Given I am logged in
    And the endpoints are described in Centreon Web API documentation (version: 22.10)

    # Valid PUT request
    When I send a PUT request to '/api/latest/administration/authentication/providers/local' with body:
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
    When I send a GET request to '/api/latest/administration/authentication/providers/local'
    Then the response code should be "200"
    And the JSON should be equal to:
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

  Scenario: Update Local Provider Configuration information with invalid properties
    Given I am logged in

    # Invalid PUT request: missing required property
    When I send a PUT request to '/api/latest/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 8,
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
    When I send a PUT request to '/api/latest/administration/authentication/providers/local' with body:
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
    When I send a PUT request to '/api/latest/administration/authentication/providers/local' with body:
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
    When I send a PUT request to '/api/latest/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 12,
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
    When I send a PUT request to '/api/latest/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 13,
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
    When I send a PUT request to '/api/latest/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 13,
          "has_uppercase": true,
          "has_lowercase": true,
          "has_number": true,
          "has_special_character": false,
          "attempts": 8,
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
    When I send a PUT request to '/api/latest/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 13,
          "has_uppercase": true,
          "has_lowercase": true,
          "has_number": true,
          "has_special_character": false,
          "attempts": 8,
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

    # Invalid PUT request: expiration_delay is exceeds the allowed value
    When I send a PUT request to '/api/latest/administration/authentication/providers/local' with body:
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
            "expiration_delay": 31557601,
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
        "message": "[password_security_policy.password_expiration.expiration_delay] Must have a maximum value of 31536000\n"
      }
    """

    # Invalid PUT request: delay_before_new_password is lower than the allowed value
    When I send a PUT request to '/api/latest/administration/authentication/providers/local' with body:
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
          "delay_before_new_password": 3599
        }
      }
    """
    Then the response code should be "500"
    And the JSON should be equal to:
    """
      {
        "code": 500,
        "message": "[password_security_policy.delay_before_new_password] Must have a minimum value of 3600\n"
      }
    """

    # Invalid PUT request: delay_before_new_password exceeds the allowed value
    When I send a PUT request to '/api/latest/administration/authentication/providers/local' with body:
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
          "delay_before_new_password": 604801
        }
      }
    """
    Then the response code should be "500"
    And the JSON should be equal to:
    """
      {
        "code": 500,
        "message": "[password_security_policy.delay_before_new_password] Must have a maximum value of 604800\n"
      }
    """

  Scenario: Update and retrieve Local Provider Configuration information as logged non-admin user without Reach API rights
    Given the following CLAPI import data:
    """
      CONTACT;ADD;kev;kev;kev@localhost;Centreon@2022;0;1;en_US;local
    """
    And I am logged in with "kev"/"Centreon@2022"

    # Forbidden PUT request
    When I send a PUT request to '/api/latest/administration/authentication/providers/local' with body:
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
    Then the response code should be "403"

    # Forbidden GET request
    When I send a GET request to '/api/latest/administration/authentication/providers/local'
    Then the response code should be "403"

  Scenario: Update and retrieve Local Provider Configuration information as logged non-admin user with Reach API rights
    Given the following CLAPI import data:
    """
      CONTACT;ADD;kev;kev;kev@localhost;Centreon@2022;1;1;en_US;local
      CONTACT;setparam;kev;reach_api;1
    """
    And I am logged in with "kev"/"Centreon@2022"

    # Valid PUT request
    When I send a PUT request to '/api/latest/administration/authentication/providers/local' with body:
    """
      {
        "password_security_policy": {
          "password_min_length": 12,
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
    When I send a GET request to '/api/latest/administration/authentication/providers/local'
    Then the response code should be "200"
    And the JSON should be equal to:
    """
      {
        "password_security_policy": {
          "password_min_length": 12,
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
