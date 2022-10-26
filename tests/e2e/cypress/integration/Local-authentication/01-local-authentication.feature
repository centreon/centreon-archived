Feature: Local authentication
    As a user
    I want to be able to manage password security policies on a Centreon platform for users going through local authentication
    So that platform administrators can rely on better password practices and and increased security
    
Scenario: Default password policy
    Given an administrator deploying a new Centreon platform
    When the administrator first open authentication configuration menu
    Then a default password policy and default excluded users must be preset
    
Scenario: Enforcing a password case policy
    Given an administrator configuring a Centreon platform and an existing user account
    When the administrator sets a valid password length and a sets all the letter cases
    Then the existing user can not define a password that does not match the password case policy defined by the administrator and is notified about it
    
Scenario: Enforcing a password expiration policy
Scenario: Enforcing a password blocking policy
Scenario: Enforcing