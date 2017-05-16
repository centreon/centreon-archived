=====
Traps
=====

Overview
--------

Object name: **TRAP**

Show
----

In order to list available traps, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TRAP -a show
  id;name;oid;manufacturer
  576;alertSystemUp;.1.3.6.1.4.1.674.10892.1.0.1001;Dell
  577;alertThermalShutdown;.1.3.6.1.4.1.674.10892.1.0.1004;Dell
  578;alertTemperatureProbeNormal;.1.3.6.1.4.1.674.10892.1.0.1052;Dell
  599;alertFanEnclosureInsertion;.1.3.6.1.4.1.674.10892.1.0.1452;Dell
  600;alertFanEnclosureRemoval;.1.3.6.1.4.1.674.10892.1.0.1453;Dell
  601;alertFanEnclosureExtendedRemoval;.1.3.6.1.4.1.674.10892.1.0.1454;Dell
  602;alertLogNormal;.1.3.6.1.4.1.674.10892.1.0.1552;Dell
  605;ccmCLIRunningConfigChanged;.1.3.6.1.4.1.9.9.43.2.0.2;Cisco
  [...]


Add
---

In order to add a trap, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TRAP -a add -v "aNewTrap;.1.3.6.1.4.1.11.2.3.9.7.1.0.30" 

Required fields are:

======= ======================
Order	Description
======= ======================
1	Trap name

2	OID of the SNMP Trap
======= ======================


Del
---

If you want to remove a Trap, use the **DEL** action. The Name is used for identifying the Trap to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TRAP -a del -v "aNewTrap" 


Setparam
--------

If you want to change a specific parameter of a Trap, use the **SETPARAM** command. The Name is used for identifying the Trap to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TRAP -a setparam -v "aNewTrap;vendor;3com" 

Arguments are composed of the following columns:

======== =======================
Order	 Column description
======== =======================
1	 Name of Trap

2	 Parameter name

3	 Parameter value
======== =======================

Parameters that you may change are:

========================== ===================================================== =============================================================
Column	                   Description	                                         Possible values
========================== ===================================================== =============================================================
name	                   Name	

comments	           Comments	

output	                   Output	

oid	                   OID	

status	                   Status	                                         *ok*, *warning*, *critical*, *unknown* or *0*, *1*, *2*, *3*

vendor	                   Vendor name	                                         A valid vendor name

matching_mode	           Advanced regexp matching mode	                 *1* to enable, *0* to disable

reschedule_svc_enable	   Whether or not will reschedule service check 
                           when trap is received	                         *1* to enable, *0* to disable

execution_command	   Command to be executed when trap is received	         A valid Unix command line

execution_command_enable   Whether or not will execute the 'execution_command'	 *1* to enable, *0* to disable

submit_result_enable	   Whether or not will submit result to Service	         *1* to enable, *0* to disable
========================== ===================================================== =============================================================


Getmatching
-----------

In order to display the list of matching rules defined for a specific trap, use the **GETMATCHING** command::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TRAP -a getmatching -v "aNewTrap" 
  id;string;regexp;status;order
  8;@OUTPUT@;/test/;UNKNOWN;1

======== ======================================
Column	 Description
======== ======================================
ID	 ID of the matching rule

String	 String to match

Regexp	 Matching Regular Expression

Status	 Status to submit

Order	 Priority order of the matching rule
======== ======================================


Addmatching
-----------

In order to add a matching rule, use the **ADDMATCHING** command::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TRAP -a addmatching -v "aNewTrap;@OUTPUT@;/test2/;critical" 

Required fields are:

======= ================================= =============================================================
Order	Description	                  Possible values
======= ================================= =============================================================
1	Trap name	

2	String to match	

3	Matching Regular Expression	

4       Status to submit	          *ok*, *warning*, *critical*, *unknown* or *0*, *1*, *2*, *3*
======= ================================= =============================================================


Delmatching
-----------

In order to delete a matching rule, use the **DELMATCHING** command::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TRAP -a delmatching -v "8" 

Required fields are:

======= =========================
Column	Description
======= =========================
ID	ID of the matching rule
======= =========================


Updatematching
--------------

In order to delete a matching rule, use the **UPDATEMATCHING** command::

  [root@centreon ~]# ./centreon -u admin -p centreon -o TRAP -a updatematching -v "8;status;critical" 

Arguments are composed of the following columns:

======= ===========================
Order	Column description
======= ===========================
1	 ID of the matching rule

2	 Parameter name

3	 Parameter value
======= ===========================

Parameters that you may change are:

======== =============================== =================================
Column	 Description	                 Possible values
======== =============================== =================================
string	 String to match	

order	 Priority order	

status	 Status to submit	         *ok*, *warning*, *critical*, *unknown* or *0*, *1*, *2*, *3*

regexp	 Matching Regular Expression	
======== =============================== =================================
