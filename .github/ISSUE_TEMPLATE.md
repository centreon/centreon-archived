<!--

Centreon's Code of Conduct must be respected when opening any issue. (https://github.com/centreon/centreon/blob/master/CODE_OF_CONDUCT.md)

If you want to ask a question feel free to use on of those ressources
slack: https://centreon.github.io/register-slack

If you are reporting a new issue, make sure that we do not have any duplicates already open. You 
can ensure this by searching the issue list for this repository. If there is a duplicate, please 
close your issue and add a comment linking to the existing issue instead.

If you think that your problem is a bug, please add a description organized like the BUG REPORT 
INFORMATION shown below. If you can't provide all this information, it's possible that we will not 
be able to debug and fix your problem, and so we will be forced to close the issue. Nevertheless,
you will be able to provide more information later in order to re-open the issue.

When we need more information, we will reply in order to request it. If you do not answer
in the next 30 days, the ticket will be automaticaly closed.

Please describe your issue in English.
-->

# BUG REPORT INFORMATION

### Prerequisites

> The opened issue, must be code related. GitHub is not meant for support. Feel free to check the CONTRIBUTING section for more details.

***Versions***

For the RPM based systems

-- Copy/Paste the result of the following command --
```
$ rpm -qa | grep centreon
```

For the deb based systems

-- Copy/Paste the result of the following command --
```
$ dpkg -l | grep centreon
```
***Operating System***

*CentOS, Debian ...*

***Browser used***

- [ ] Google Chrome
- [ ] Firefox
- [ ] Internet Explorer IE11
- [ ] Safari

Version: --

***Additional environment details (AWS, VirtualBox, physical, etc.):***

### Description

-- Describe the encountered issue --

### Steps to Reproduce

Please describe precisely the steps to reproduce the encountered issue.

1. I logged in Centreon
2. I reached the Custom View
3. And so on...

### Describe the received result

### Describe the expected result

### Logs

**PHP error logs**

```
tail -f /var/opt/rh/rh-php71/log/php-fpm/centreon-error.log
```
**centreon-engine logs (*if needed*)**

```
tail -f /var/log/centreon-engine/centengine.log
```
**centreon-broker logs (*if needed*)**

```
tail -f /var/log/centreon-broker/central-broker-master.log
```
**centcore logs (*if needed*)**

```
tail -f /var/log/centreon/centcore.log
```

### Additional relevant information (e.g. frequency, ...)
