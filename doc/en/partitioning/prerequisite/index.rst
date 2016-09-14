*************
Prerequisites
*************

The following packages are required:

* php-mysql
* Pear-DB
* MySQL (>= 5.1.x)


MySQL **open_files_limit** parameter must be set to 32000 in [server] section :

::

  [server]
  open_files_limit = 32000
