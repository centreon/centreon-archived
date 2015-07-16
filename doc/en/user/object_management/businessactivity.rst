Business activity
=================

Overview
--------

Object name: **centreon-bam:BusinessActivity**

Available parameters are the following:

======================= ================================
Parameter                             Description
======================= ================================
**--name**              Business activity name

**--ba-type-id**        Business activity type id

**--level-w**           Warning threshold

**--level-c**           Critical threshold

**--activate**          Enable (0 or 1)

--icon-id               Icon id

--id-reporting-period   Reporting period id
======================= ================================

List
----

In order to list business activities, use **list** action::

  ./centreonConsole centreon-bam:BusinessActivity:list
  id;name;slug;description;level_w;level_c
  1;BA sur les ping des machines des PP;ba-sur-les-ping-des-machines-des-pp;;70;50
  2;BA sur les memory des machines des PP;ba-sur-les-memory-des-machines-des-pp;;70;50
  3;BA ping + memory PP;ba-ping-memory-pp;;70;50

Columns are the following:

============= ==============================
Column        Description
============= ==============================
id            Business activity id

name          Business activity name

slug          Business activity slug

type          Business activity type

description   Business activity description

level_w       Warning threshold

level_c       Critical threshold
============= ==============================

Show
----

In order to show a business activity, use **show** action::

  ./centreonConsole centreon-bam:BusinessActivity:show --ba='ba-sur-les-ping-des-machines-des-pp'
  id: 1
  name: BA sur les ping des machines des PP
  slug: ba-sur-les-ping-des-machines-des-pp
  description: 
  level-w: 70
  level-c: 50
  sla_month_percent_warn: 
  sla_month_percent_crit: 
  sla_month_duration_warn: 
  sla_month_duration_crit: 
  id-reporting-period: 
  max_check_attempts: 
  normal_check_interval: 
  retry_check_interval: 
  current_level: 100
  calculate: 0
  downtime: 0
  acknowledged: 0
  must_be_rebuild: 0
  last_state_change: 1436795692
  current_status: 0
  in_downtime: 0
  dependency_dep_id: 
  graph_id: 
  icon-id: 
  graph_style: 
  disable: 1
  comment: 
  organization_id: 1
  type: 1


Create
------

In order to create a business activity, use **create** action::

  ./centreonConsole centreon-bam:BusinessActivity:create --name=ba1 --ba-type-id=application --level-w=90 --level-c=80
  ba1
  Object successfully created


Update
------

In order to update a business activity, use **update** action::

  ./centreonConsole centreon-bam:BusinessActivity:update --ba=ba1 --name=ba2
  Object successfully updated

Delete
------

In order to delete a business activity, use **delete** action::

  ./centreonConsole centreon-bam:BusinessActivity:delete --ba=ba2
  Object successfully deleted

Duplicate (Not yet implemented)
-------------------------------

In order to duplicate a business activity, use **duplicate** action::

  ./centreonConsole centreon-bam:BusinessActivity:duplicate --businessactivity=ba1
  Object successfully duplicated

List tag
--------

In order to list tags of a business activity, use **listTag** action::

  ./centreonConsole centreon-bam:BusinessActivity:listTag --ba=ba1
  tag-ba-1

Add tag
-------

In order to add a tag to a business activity, use **addTag** action::

  ./centreonConsole centreon-bam:BusinessActivity:addTag --ba=ba1 --tag=tag-ba-1
  The tag has been successfully added to the object

Remove tag
----------

In order to remove a tag from a business activity, use **removeTag** action::

  ./centreonConsole centreon-bam:BusinessActivity:removeTag --ba=ba1 --tag=tag-ba-1
  The tag has been successfully removed from the object

