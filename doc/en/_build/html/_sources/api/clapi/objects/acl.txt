====
ACL
====

Overview
--------

Object name: **ACL**

Reload
------

In order to reload ACL, use the **RELOAD** command::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACL -a reload 


Lastreload
----------

In order to check when the ACL was last reloaded, use the **LASTRELOAD** command::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACL -a lastreload
  1329833702

If you wish to get a human readable time format instead of a timestamp, use the following command::

  [root@centreon ~]# ./centreon -u admin -p centreon -o ACL -a lastreload -v "d-m-Y H:i:s" 
  21-02-2012 15:17:01

You can change the date format:

================ ===========
Format character Description
================ ===========
d                Day

m                Month

Y                Year

H                Hour

i                Minute

s                Second
================ ===========


