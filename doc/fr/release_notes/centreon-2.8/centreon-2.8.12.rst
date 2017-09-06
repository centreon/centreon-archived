###################
Centreon Web 2.8.12
###################

Enhancements
============

* [API] Update documentation to remove non available functions
* [API] Export/Import LDAP configuration
* [API] Export/Import ACL Groups
* [API] Export/Import ACL Menus
* [API] Export/Import ACL Actions
* [API] Export/Import ACL Ressources
* [API] Replacing contact_name by contact_alias PR #5546
* [Configuration] Input text not aligned in Curves page #5534 PR #5553
* [Monitoring] Monitoring Services by Hostgroup : improvement order suggestion #5402 PR #5552
* [Monitoring] Increase perfs on EventLogs for non admin user PR #5480
* [Knowledge Base] Display API errors #5502
* [Knowledge Base] Refresh page after deletion #5503
* [Backup] Get correct datadir with CentOS7/MariaDB PR #5484

Bugfix
======

* [ACL] Bug on Access Groups #5189
* [ACL] The ACL of a contact and of a contact group is deleted during duplication #5497
* [API] CLAPI Import not working #5541
* [API] CLAPI export with select filter give PHP Warning and non result #5548
* [API] Missing functions setseverity and unsetseverity for services by hostgroup #5262
* [API] Problem with icon_image and map_icon_image of Hostgroup #5292
* [API] Missing function setservice for Service categories #5304
* [API] Problem with setting gmt in API #5291
* [API] Contact group additive inheritance isn't implemented #5311
* [API] Contact additive inheritance isn't implemented #5310
* [API] Problem with delmacro for services by hostgroup #5309
* [API] Several bugs on HG / CG when export is filtered #5297 PR #5297
* [Monitoring] Sorting by duration and Maximum page size change #5287 #5410 PR #5517
* [Configuration] Dependent host deleted during a service dependency duplication #5531
* [Configuration] All pollers had "config changed" #5549
* [Configuration] Unable to change the severity of an host template #5472
* [Configuration] Unable to change the severity of a service template #5559
* [Configuration] Meta service - unable to change the geo_coordinates #5493 PR #5505
* [Configuration] Meta service - unable to add more than one contact #5506 PR #5507
* [Configuration] Meta service - Implied contact is deleted during duplication #5495 PR #5508
* [Configuration] Problem with escalation's name during a duplication #5512 PR #5513
* [Configuration] Duplicate severity should remove link to objects #5478 PR #5509
* [Configuration] Fix search in trap select2
* [Configuration] Fix search in service template select2
