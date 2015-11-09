=============
Import/Export
=============

Export
------
At some point, you might need to export all of the object configuration parameters into a plain text file, either for synchronizing or backuping purpose.
This export feature is ran like this::

  [root@centreon ~]# ./centreon -u admin -p centreon -e > /tmp/clapi-export.txt 

This will generate CLAPI commands and redirect them to the */tmp/clapi-export.txt* file.

This file can now be read by the import command.

With this, you can also build your own CLAPI command file if you know the straight forward syntax.

For instance:::

  HOST;ADD;Host-Test1;Test host;127.0.0.1;generic-host;Local Poller;Linux
  HOST;ADD;Host-Test2;Test host;127.0.0.1;generic-host;Local Poller;Linux
  HOST;ADD;Host-Test3;Test host;127.0.0.1;generic-host;Local Poller;Linux
  HOST;ADD;Host-Test4;Test host;127.0.0.1;generic-host;Local Poller;Linux
  HOST;ADD;Host-Test5;Test host;127.0.0.1;generic-host;Local Poller;Linux


Import
------
You can import configuration from the exported file */tmp/clapi-export* ::

  [root@centreon ~]# ./centreon -u admin -p centreon -i /tmp/clapi-export.txt

In case you have a very large export file, it is advised to redirect the output of the above command to a file.
Indeed, when errors occur during the import process, CLAPI will print out an error message along with the line number of the file, you might need to store those output message for troubleshooting later on.

You can build your own CLAPI command file if you know the straight forward syntax.
You can use parameter described in Object Management with the syntax you can see in export files ::

  OBJECT;AACTION;Parameter1;Parameter2;Parameter3;...

