====================
Centreon Web 19.04.4
====================

Enhancements
------------

* [Administration] Add the possibility to define the refresh frequency for LDAP settings for users (PR/#7627)
* [API] Update output of getparam command on host object (PR/#7678)
* [Configuration] Close tooltip when user clicks somewhere else (PR/#7729)

Bug fixes
---------

* [ACL] Add ACL to select meta-services for service performance (#6534, PR/#7736)
* [Backup] Change backup path of httpd24-httpd (PR/#7577)
* [Configuration/Administration] Fix filters save with pagination (PR/#7732)
* [Configuration] Fix meta service generation with special char (#7608, PR/#7705)
* [Configuration] Trap generation reindexing pollers id (#6205, PR/#6416)
* [Clapi] Delete services when host template is detached from host (#4371, PR/#7784)
* [Clapi] Fix import of contactgroup when linked to ldap (PR/#7797)
* [Centcore] Use correct ssh port (PR/#7677)
* [Graphs] Issue with export of splitted graphs fixed (PR/#7822)
* [Menu] translate properly menu entries
* [Monitoring] Fix pagination display in service monitoring (PR/#7755)
* [Remote-Server] Check bam installation on remote server is http only (#7626, PR/#7640)
* [Remote-Server] Fix enableremote parameters parsing and setting (PR/#7711)
* [System] Compatibility with MySQL v8
* [UI] Remove chrome password autocomplete in several form (#6283, PR/#7697)
* [UI] Custom view page is no longer broken with spanish language (PR/#7778)

Documentation
-------------

* Correct CLAPI Host parameters (PR/#7658)
* Correct SSH exchange notice (#7620, PR/#7639)

Technical
---------

* [Lib] update composer