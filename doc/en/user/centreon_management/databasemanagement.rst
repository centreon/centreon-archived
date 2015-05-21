Database managent
#################

Generate database
-----------------
This procedure to update the schematic of the database , it will compare the current version with the new schematic generated XML files.

::

   external/bin/centreonConsole core:database:Configuration:generate


Tools for database
------------------

Convert sql datas to JSON
^^^^^^^^^^^^^^^^^^^^^^^^^
This command will generate a JSON file from a table in the database . You must replace the argument MyTableName by the name of the table you are interested in.

========= ==================== =======
argument  description          example
========= ==================== =======
dbname    The name of database db_centreon
tablename The name of table    cfg_tags
========= ==================== =======

::

   external/bin/centreonConsole core:database:Tools:sqlToJson dbname=db_centreon:tablename=myTableName

Example of return:
::

   [{"tag_id":"28","tagname":"europe.paris.defense"},{"tag_id":"4","tagname":"PARIS"},{"tag_id":"3","tagname":"REIMS"},{"tag_id":"11","tagname":"taghost"},{"tag_id":"2","tagname":"TOULOUSEMIRAIL"}]


Convert JSON datas to sql
^^^^^^^^^^^^^^^^^^^^^^^^^
This command will generate a SQL code from a JSON file. The file argument will contain the source file. The second argument contains the name of the table in the database . The latter is optional. It is useful if you want to send the answer to a file, if empty the contents will be displayed in the concole.

=========== ================================== ==============
argument    description                        example
=========== ================================== ==============
file        The file to import                 /tmp/tags.json
tablename   The name of table                  cfg_tags
destination The file where data will be stored /tmp/tags.sql
=========== ================================== ==============

::

   external/bin/centreonConsole core:database:Tools:jsonToSql file=mySource,tablename=myTableName,destination=myDestination


Migrate class
^^^^^^^^^^^^^
This command will generate a database migration file. it will not execute, it may be overloaded with new post orders or pre-order.

::

   external/bin/centreonConsole core:database:Tools:generateMigrationClass
