.. warning::
    Centreon a démarré sa compatibilité avec le mode strict SQL. Cependant, tous ses composants ne sont pas encore
    prêts. C'est pourquoi il est impératif de désactiver le mode strict SQL si vous utilisez MariaDB >= 10.2.4 ou MySQL
    >= 5.7.5 pour vos environnements de production.
    
    **Pour MariaDB**
    
    Exécutez les commandes suivantes : ::
        
        # SET sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
        # SET GLOBAL sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
    
    ou modifiez le fichier */etc/my.cnf.d/centreon.cnf* pour ajouter à la section '[server]' la ligne suivante : ::
        
        sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'
    
    puis redémarrez votre SGBD.

    **Pour MySQL**
    
    Exécutez les commandes suivantes : ::
        
        # SET sql_mode = 'NO_ENGINE_SUBSTITUTION';
        # SET GLOBAL sql_mode = 'NO_ENGINE_SUBSTITUTION';
    
    ou modifiez le fichier */etc/my.cnf.d/centreon.cnf* pour ajouter à la section '[server]' la ligne suivante : ::
        
        sql_mode = 'NO_ENGINE_SUBSTITUTION'
    
    puis redémarrez votre SGBD.
