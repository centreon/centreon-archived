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

The pull request must comply with certain requirements which are set out in the following template
[PULL_REQUEST_TEMPLATE.md](.github/PULL_REQUEST_TEMPLATE.md)

> **Notice**: A pull request which contains more than the described modifications or contains more than one issue will
be rejected. Kindly write a detailed description and open one pull request per issue.

If the pull request matches the expected requirements, it will be added to a refinement session with the development team and the product owner.
If everything is clear, the pull request will be integrated to the development workflow and will be merged if it successfully passes our Continuous Integration's acceptance tests.
Afterwards, our Quality Assurance team will test it again to avoid any regressions before the pull request is released.

If the development team needs more details, they will contact you about the pull request. Please stay tuned.

> **Warning**: Any pull request that does not respect the requirements will ultimately be rejected ! In addition,
if you are asked to do so, you must help us understand your changes or behavior, and respond to us within 8 days.

> **Notice**: We used another open source project's contribution model as inspiration to provide better communication on your pull request's status :
Visual Studio Code.


Here are the labels and descriptions we may add to your work.

| Label                             | Reason |
| :-----                            | :------ |
| ```pr/external```                 | The first badge |
| ```status/accepted```             | The development team has approved your modifications |
| ```status/implemented```          | The PR has been added or may have already been released in next version |
| ```status/duplicate```            | Another PR is already open |
| ```status/invalid```              | The requirements are not met |
| ```status/in-backlog```           | The development team has groomed your proposition and will work on it soon |
| ```status/could-not-reproduce```  | The dev was unable to reproduce the use-case and may need more information |
| ```status/needs-attention```      | This PR is on hold. The reason is specified in the issue |
| ```status/needs-merge```          | A dev needs to merge your work |
| ```status/too-dangerous```        | The modification seems to impact too much features or may introduce side effects |
| ```status/multiple-issues```      | The modifications contain more than one issue in a single PR |
| ```status/out-of-scope```         | The modifications are out of the described scope |
| ```status/more-info-needed```     | A dev asked you for more details and is waiting for your reply |
| ```status/wont-fix```             | The modifications do not fix the described behavior |

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

The ***scope*** is defined by project. Scopes for Centreon Open Source project are related to the modifications.
For example: fix(***security***) or feat(***hostgroup***)

#### Coding style

Centreon software is made of several languages. For each language a specific coding style **must be respected**.

> **Notice**: For some languages, a bot may ask you to rework the formatting of your code. Until you make the
 modifications, we won't be able to add your work to the refinement loop.

Since the 18.10 version of Centreon REACT has been introduced and Centreon follows the [airbnb](https://github.com/airbnb/javascript/tree/master/react) react coding style.

For other languages, coding style rules are defined in Centreon GitHub repository
* [PHP](https://github.com/centreon/centreon/tree/master/doc/coding-style/php)
* [CSS](https://github.com/centreon/centreon/tree/master/doc/coding-style/css)
* [HTML](https://github.com/centreon/centreon/tree/master/doc/coding-style/html)
* [JavaScript](https://github.com/centreon/centreon/tree/master/doc/coding-style/js)

#### Documentation

If you want to visualize and suggest modification to the documentation through a pull request
you can check the **HOW TO** build the documentation section [here](doc/README.md)
