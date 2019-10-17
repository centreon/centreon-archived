.. warning::
    Centreon started the compatibility with SQL strict mode but not all components are ready yet. It is mandatory to
    disable the strict mode if you use MariaDB >= 10.2.4 or MySQL >= 5.7.5 for your production environments.
    
    **For MariaDB**

    Execute the following SQL request: ::
        
        # SET sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
        # SET GLOBAL sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
    
    or modify the */etc/my.cnf.d/centreon.cnf* file to add in the '[server]' section the following line: ::
        
        sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'
    
    then restart your DBMS.

    **For MySQL**

    Execute the following SQL request: ::
        
        # SET sql_mode = 'NO_ENGINE_SUBSTITUTION';
        # SET GLOBAL sql_mode = 'NO_ENGINE_SUBSTITUTION';
    
    or modify the */etc/my.cnf.d/centreon.cnf* file to add in the '[server]' section the following line: ::
        
        sql_mode = 'NO_ENGINE_SUBSTITUTION'
    
    then restart your DBMS.
