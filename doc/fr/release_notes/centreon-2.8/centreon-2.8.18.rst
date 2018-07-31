###################
Centreon Web 2.8.18
###################

Enhancements
============

* [Administration] Add more actions and logging for ACL management  - PR #5841
* [API] Validate input parameters - PR #5958
* [API] Check illegal char in add function for CLAPI - PR #5948
* [API] Improve error message - PR #5972
* [API] Get multiple parameters for host - PR #5946
* [Configuration] Add form to configure Centreon Broker generic stream connectors - PR #6024 #6053 #6052 #6042 (beta)
* [Documentation] Add new chapter for Centreon ISO el7 installation - PR #6019
* [Documentation] Describe get parameters for hosts #5783 - PR #5924 
* [Knowledge-Base] Add option to disable SSL certificate - PR #6027

Bug Fixes
=========

* [Administration] Define default value for Broker - #6029 PR #6033
* [Configuration] Change low limit of EventMaxQueueSize for Centreon Broker configuration - PR #6013
* [Configuration] Avoid php notice when poller has no timezone - PR #6031
* [Install] Compatibility with PHP version 5.3 - PR #5976
* [Meta-service] Do not duplicate them on update - PR #5982
* [Meta-service] Possibility for user with ACL to display chart - PR #5952
* [Monitoring] Top Counter with ACL really slow - #5974 PR #5992
* [Monitoring] Centreon UI freezes when access to "View contact Notification" - #5760 PR #5954
* [Monitoring] Replace dot character in command line for better display - PR #5945
* [Monitoring] Fix add downtime on hostgroup or poller with ACL - PR #6023 
