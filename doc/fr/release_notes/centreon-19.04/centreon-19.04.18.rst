#####################
Centreon Web 19.04.18
#####################

Bug fixes
---------
* [LDAP] legacy errors in the logs
* [Monitoring] Service limit when sending an external command

Security fixes
--------------
* [Security] Apply missing commit
* [Security] Authenticated Remote Code Execution in Centreon (post command execution) - CVE-2019-19699
* [Security] Missing access control mechanism in hostSendCommand / serviceSendCommand
* [Security] Missing access control mechanism in widget action
* [Security] Missing access control mechanism in widget preferencies
* [Security] Privilege Escalation from backup crontab
* [Security] Multiples SQL injection vulnerabilities in "Configuration > Knowledge Base"
* [Security] SQL injection vulnerability in "Administration > Parameters > Data"
* [Security] SQL injection vulnerability in loadServiceFromHost
* [Security] SQL injection vulnerability in centreonTraps class
* [Security] XSS in setHistory.php and commonJS.php
