# Tests End-to-End with Cypress.io

## Best Practices

Cypress : https://docs.cypress.io/guides/references/best-practices.html

## Require

- docker + docker-compose
- npm >= 5.2 (to use npx included)

## Infos

Docker-compose is configured to expose HTTP Port (80) to the **localhost:3400**

Cypress is configured for tests to be performed on the basis of the exposed port of the dockers: 3400

Plugins added in the environment

- cypress-cucumber-preprocessor

This allows to manage scenarios written in .feature format (Gerkhin syntax).

> https://github.com/TheBrainFamily/cypress-cucumber-preprocessor

## Step by Step

### Create the Centreon Docker testing environment

> cypress:build:docker

optional parameters :

1. Version centreon [default = the last of master]
2. Image docker name [default = ‘mon-web’]
3. distribution [default = centos7]

### Start and Stop dockers testing environment

> cypress:env:start or cypress:env:stop

These 2 commands allow to launch the scripts to start or stop the Dockers built by the previous command

## Open the Cypress dashboard or Run by CLI

> cypress:open

This command allows to open a Cypress UI to manipulate and execute the tests manually.

> cypress:run

This command allows to execute the tests automatically with several possible parameters.

Here the documentation: https://docs.cypress.io/guides/guides/command-line.html#Commands
