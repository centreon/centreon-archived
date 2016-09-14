*************
Prérequis
*************

Les paquets suivants sont des prérequis:

* php-mysql
* Pear-DB
* MySQL (>= 5.1.x)


Le paramètre MySQL **open_files_limit** doit être fixé à 32000 dans la section [server] :

::

  [server]
  open_files_limit = 32000