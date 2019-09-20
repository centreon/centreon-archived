# Contributing to Centreon project

:clap: First thing first... Thank you for taking the time to contribute to Centreon project ! :clap: Much appreciated ! :metal:

This article contains guidelines for contributing to [Centreon](https://github.com/centreon/) project.

### Table of contents

[I have a question](#i-have-a-question-)

[How can I contribute?](#-how-can-i-contribute-)
- [Where should I report ?](#-where-shoud-i-report-)
- [I have an issue to report](#-i-have-an-issue-to-report-)
- [I have a suggestion of enhancement](#-i-have-a-suggestion-of-enhancement-)
- [I have a pull request to submit](#-i-have-a-pull-request-to-submit-)

[Centreon style guides](#-centreon-style-guides-)
- [Formating commit messages](#-formating-commit-messages-)
- [Coding style](#-coding-style-)

## Code of Conduct

Any people that wants to contribute and participate in developping the project must respect [Centreon Code of Conduct](CODE_OF_CONDUCT.md). Please report any unacceptable behavior to [community@centreon.com](mailto:community@centreon.com).

## I have a question

> **Advise**: Centreon GitHub is meant for opening issues (code related), feature requests and so on. It is not **meant** for **support**. Please refer to the following available ressources, you'll get an answer from a Centreon team or community member.


- [Official Centreon Slack](https://centreon.github.io/register-slack)

## How can I contribute

Centreon community can contribute in **many ways** to the project.

### Where shoud I report ?

Issues and feature requests should be done on the **appropriate repositories**. Here are the repositories maintened by Centreon:

| Modules
| -------
| [centreon-broker](https://github.com/centreon/centreon-broker)
| [centreon-engine](https://github.com/centreon/centreon-engine)
| [centreon-clib](https://github.com/centreon/centreon-clib)
| [centreon-connectors](https://github.com/centreon/centreon-connectors)
| [centreon-plugins](https://github.com/centreon/centreon-plugins)
| [centreon-dsm](https://github.com/centreon/centreon-dsm)
| [centreon-vmware](https://github.com/centreon/centreon-vmware)

| Widgets
| -------------
| [centreon-widget-host-monitoring](https://github.com/centreon/centreon-widget-host-monitoring)
| [centreon-widget-service-monitoring](https://github.com/centreon/centreon-widget-service-monitoring)
| [centreon-widget-hostgroup-monitoring](https://github.com/centreon/centreon-widget-hostgroup-monitoring)
| [centreon-widget-servicegroup-monitoring](https://github.com/centreon/centreon-widget-servicegroup-monitoring)
| [centreon-widget-graph-monitoring](https://github.com/centreon/centreon-widget-graph-monitoring)
| [centreon-widget-httploader](https://github.com/centreon/centreon-widget-httploader)
| [centreon-widget-global-health](https://github.com/centreon/centreon-widget-global-health)
| [centreon-widget-broker-status](https://github.com/centreon/centreon-widget-broker-status)
| [centreon-widget-engine-status](https://github.com/centreon/centreon-widget-engine-status)


### I have an issue to report

Before reporting an issue please make sure that it has not been already reported by checking [Centreon Bug Tracker](https://github.com/centreon/centreon/issues)

If your issue has **not** been reported yet, then the issue should be opened using the following template [ISSUE_TEMPLATE.md](.github/ISSUE_TEMPLATE.md)

### I have a suggestion of enhancement

Any ideas, enhancements, feature requests are more than welcome. Feature requests should be opened by using the following template [FEATURE_REQUEST.md](.github/FEATURE_REQUEST.md)

### I have a pull request to submit

You have been working on Centreon base code and want to submit it to us. Well... Again you are more than welcome and thank you in advance ! :clap:

The pull request should respect the some requirements that can found in the following template [PULL_REQUEST_TEMPLATE.md](.github/PULL_REQUEST_TEMPLATE.md)

> **Notice**: Any pull request that does not respect those requirements will be legitimately rejected !

### Centreon style guides

#### Formating commit messages

The commit format should follow this commit template message
```
<type>(<scope>): <subject>

<body>

<footer>
```

The format explanation can be found [here](http://karma-runner.github.io/1.0/dev/git-commit-msg.html).

The ***body*** section needs to be clear, made of complete sentences introducing the purpose of the commit and the context, making code reviews much easier.

The ***footer*** should contain reference(s) to the ticket(s) related to the commit.
Applied to the sample ticket
```
Refs: #5567 (GitHub) or MON-2234 (Jira)
```
The ***type*** can refer to
- **feat**: adding a feature
- **fix**: adding a patch
- **enh**: adding an enhancement
- **docs**: adding documentation changes
- **style**: fixing coding style issues
- **refactor**: code refactoring
- **test**: adding new tests or fixing old ones
- **chore** : updating project construction files (Jenkins files, CMakefile, gulp, webpack ...)

The ***scope*** is defined by project. Scopes for Centreon Open Source project can be found [here](scopes/centreon.md).

#### Coding style

Centreon software is made of several languages. For each language a specific coding style **must be respected**.

Since the 18.10 version of Centreon REACT has been introduced and Centreon follows the [airbnb](https://github.com/airbnb/javascript/tree/master/react) react coding style.

For other languages, coding style rules are defined in Centreon GitHub repository
* [PHP](https://github.com/centreon/centreon/tree/master/doc/coding-style/php)
* [CSS](https://github.com/centreon/centreon/tree/master/doc/coding-style/css)
* [HTML](https://github.com/centreon/centreon/tree/master/doc/coding-style/html)
* [JavaScript](https://github.com/centreon/centreon/tree/master/doc/coding-style/js)

#### Documentation

If you want to visualize and suggest modification to the documentation through a pull request
you can check the **HOW TO** build the documentation section [here](doc/README.md)









